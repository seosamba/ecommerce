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
            'free_shipping'     => $model->getFreeShipping(),
            'is_digital'        => $model->getIsDigital(),
            'prod_length'       => $model->getProdLength(),
            'prod_depth'        => $model->getProdDepth(),
            'prod_width'        => $model->getProdWidth(),
            'gtin'              => $model->getGtin(),
            'wishlist_qty'      => $model->getWishlistQty(),
            'minimum_order'     => $model->getMinimumOrder(),
            'negative_stock'    => $model->getNegativeStock()
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

        if (!empty($model->getCustomParams())) {
            $this->_processCustomParams($model->getCustomParams(), $model->getId());
        }

        $this->_processCompanyProducts($model->getCompanyProducts(), $model->getId());

        //process product parts if any
        $this->_processParts($model);

        //process product Allowance
        $allowanceDate = $model->getAllowance();
        if(!empty($allowanceDate)) {
            $this->_processAllowance($model);
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
        $price = array(),
        $sort = null,
        $allowance = false,
        $productPrice = array(),
        $inventory = null

    ) {
        $entities = array();

        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)->setIntegrityCheck(false)
            ->from(array('p' => 'shopping_product'))
            ->join(array('b' => 'shopping_brands'), 'b.id = p.brand_id', null)
            ->group('p.id');

        if ((!is_null($order)) && (is_array($order)) && (!is_null($sort))) {
            foreach ($order as $key => $ord){
                $order[$key] = $ord . ' ' . $sort;
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

        if(!empty($productPrice)) {
            $select->joinLeft(array('sfv' => 'shopping_filtering_values'), 'sfv.product_id = p.id', array());
            foreach ($productPrice as $pPrice) {
                $select->where("sfv.value BETWEEN " . $pPrice['min'] ." AND ".$pPrice['max']);
            }
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

        if(is_array($inventory) && !empty($inventory)) {
            if(in_array('unlimited', $inventory)){
                $where = new Zend_Db_Expr('p.inventory IS NULL');
                unset($inventory[0]);
                if (!empty($inventory)) {
                    $where .= ' OR ' . $this->getDbTable()->getAdapter()->quoteInto('p.inventory IN (?)', $inventory);
                }
            }else{
                $where = $this->getDbTable()->getAdapter()->quoteInto('p.inventory IN (?)', $inventory);
            }
            $select->where($where);
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
                $attributeValues = explode(' ', $search);
                $whereSplitSearch = ' (';

                foreach ($attributeValues as $key => $attrVal) {
                    $whereSplitSearch .= $this->getDbTable()->getAdapter()->quoteInto('p.name LIKE ?',
                        '%' . $attrVal . '%');

                    if (count($attributeValues) > $key + 1) {
                        $whereSplitSearch .= ' AND ';
                    }

                }

                $whereSplitSearch .= ') OR ( ';

                foreach ($attributeValues as $key => $attrVal) {
                    $whereSplitSearch .= $this->getDbTable()->getAdapter()->quoteInto('p.sku LIKE ?',
                        '%' . $attrVal . '%');

                    if (count($attributeValues) > $key + 1) {
                        $whereSplitSearch .= ' AND ';
                    }

                }

                $whereSplitSearch .= ') OR ( ';

                foreach ($attributeValues as $key => $attrVal) {
                    $whereSplitSearch .= $this->getDbTable()->getAdapter()->quoteInto('p.mpn LIKE ?',
                        '%' . $attrVal . '%');

                    if (count($attributeValues) > $key + 1) {
                        $whereSplitSearch .= ' AND ';
                    }

                }

                $whereSplitSearch .= ') OR ( ';

                foreach ($attributeValues as $key => $attrVal) {
                    $whereSplitSearch .= $this->getDbTable()->getAdapter()->quoteInto('b.name LIKE ?',
                        '%' . $attrVal . '%');

                    if (count($attributeValues) > $key + 1) {
                        $whereSplitSearch .= ' AND ';
                    }

                }

                $whereSplitSearch .= ') OR ( ';

                foreach ($attributeValues as $key => $attrVal) {
                    $whereSplitSearch .= $this->getDbTable()->getAdapter()->quoteInto('t.name LIKE ?',
                        '%' . $attrVal . '%');

                    if (count($attributeValues) > $key + 1) {
                        $whereSplitSearch .= ' AND ';
                    }

                }

                $whereSplitSearch .= ')';

                $select->where($whereSplitSearch);
            }
        }

        if($allowance) {
            $select->joinLeft(array('ap' => 'shopping_allowance_products'), 'ap.product_id = p.id', array('allowance' => 'ap.allowance_due'));
        }

        $select->joinLeft(array('scp' => 'shopping_company_products'), 'scp.product_id = p.id', array('companyProducts' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT(scp.company_id))')));

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

    /**
     *
     * @param string $where SQL where clause
     * @param string $order OPTIONAL An SQL ORDER clause.
     * @param int $limit OPTIONAL An SQL LIMIT count.
     * @param int $offset OPTIONAL An SQL LIMIT offset.
     * @param bool $withoutCount flag to get with or without records quantity
     * @param bool $singleRecord flag fetch single record
     * @param string $having mysql having
     *
     * @return array
     */
    public function fetchAllData(
        $where = null,
        $order = null,
        $limit = null,
        $offset = null,
        $withoutCount = false,
        $singleRecord = false,
        $having = ''
    ) {
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('sp' => 'shopping_product'),
                array(
                    'sp.*'
                )
            )->joinLeft(array('p' => 'page'), 'p.id = sp.page_id', array('p.url'))
             ->joinLeft(array('sb' => 'shopping_brands'), 'sb.id = sp.brand_id', array('brandName' => 'sb.name'));

        if (!empty($having)) {
            $select->having($having);
        }

        $select->group('sp.id');
        if (!empty($order)) {
            $select->order($order);
        }

        if (!empty($where)) {
            $select->where($where);
        }

        $select->limit($limit, $offset);

        if ($singleRecord) {
            $data = $this->getDbTable()->getAdapter()->fetchRow($select);
        } else {
            $data = $this->getDbTable()->getAdapter()->fetchAll($select);
        }

        if ($withoutCount === false) {
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::FROM);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);
            $select->reset(Zend_Db_Select::LIMIT_COUNT);

            $count = array('count' => new Zend_Db_Expr('COUNT(DISTINCT(sp.id))'));

            $select->from(array('sp' => 'shopping_product'), $count);

            $select =  $this->getDbTable()->getAdapter()->select()
                ->from(
                    array('subres' => $select),
                    array('count' => 'SUM(count)')
                );

            $count = $this->getDbTable()->getAdapter()->fetchRow($select);

            return array(
                'totalRecords' => $count['count'],
                'data' => $data,
                'offset' => $offset,
                'limit' => $limit
            );
        } else {
            return $data;
        }
    }

    /**
     * Returned products array
     */
    public function fetchAllProductByParams (
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
        $price = array(),
        $sort = null,
        $allowance = false,
        $productPrice = array(),
        $inventory = null
    ) {
        $entities = array();

        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)->setIntegrityCheck(false)
            ->from(array('p' => 'shopping_product'))
            ->join(array('b' => 'shopping_brands'), 'b.id = p.brand_id', null)
            ->group('p.id');

        if ((!is_null($order)) && (is_array($order)) && (!is_null($sort))) {
            foreach ($order as $key => $ord){
                $order[$key] = $ord . ' ' . $sort;
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

        if(!empty($productPrice)) {
            $select->joinLeft(array('sfv' => 'shopping_filtering_values'), 'sfv.product_id = p.id', array());
            foreach ($productPrice as $pPrice) {
                $select->where("sfv.value BETWEEN " . $pPrice['min'] ." AND ".$pPrice['max']);
            }
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

        if(is_array($inventory) && !empty($inventory)) {
            if(in_array('unlimited', $inventory)){
                $where = new Zend_Db_Expr('p.inventory IS NULL');
                unset($inventory[0]);
                if (!empty($inventory)) {
                    $where .= ' OR ' . $this->getDbTable()->getAdapter()->quoteInto('p.inventory IN (?)', $inventory);
                }
            }else{
                $where = $this->getDbTable()->getAdapter()->quoteInto('p.inventory IN (?)', $inventory);
            }
            $select->where($where);
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

        if($allowance) {
            $select->joinLeft(array('ap' => 'shopping_allowance_products'), 'ap.product_id = p.id', array('allowance' => 'ap.allowance_due'));
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
            $product = $row->toArray();
            //fetching tags
            $tagsSet = $row->findManyToManyRowset('Models_DbTable_Tag','Models_DbTable_ProductTag');
            if ($tagsSet->count()){
                $product['tags'] = $tagsSet->toArray();
            }

            array_push($entities, $product);
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

        $allowanceData = Store_Mapper_AllowanceProductsMapper::getInstance()->findByProductId($row->id);
        if($allowanceData instanceof Store_Model_AllowanceProducts) {
            $allowanceDate = $allowanceData->getAllowanceDue();
            $entity->setAllowance($allowanceDate);
        }

        $productCustomParams = Store_Mapper_ProductCustomParamsDataMapper::getInstance()->findByProductId($row->id);
        if (!empty($productCustomParams)) {
            $entity->setCustomParams($productCustomParams);
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
                        'selection' => $option['selection'],
                        'hideDefaultOption' => $option['hideDefaultOption']
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

    /**
     * @param array $customParams custom params data
     * @param int $productId product id
     */
    private function _processCustomParams($customParams, $productId)
    {
        if (!empty($customParams)) {
            $productCustomFieldsConfigMapper = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance();
            $productCustomParamsDataMapper = Store_Mapper_ProductCustomParamsDataMapper::getInstance();

            foreach ($customParams as $customParam) {
                $productCustomFieldsConfigModel = $productCustomFieldsConfigMapper->findById($customParam['id']);
                if ($productCustomFieldsConfigModel instanceof Store_Model_ProductCustomFieldsConfigModel) {
                    $productCustomParamsDataModel = $productCustomParamsDataMapper->checkIfParamExists($productId,
                        $customParam['id']);
                    if (!$productCustomParamsDataModel instanceof Store_Model_ProductCustomParamsDataModel) {
                        $productCustomParamsDataModel = new Store_Model_ProductCustomParamsDataModel();
                        $productCustomParamsDataModel->setProductId($productId);
                        $productCustomParamsDataModel->setParamId($customParam['id']);
                    }

                    if ($customParam['param_type'] === Store_Model_ProductCustomFieldsConfigModel::CUSTOM_PARAM_TYPE_SELECT) {
                        $productCustomParamsDataModel->setParamsOptionId($customParam['param_value']);
                        $productCustomParamsDataModel->setParamValue('');
                    } else {
                        $productCustomParamsDataModel->setParamValue($customParam['param_value']);
                        $productCustomParamsDataModel->setParamsOptionId(null);
                    }

                    $productCustomParamsDataMapper->save($productCustomParamsDataModel);
                }
            }
        }
    }

    /**
     * @param $companyProducts
     * @param $productId
     * @return void
     */
    private function _processCompanyProducts($companyProducts, $productId)
    {
        $companyProductsMapper = Store_Mapper_CompanyProductsMapper::getInstance();

        if(!empty($productId)) {
            $companyProductsMapper->deleteByProductId($productId);
            if (!empty($companyProducts)) {
                if(!is_array($companyProducts)) {
                    $companyProducts = explode(',', $companyProducts);
                }
                $companyProductsMapper->processData($productId, $companyProducts);
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
     * @param Models_Model_Product $model
     */
    private function _processAllowance(Models_Model_Product $model) {
        $allowanceProductsMapper = Store_Mapper_AllowanceProductsMapper::getInstance();

        $allowanceProduct = $allowanceProductsMapper->findByProductId($model->getId());

        if (!$allowanceProduct instanceof Store_Model_AllowanceProducts) {
            $allowanceProduct = new Store_Model_AllowanceProducts();
            $allowanceProduct->setProductId($model->getId());
        }

        $allowanceProduct->setAllowanceDue($model->getAllowance());
        $allowanceProductsMapper->save($allowanceProduct);
    }

    /**
     * @param $productId
     * @return mixed
     * @throws Exception
     */
    public function findByProductId($productId) {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $productId);
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_product')->where($where);

        return $this->getDbTable()->getAdapter()->fetchRow($select);
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

    /**
     * Search products by name, sku, mpn, brand name, tag name
     *
     * @param $searchTerm
     * @return mixed
     * @throws Exception
     */
    public function buildIndex($searchTerm){
        $db = $this->getDbTable()->getAdapter();

        $where = $this->getDbTable()->getAdapter()->quoteInto('sp.name LIKE ?', '%' . $searchTerm . '%');
        $where .= ' OR ' .  $this->getDbTable()->getAdapter()->quoteInto('sp.sku LIKE ?', '%' . $searchTerm . '%');
        $where .= ' OR ' .  $this->getDbTable()->getAdapter()->quoteInto('sp.mpn LIKE ?', '%' . $searchTerm . '%');
        $where .= ' OR ' .  $this->getDbTable()->getAdapter()->quoteInto('sb.name LIKE ?', '%' . $searchTerm . '%');
        $where .= ' OR ' .  $this->getDbTable()->getAdapter()->quoteInto('st.name LIKE ?', '%' . $searchTerm . '%');

        $select = $db->select()->from(array('sp' => 'shopping_product'), array(
            'prod' => 'sp.name'
        ))
            ->join(array('sb' => 'shopping_brands'), 'sb.id = sp.brand_id', array())
            ->joinLeft(array('spht' => 'shopping_product_has_tag'), 'spht.product_id = sp.id', array())
            ->joinLeft(array('st' => 'shopping_tags'), 'spht.tag_id = st.id', array())
            ->where($where)
            ->group('sp.id');

        $data = $db->fetchCol($select);

        return $data;
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

    /**
     * Get products quantity with empty param
     *
     * @param string $paramName shopping product table field name
     * @return mixed
     * @throws Exception
     */
    public function countProductsWithoutParam($paramName)
    {
        $select = $this->getDbTable()->getAdapter()
            ->select()
            ->from('shopping_product', array('count' => 'COUNT(id)'))
            ->where($paramName.' = 0 OR '.$paramName.' IS NULL');
        $result = $this->getDbTable()->getAdapter()->fetchAll($select);

        return $result[0]['count'];
    }

    /**
     * Get products dimensions and weight
     *
     * @return mixed
     * @throws Exception
     */
    public function fetchDimensionsProducts()
    {
        $select = $this->getDbTable()->getAdapter()
            ->select()
            ->from('shopping_product', array(
                'product_id' => 'id',
                'prod_length',
                'prod_depth',
                'prod_width',
                'weight'
            ));
        $result = $this->getDbTable()->getAdapter()->fetchAssoc($select);

        return $result;
    }

    /**
     * Find product model by sku
     *
     * @param string $sku product sku
     * @return Models_Model_Product|null
     * @throws Exception
     */
    public function findBySku($sku)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('sku = ?', $sku);
        return $this->_findWhere($where);
    }

    /**
     * Get products Inventory
     *
     * @return mixed
     * @throws Exception
     */
    public function getProductsInventory()
    {
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_product', array(
            'inventory' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT(inventory))')
        ));

        return $this->getDbTable()->getAdapter()->fetchRow($select);
    }

}
