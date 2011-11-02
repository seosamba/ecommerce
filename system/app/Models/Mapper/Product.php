<?php

/**
 * Product
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Product extends Application_Model_Mappers_Abstract {

	protected $_dbTable	= 'Models_DbTable_Product';
	
	protected $_model	= 'Models_Model_Product';
	
	protected $_brandMapper = null;
	
	public function __construct() {
		$this->_brandMapper = Models_Mapper_Brand::getInstance();
	}

	
	public function save($model) {
		if (!$model instanceof $this->_model){
			if (isset($model['brand']) && !empty ($model['brand'])){
				if (!$brand = $this->_brandMapper->findByName($model['brand'])){
					$brand = $this->_brandMapper->save(array('name' => $model['brand']));
				}
				unset($model['brand']);
			}
			if (isset($model['categories'])){
				$categories = $model['categories'];
				unset($model['categories']);
			}
			if (isset($model['options'])) {
				$options = $model['options'];
				unset($model['options']);
			}
			$model = new $this->_model($model);
			
		}
		$data = array(
			'parent_id' => $model->getParentId(),
			'sku'	=> $model->getSku(),
			'name' => $model->getName(),
			'photo' => $model->getPhoto(),
			'mpn' => $model->getMpn(),
			'weight' => $model->getWeight(),
			'short_description'	=> $model->getShortDescription(),
			'full_description' => $model->getFullDescription(),
			'price' => $model->getPrice(),
			'tax_class' => $model->getTaxClass()
			);
		
		if (isset($brand)){
			$model->setBrand($brand->getName());
			$data['brand_id'] = $brand->getId();
		}
		
		if ($model->getId()){
			$data['updated_at'] = date(DATE_ATOM);
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			try {
				$result = $this->getDbTable()->update($data, $where);
			} catch (Exception $e) {
				error_log($e->getMessage());
				return null;
			}
		} else {
			$data['created_at'] = date(DATE_ATOM);
			try{
				$id = $this->getDbTable()->insert($data);
			} catch (Exception $e){
				error_log($e->getMessage());
				return null;
			}
			if ($id){
				$model->setId($id);
			}
		}
		
		if (isset($categories)) {
			$productCategoryTable = new Models_DbTable_ProductCategory();
			$productCategoryTable->getAdapter()->beginTransaction();
			$productCategoryTable->delete($productCategoryTable->getAdapter()->quoteInto('product_id = ?', $model->getId()));
			foreach ($categories as $category) {
				$productCategoryTable->insert(array(
					'product_id' => $model->getId(),
					'category_id' => $category['id']
				));
			}
			$productCategoryTable->getAdapter()->commit();
			$model->setCategories($categories);
		}
		
		if (isset($options)){
			$this->_proccessOptions($model, $options);
		}
		
		return $model;
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

	private function _toModel(Zend_Db_Table_Row_Abstract $row){
		$entity = new $this->_model($row->toArray());
		
		if ($row->brand_id){
			$brandRow = $row->findDependentRowset('Models_DbTable_Brand');
			if ($brandRow->count()){
				$entity->setBrand($brandRow->current()->name);
			}
		}
		
		$categorySet = $row->findManyToManyRowset('Models_DbTable_Category','Models_DbTable_ProductCategory');
		if ($categorySet->count()){
			$entity->setCategories($categorySet->toArray());
		}
		
		$optionSet = $row->findDependentRowset('Models_DbTable_ProductOption');
		
		if ($optionSet->count()) {
			$options = array();
			$optionMapper = Models_Mapper_Option::getInstance();
			foreach ($optionSet as $optionRow) {
				array_push($options, $optionMapper->find($optionRow->option_id));
			}
			$entity->setDefaultOptions($options);
		}
		
		return $entity;
	}
	
	private function _proccessOptions(Models_Model_Product $model, $options){
		$optionMapper = Models_Mapper_Option::getInstance();

		$relationTable = new Models_DbTable_ProductOption();
		$currentList = $relationTable->fetchAll($relationTable->getAdapter()->quoteInto('product_id = ?', $model->getId()));
		$cList = $currentList->toArray();
		
		$ids = array();
		foreach ($options as $option) {
			if ( !isset($option['title']) || empty($option['title']) ) {
				continue;
			} else {
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
}