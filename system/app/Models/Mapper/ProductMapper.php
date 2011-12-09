<?php

/**
 * ProductMapper
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
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
			'parent_id' => $model->getParentId(),
			'sku'	=> $model->getSku(),
			'name' => $model->getName(),
			'photo' => $model->getPhoto(),
			'brand_id'  => isset($brand)?$brand->getId():null,
			'mpn' => $model->getMpn(),
			'weight' => $model->getWeight(),
			'short_description'	=> $model->getShortDescription(),
			'full_description' => $model->getFullDescription(),
			'price' => $model->getPrice(),
			'tax_class' => $model->getTaxClass()
			);

		if ($model->getId()){
			$data['updated_at'] = date(DATE_ATOM);
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			$result = $this->getDbTable()->update($data, $where);
		} else {
			$data['created_at'] = date(DATE_ATOM);
			$id = $this->getDbTable()->insert($data);
			if ($id){
				$model->setId($id);
			}
		}

		if ($model->getCategories()) {
			$productCategoryTable = new Models_DbTable_ProductCategory();
			$productCategoryTable->getAdapter()->beginTransaction();
			$productCategoryTable->delete($productCategoryTable->getAdapter()->quoteInto('product_id = ?', $model->getId()));
			foreach ($model->getCategories() as $category) {
				$productCategoryTable->insert(array(
					'product_id' => $model->getId(),
					'category_id' => $category['id']
				));
			}
			$productCategoryTable->getAdapter()->commit();
		}

		if ($model->getDefaultOptions()){
			$this->_processOptions($model);
		}

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

	public function fetchAll($where = null, $order = array()) {
		$entities = array();
		$resultSet = $this->getDbTable()->fetchAll($where, $order);
		if(null === $resultSet) {
			return null;
		}
		foreach ($resultSet as $row) {
			$entity = $this->_toModel($row);

			array_push($entities, $entity);
		}
		return $entities;
	}

	public function find($id) {
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return null;
		}
		$row = $result->current();

		return $this->_toModel($row);
	}
	
	public function findByPageId($id) {
		$products = $this->fetchAll(array('page_id = ?' => $id));
		if (!empty ($products)){
			return $products[0];
		}
		return null;
	}

    /**
     * @var Models_Model_Product $entity
     * @param Zend_Db_Table_Row_Abstract $row
     * @return mixed
     */
	private function _toModel(Zend_Db_Table_Row_Abstract $row){
		$entity = new $this->_model($row->toArray());

        if ($row->brand_id){
			$brandRow = $row->findDependentRowset('Models_DbTable_Brand');
			if ($brandRow->count()){
				$entity->setBrand($brandRow->current()->name);
			}
		}

		//fetching categories
		$categorySet = $row->findManyToManyRowset('Models_DbTable_Category','Models_DbTable_ProductCategory');
		if ($categorySet->count()){
			$entity->setCategories($categorySet->toArray());
		}

		//fetching options
		$optionSet = $row->findDependentRowset('Models_DbTable_ProductOption');
		if ($optionSet->count()) {
			$options = array();
			$optionMapper = Models_Mapper_OptionMapper::getInstance();
			foreach ($optionSet as $optionRow) {
				$opt = $optionMapper->find($optionRow->option_id);
				if ($opt){
					array_push($options, $opt->toArray());
				}
			}
			$entity->setDefaultOptions($options);
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
			$pageMapper = Application_Model_Mappers_PageMapper::getInstance();
			$page = $pageMapper->find($row->page_id);
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

		$ids = array();
		foreach ($model->getDefaultOptions() as $option) {
			if ( !isset($option['title']) || empty($option['title']) ) {
				continue;
			} else {
                if (isset($option['isTemplate']) && $option['isTemplate'] === true){
                    $template = $optionMapper->save( array(
                        'title'     => isset($option['templateName']) && !empty($option['templateName']) ? $option['templateName'] : 'template-'.$option['title'],
                        'type'      => $option['type'],
                        'parentId'  => '0'
                    ) );
                    $option['parentId'] = $template->getId();
                    unset($template);
                }
                $result = $optionMapper->save($option);
			}
			array_push($ids, $result->getId());
		}

		foreach ($currentList as $row) {
			if (!in_array($row->option_id, $ids)){
				$row->delete();
			}
		}

		foreach ($ids as $optionId){
			$row = $relationTable->find($model->getId(), $optionId);
			if (!$row->count()){
				$row = $relationTable->createRow(array(
					'product_id' => $model->getId(),
					'option_id'	 => $optionId
					));
				$row->save();
			}
		}

		return $ids;
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

	public function findByCategories(array $categories) {
		$products         = array();
		$filteredProducts = array();
		$catDbTable       = new Models_DbTable_Category();
		if(!empty($categories)) {
			foreach($categories as $cId) {
				$catRow         = $catDbTable->find($cId)->current();
			 	$productsRowset = $catRow->findManyToManyRowset('Models_DbTable_Product', 'Models_DbTable_ProductCategory');
				foreach($productsRowset as $productRow) {
					$productModel = $this->_toModel($productRow);
					$modelHash = md5($productModel->getId());
					if(!array_key_exists($modelHash, $products)) {
						$products[$modelHash] = $productModel;
					}
				}
				$filteredProducts = (empty($filteredProducts)) ? $products : array_intersect_key($products, $filteredProducts);
				$products = array();
			}
		}
		return array_values($filteredProducts);
	}

	public function findByBrands(array $brands) {
		$products     = array();
		$brandDbTable = new Models_DbTable_Brand();
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
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $product->getId());

		$status = $this->getDbTable()->delete($where);

        if ($status) {
            $brand = $this->_brandMapper->findByName($product->getBrand());
            if ($brand) {
                $prodList = $this->fetchAll($this->getDbTable()->getAdapter()->quoteInto('brand_id = ?', $brand->getId()));
                if (empty($prodList)){
                    $this->_brandMapper->delete($brand);
                }
            }
            // removing page
            if ($product->getPage()){
                Application_Model_Mappers_PageMapper::getInstance()->delete($product->getPage());
            }

            return true;
        } else {
            return false;
        }
	}
}