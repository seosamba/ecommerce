<?php

/**
 * ProductMapper
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 * @method Models_Mapper_ProductMapper getInstance() getInstance()  Returns an instance of itself
 */
class Models_Mapper_ProductMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable	= 'Models_DbTable_Product';

	protected $_model	= 'Models_Model_Product';

    /**
     * @var #M#C\Models_Mapper_Brand.getInstance|null|?
     */
	protected $_brandMapper = null;

	public function __construct() {
		$this->_brandMapper = Models_Mapper_Brand::getInstance();
	}

	public function save($model) {
		if (!$model instanceof $this->_model){
			$model = new $this->_model($model);
		}
		if ($model->getBrand()){
			if (!$brand = $this->_brandMapper->findByName($model->getBrand())){
				$brand = $this->_brandMapper->save(array('name' => $model->getBrand()));
			}
		}
		$data = array(
			'parent_id'         => $model->getParentId(),
			'sku'	            => $model->getSku(),
			'name'              => $model->getName(),
			'photo'             => $model->getPhoto(),
			'brand_id'          => isset($brand)?$brand->getId():null,
			'mpn'               => $model->getMpn(),
			'enabled'           => $model->getEnabled(),
			'weight'            => $model->getWeight(),
			'short_description'	=> $model->getShortDescription(),
			'full_description'  => $model->getFullDescription(),
			'price'             => $model->getPrice(),
			'tax_class'         => $model->getTaxClass()
		);

		if ($model->getId()){
			$data['updated_at'] = date(DATE_ATOM);
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			$result = $this->getDbTable()->update($data, $where);
			$model->registerObserver(new Tools_ProductWatchdog(array(
				'action' => Tools_Cache_GarbageCollector::CLEAN_ONUPDATE
			)));
		} else {
			$data['created_at'] = date(DATE_ATOM);
			$id = $this->getDbTable()->insert($data);
			if ($id){
				$model->setId($id);
			}
			$model->registerObserver(new Tools_ProductWatchdog(array(
				'action' => Tools_Cache_GarbageCollector::CLEAN_ONCREATE
			)));
		}

		if (!is_null($model->getTags())) {
			$productTagsTable = new Models_DbTable_ProductTag();
			$productTagsTable->getAdapter()->beginTransaction();
			$productTagsTable->delete($productTagsTable->getAdapter()->quoteInto('product_id = ?', $model->getId()));
			foreach ($model->getTags() as $tag) {
				$productTagsTable->insert(array(
					'product_id' => $model->getId(),
					'tag_id'     => $tag['id']
				));
			}
			$productTagsTable->getAdapter()->commit();
		}

		$this->_processOptions($model);

		if ($model->getRelated()){
			$this->_processRelated($model);
		}

		$model->notifyObservers();

		return $model;
	}

	public function updatePageIdForProduct($model){
		$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
		return $this->getDbTable()->update( array('page_id' => $model->getPage()->getId()), $where);
	}

	public function fetchAll($where = null, $order = array(), $offset = null, $limit = null, $search = null) {
		$entities = array();

        if ($search === null) {
            $resultSet = $this->getDbTable()->fetchAll($where, $order, $limit, $offset);
        } else {
            $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                    ->setIntegrityCheck(false);
            $select->where('shopping_product.name LIKE ?', '%'.$search.'%')
                    ->orWhere('shopping_product.sku LIKE ?', '%'.$search.'%')
                    ->orWhere('shopping_product.mpn LIKE ?', '%'.$search.'%')
                    ->joinLeft('shopping_brands', 'shopping_brands.id = shopping_product.brand_id', null)
                    ->joinLeft('shopping_product_has_tag', 'shopping_product_has_tag.product_id = shopping_product.id', null)
                    ->joinLeft('shopping_tags', 'shopping_tags.id = shopping_product_has_tag.tag_id', null)
                    ->orWhere('shopping_brands.name LIKE ?', '%'.$search.'%')
                    ->orWhere('shopping_tags.name LIKE ?', '%'.$search.'%')
                    ->group('shopping_product.id')
                    ->limit($limit, $offset)
                    ->order($order);

            $resultSet = $this->getDbTable()->fetchAll($select);
        }

		if(count($resultSet) === 0) {
			return null;
		}
		foreach ($resultSet as $row) {
			array_push($entities, $this->_toModel($row));
		}
		return $entities;
	}

	public function find($id) {
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return null;
		} elseif (count($result) > 1) {
			$list = array();
			foreach ($result as $row) {
				array_push($list, $this->_toModel($row));
			}
			return $list;
		}
		$row = $result->current();

		return $this->_toModel($row);
	}
	
	public function findByPageId($id) {
		$productRow = $this->getDbTable()->fetchRow(array('page_id = ?' => $id));
		if (!empty ($productRow)){
			return $this->_toModel($productRow);
		}
		return null;
	}

    /**
     * @param Zend_Db_Table_Row_Abstract $row
     * @return mixed
     */
	private function _toModel(Zend_Db_Table_Row_Abstract $row){
		/**
	      * @var Models_Model_Product $entity
		 */
		$entity = new $this->_model($row->toArray());

        if ($row->brand_id){
			$brandRow = $row->findDependentRowset('Models_DbTable_Brand');
			if ($brandRow->count()){
				$entity->setBrand($brandRow->current()->name);
			}
		}

		//fetching tags
		$tagsSet = $row->findManyToManyRowset('Models_DbTable_Tag','Models_DbTable_ProductTag');
		if ($tagsSet->count()){
			$entity->setTags($tagsSet->toArray());
		}

		//fetching options
		$optionSet = $row->findDependentRowset('Models_DbTable_ProductOption');
		if ($optionSet->count()) {
            $ids = array();
            foreach ($optionSet as $optionRow) {
                array_push($ids, $optionRow->option_id);
            }
			$entity->setDefaultOptions(Models_Mapper_OptionMapper::getInstance()->fetchAll(array('id IN (?)' => $ids), null, false));
            unset($ids);
		}

		//fetching related products
		$relatedSet = $row->findDependentRowset('Models_DbTable_ProductRelated');
		if ($relatedSet->count()){
			$related = array();
			foreach ($relatedSet as $relatedRow) {
				array_push($related, $relatedRow->related_id);
			}
			$entity->setRelated($related);
		}

		//fetching product page
		if ($row->page_id){
			$page = Application_Model_Mappers_PageMapper::getInstance()->find($row->page_id);
			if ($page){
				$entity->setPage($page);
			}
		}
		return $entity;
	}

	private function _processOptions(Models_Model_Product $model){
		$optionMapper = Models_Mapper_OptionMapper::getInstance();

		$relationTable = new Models_DbTable_ProductOption();
		$currentList = $relationTable->fetchAll($relationTable->getAdapter()->quoteInto('product_id = ?', $model->getId()));

		$options = array();
        $validIds = array();
		foreach ($model->getDefaultOptions() as $option) {
			if ( !isset($option['title']) || empty($option['title']) ) {
				continue;
			} else {
                if (isset($option['isTemplate']) && $option['isTemplate'] === true){
                    $template = $optionMapper->save( array(
                        'title'     => isset($option['templateName']) && !empty($option['templateName']) ? $option['templateName'] : 'template-'.$option['title'],
                        'type'      => $option['type'],
                        'parentId'  => '0',
                        'selection' => $option['selection']
                    ) );
                    $option['parentId'] = $template->getId();
                    unset($template);
                }
                $result = $optionMapper->save($option);
			}
			array_push($options, $result->toArray());
			array_push($validIds, $result->getId());
		}

		foreach ($currentList as $row) {
			if (!in_array($row->option_id, $validIds)){
				$row->delete();
			}
		}

		foreach ($validIds as $optionId){
			$row = $relationTable->find($model->getId(), $optionId);
			if (!$row->count()){
				$row = $relationTable->createRow(array(
					'product_id' => $model->getId(),
					'option_id'	 => $optionId
					));
				$row->save();
			}
		}

        $model->setDefaultOptions($options);
		return $options;
	}

	private function _processRelated(Models_Model_Product $model){
		$related = $model->getRelated();
		$relatedTable = new Models_DbTable_ProductRelated();

		$where = $relatedTable->getAdapter()->quoteInto('product_id = ?', $model->getId());
		$relatedTable->delete($where);

		foreach ($related as $id) {
			 $relatedTable->insert(array(
				'product_id' => $model->getId(),
				'related_id' => intval($id)
				));
		}

	}

	/**
	 * Find product wich contains given tags
	 * @param array $tags List of tags to search for
	 * @param bool $intersect
	 * @return array List of products
	 */
	public function findByTags(array $tags, $intersect = true) {
		$products         = array();
		$filteredProducts = array();
		$catDbTable       = new Models_DbTable_Tag();
		if(!empty($tags)) {
			$select = $catDbTable->getAdapter()->select()->from(array('t' => $catDbTable->info('name')), null)
				->joinLeft(array('pht'=>'shopping_product_has_tag'), 'pht.tag_id = t.id', null)
				->joinLeft(array('p'=>'shopping_product'), 'p.id = pht.product_id')
				->where('t.id IN (?)', $tags)
				->group('p.id');

			$productsRaw = $catDbTable->getAdapter()->fetchAll($select);

			if (!empty($productsRaw)){
				$products = array_map(function($product){
					return new Zend_Db_Table_Row(array('table' => new Models_DbTable_Product(), 'data'=>$product));
				}, $productsRaw);
				unset($productsRaw);
				$filteredProducts = array_map(array($this, '_toModel'), $products);
			}
		}
		return array_values($filteredProducts);
	}

	public function findByBrands(array $brands) {
		$products = array();
		foreach($brands as $brand) {
		 	$brandModel =  $this->_brandMapper->findByName($brand);
			if($brandModel) {
				$brandProducts = $this->fetchAll($this->getDbTable()->getAdapter()->quoteInto('brand_id = ?', $brandModel->getId()));
				if(is_array($brandProducts)) {
					$products = array_merge($products, $brandProducts);
				}
			}
		}
		return $products;
	}

    /**
     * Delete product from database and remove brand if no more products present
     * @param Models_Model_Product $product
     * @return boolean true on success, false on failure
     */
	public function delete(Models_Model_Product $product){
		$product->registerObserver(new Tools_ProductWatchdog(array(
			'action' => Tools_Cache_GarbageCollector::CLEAN_ONDELETE
		)));

        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $product->getId());

        $status = $this->getDbTable()->delete($where);

        if ((bool) $status) {
            if ($product->getBrand()){
                $brand = $this->_brandMapper->findByName($product->getBrand());
                if ($brand) {
                    $prodList = $this->fetchAll($this->getDbTable()->getAdapter()->quoteInto('brand_id = ?', $brand->getId()));
                    if (empty($prodList)){
                        $this->_brandMapper->delete($brand);
                    }
                }
            }
            // removing page
            if ($product->getPage()){
                Application_Model_Mappers_PageMapper::getInstance()->delete($product->getPage());
            }

	        $product->notifyObservers();
            return true;
        } else {
            return false;
        }
	}

    public function buildIndex(){
        $db = $this->getDbTable()->getAdapter();

        $select[] = $db->select()
            ->from('shopping_product', array('name'));
        $select[] = $db->select()
            ->from('shopping_product', array('sku'));
        $select[] = $db->select()
            ->from('shopping_product', array('mpn'));
        $select[] = $db->select()
            ->from('shopping_tags', array('name'));
        $select[] = $db->select()
            ->from('shopping_brands', array('name'));

        return $db->fetchCol($db->select()->union($select));
    }

	public function updateAttributes($id, $attributes){
		if (is_array($id) && !empty($id)){
			$status = 0;
			foreach ($id as $prodId) {
				if ($this->updateAttributes($prodId, $attributes)){
					$status++;
				};
			}
			return $status;
		} else {
			return $this->getDbTable()->update($attributes, array('id = ?' => $id));
		}
	}
}