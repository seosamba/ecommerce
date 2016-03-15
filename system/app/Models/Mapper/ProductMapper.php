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
			'tax_class'         => $model->getTaxClass(),
			'inventory'         => is_numeric($model->getInventory()) ? $model->getInventory() : null,
            'free_shipping'     => $model->getFreeShipping()
		);

		if ($model->getId()){
			$data['updated_at'] = date(Tools_System_Tools::DATE_MYSQL);
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			$result = $this->getDbTable()->update($data, $where);
			$model->registerObserver(new Tools_ProductWatchdog(array(
				'action' => Tools_Cache_GarbageCollector::CLEAN_ONUPDATE
			)));
		} else {
			$data['created_at'] = date(Tools_System_Tools::DATE_MYSQL);
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

		if (is_array($model->getRelated())){
			$this->_processRelated($model);
		}

        if (is_array($model->getFreebies())){
            $this->_processFreebies($model);
        }

        //process product parts if any
        $this->_processParts($model);

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
     * @param bool $strictTagsCount
     * @param bool $organicSearch (search by product name, brand name etc...)
     * @param array $attributes product attributes
	 * @return array|null List of products
	 */
    public function fetchAll(
        $where = null,
        $order = null,
        $offset = null,
        $limit = null,
        $search = null,
        $tags = null,
        $brands = null,
        $strictTagsCount = false,
        $organicSearch = false,
        $attributes = array(),
        $price = array()
    ) {
        $entities = array();

        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)->setIntegrityCheck(false)
            ->from(array('p' => 'shopping_product'))
            ->join(array('b' => 'shopping_brands'), 'b.id = p.brand_id', null)
            ->group('p.id');

        if (!is_null($order)) {
			if(is_array($order) && ($key = array_search('p.sku', $order)) !== false) {
				unset($order[$key]);
				$select->order('ABS(p.sku)');
			}
			$select->order($order);
        }

        if (!empty($where)) {
            $select->where($where);
        }

        if (!empty($brands)) {
            if (!is_array($brands)) {
                $brands = (array)$brands;
            }
            $select->where('b.name in (?)', $brands);
        }

        if (!empty($attributes)) {
            $productIds = Filtering_Mappers_Eav::getInstance()->findProductIdsByAttributes($attributes);
            if (!empty($productIds)) {
                $select->where('p.id IN (?)', $productIds);
            } else {
                return null;
            }
        }

        if(!empty($price)){
            $select->where("p.price BETWEEN " . $price['min'] ." AND ".$price['max']);
        }

        if (!empty($tags)) {
            if (!is_array($tags)) {
                $tags = (array)$tags;
            }

            $select->from(array('t' => 'shopping_tags'), null)
                ->join(array('pt' => 'shopping_product_has_tag'), 'pt.tag_id = t.id AND pt.product_id = p.id', null)
                ->where('pt.tag_id IN (?)', $tags);

            // we need product with all the tags at the same time ('AND' logic)
            if ($strictTagsCount) {
                $select->having('COUNT(*) = ?', sizeof($tags));
            }
        }

        if ((bool)$search) {
            $likeWhere = array(
                'p.name LIKE ?',
                'p.sku LIKE ?',
                'p.mpn LIKE ?',
                'b.name LIKE ?',
                't.name LIKE ?'
            );

            $likeWhere = implode(' OR ', $likeWhere);

            if (empty($tags)) {
                $select
                    ->joinLeft(array('pt' => 'shopping_product_has_tag'), 'pt.product_id = p.id', array())
                    ->joinLeft(array('t' => 'shopping_tags'), 'pt.tag_id = t.id', array());
            }

            if ($organicSearch) {

                if (is_array($search)) {
                    $subWhere = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)->setIntegrityCheck(
                        false
                    );
                    foreach ($search as $term) {
                        $subWhere->where($likeWhere, '%' . $term . '%');
                    }

                    $subWhere = implode(' ', $subWhere->getPart('WHERE'));
                    $select->where($subWhere);
                } else {
                    $select->orWhere($likeWhere, '%' . $search . '%');
                }

            } else {
                $select->where($likeWhere, '%' . $search . '%');
            }
        }

        if (self::$_logSelectResultLength === false) {
            $select->limit($limit, $offset);
        }

        Tools_System_Tools::debugMode() && error_log($select->__toString());
        $resultSet = $this->getDbTable()->fetchAll($select);

        if (count($resultSet) === 0) {
            return null;
        }

        if (self::$_logSelectResultLength === true) {
            self::$_lastSelectResultLength = sizeof($resultSet);
            $tmp = array();
            $maxOffset = (sizeof($resultSet) < ($offset + $limit)) ? sizeof($resultSet) : $offset + $limit;
            for ($offset; $offset < $maxOffset; $offset++) {
                if ($resultSet->offsetExists($offset)) {
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

        //fetching product parts
        $partsSet = $row->findDependentRowset('Models_DbTable_ProductHasPart');
        if($partsSet->count()) {
            $parts = array();
            foreach($partsSet as $partsRow) {
                array_push($parts, $partsRow->part_id);
            }
            $entity->setParts($parts);
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

    private function _processFreebies(Models_Model_Product $model){
        $freebies = $model->getFreebies();
        $freebiesTable = new Models_DbTable_ProductHasFreebies();

        $where = $freebiesTable->getAdapter()->quoteInto('product_id = ?', $model->getId());
        $freebiesTable->delete($where);

        foreach ($freebies as $id) {
            $where = $freebiesTable->getAdapter()->quoteInto('freebies_id = ?', intval($id));
            $where .= ' AND '.$freebiesTable->getAdapter()->quoteInto('product_id = ?', $model->getId());
            $select = $freebiesTable->getAdapter()->select()->from('shopping_product_has_freebies')->where($where);
            $freebiesExist = $freebiesTable->getAdapter()->fetchRow($select);
            if(!empty($freebiesExist)){
                $freebiesTable->update(array('freebies_quantity' => $freebiesExist['freebies_quantity']+1), $where);
            }else{
                $freebiesTable->insert(array(
                    'product_id'  => $model->getId(),
                    'freebies_id' => intval($id),
                    'freebies_quantity' => 1
                ));
            }
        }

    }

    private function _processParts(Models_Model_Product $model) {
        $parts                 = $model->getParts();
        $productHasPartDbTable = new Models_DbTable_ProductHasPart();
        $where                 = $productHasPartDbTable->getAdapter()->quoteInto('product_id = ?', $model->getId());
        $productHasPartDbTable->delete($where);
        if(!$parts) {
            return;
        }
        foreach($parts as $partId) {
            $productHasPartDbTable->insert(array(
                'product_id' => $model->getId(),
                'part_id'    => intval($partId)
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
                    $prodList = $this->getDbTable()->getAdapter()->select()->from("shopping_product", array("num"=>"COUNT(*)"))->where('brand_id = ?', $brand->getId());
                    $brandProducts = $this->getDbTable()->getAdapter()->fetchOne($prodList);
                    if (empty($brandProducts)){
                        $this->_brandMapper->delete($brand);
                    }
                }
            }
            // removing page
            if ($product->getPage()){
                $page = $product->getPage();
                $page->registerObserver(new Tools_Page_GarbageCollector(array(
                    'action' => Tools_System_GarbageCollector::CLEAN_ONDELETE
                )));
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

	public function fetchProductSalesCount($id){
		if (!is_array($id)){
			$id = array($id);
		}
		/**
		 * @var Zend_Db_Table_Select
		 */
		$select = $this->getDbTable()->getAdapter()
			->select()
			->from(array('sales' => 'shopping_cart_session_content'), array('sales.product_id', 'cart.status', 'count' => 'COUNT(cart.status)'))
			->join(array('cart' => 'shopping_cart_session'), 'cart.id = sales.cart_id', null)
			->where('sales.product_id IN (?)', $id)
			->group(array('sales.product_id', 'cart.status'));

		$result = $this->getDbTable()->getAdapter()->fetchAll($select);
		return $result;
	}

    public function countProductsWithoutWeight(){
        $select = $this->getDbTable()->getAdapter()
            ->select()
            ->from('shopping_product', array('count' => 'COUNT(id)'))
            ->where('weight = 0 OR weight IS NULL');
        $result = $this->getDbTable()->getAdapter()->fetchAll($select);
        return $result[0]['count'];
    }

}
