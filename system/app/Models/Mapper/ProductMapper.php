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
     * @var Models_Mapper_Brand
     */
	protected $_brandMapper = null;

	protected static $_lastSelectResultLength = null;

	protected static $_logSelectResultLength = false;


	protected function __construct() {
		$this->_brandMapper = Models_Mapper_Brand::getInstance();
	}

	/**
	 * Toggle on count of select query results if true passed as first parameter.
	 * Use lastSelectResultLength() method to get value.
	 * @return Models_Mapper_ProductMapper Returns self instance for chaining
	 */
	public function logSelectResultLength(){
		func_num_args() && is_bool(func_get_arg(0))  && self::$_logSelectResultLength = func_get_arg(0) ;
		return $this;
	}

	/**
	 * Returns length of last select query result set
	 * @return number|null
	 */
	public function lastSelectResultLength(){
		return self::$_lastSelectResultLength;
	}

	/**
	 * Save product model to database
	 * @param $model array|Models_Model_Product Product model or hash with product properties
	 * @return Models_Model_Product Saved product model
	 */
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

	/**
	 * Fetch products
	 * @param string|null $where Where clause
	 * @param string|array|null $order Fields to order by
	 * @param string|null $offset Number of results to skip
	 * @param string|null $limit Maximum number of results
	 * @param array|null $search Keyword, to search across name, mpn, sku, brand name or tags
	 * @param array|null $tags List of tags ids to filter by
	 * @param array|null $brands List of brand names to filter by
	 * @return array|null List of products
	 */
	public function fetchAll($where = null, $order = null, $offset = null, $limit = null,
	                         $search = null, $tags = null, $brands = null) {
		$entities = array();

		$select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)->setIntegrityCheck(false)
				->from(array('p' => 'shopping_product'))
				->join(array('b' => 'shopping_brands'), 'b.id = p.brand_id', null)
				->group('p.id');

		if (!is_null($order)) {
			$select->order($order);
		}

		if (!empty($where)){
			$select->where($where);
		}

		if (!empty($brands)){
			if (!is_array($brands)) $brands = (array) $brands;
			$select->where('b.name in (?)', $brands);
		}

		if (!empty($tags)){
			if (!is_array($tags)) $tags = (array) $tags;

			$select->from(array('t' => 'shopping_tags'), null)
                ->join(array('pt' => 'shopping_product_has_tag'), 'pt.tag_id = t.id AND pt.product_id = p.id', null)
				->where('pt.tag_id IN (?)', $tags)
				->having('COUNT(*) = ?', sizeof($tags));
		}

        if ((bool)$search) {
	        $likeWhere = 'p.name LIKE ? OR p.sku LIKE ? OR p.mpn LIKE ? OR b.name LIKE ?';
	        if (empty($tags)){
		        $select->from(array('t' => 'shopping_tags'), null)
                    ->joinLeft(array('pt' => 'shopping_product_has_tag'), 'pt.product_id = p.id', null);
		        $likeWhere .= ' OR (t.name LIKE ? AND pt.tag_id = t.id)';
	        }
	        $select->where($likeWhere, '%'.$search.'%');
        }


		if (self::$_logSelectResultLength === false){
			$select->limit($limit, $offset);
		}

		error_log($select->__toString());
		$resultSet = $this->getDbTable()->fetchAll($select);

		if(count($resultSet) === 0) {
			return null;
		}

		if (self::$_logSelectResultLength === true){
			self::$_lastSelectResultLength = sizeof($resultSet);
			$tmp = array();
			$maxOffset = (sizeof($resultSet) < ($offset + $limit)) ? sizeof($resultSet) : $offset + $limit;
			for ($offset; $offset < $maxOffset; $offset++){
				if ($resultSet->offsetExists($offset)){
					$resultSet->seek($offset);
					array_push($tmp, $resultSet->current());
				}
			}
			$resultSet = $tmp;
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
	 * Find product which contains given tags
	 * Shorthand method to self::fetchAll
	 * @param array $tags List of tags to search for
	 * @return array|null List of products
	 */
	public function findByTags(array $tags) {
		return $this->fetchAll(null, null, null, null, null, $tags);
	}

	/**
	 * Find product with given brands
	 * Shorthand method to self::fetchAll
	 * @param array $brands List of brands
	 * @return array|null List of products
	 */
	public function findByBrands(array $brands) {
		return $this->fetchAll($this->getDbTable()->getAdapter()->quoteInto('b.name IN (?)', $brands));
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