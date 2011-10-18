<?php

/**
 * Product
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Product extends Application_Model_Mappers_Abstract {

	protected $_dbTable	= 'Models_DbTable_Product';
	
	protected $_model	= 'Models_Model_Product';
	
	public function save($model) {
		
	}
	
	public function fetchAll($where = null, $order = array()) {
		$entries = array();
		$resultSet = $this->getDbTable()->fetchAll($where, $order);
		if(null === $resultSet) {
			return null;
		}
		foreach ($resultSet as $row) {
			$entry = new $this->_model($row->toArray());
			
			$brandRow = $row->findDependentRowset('Models_DbTable_Brand');
			if ($brandRow->count()){
				$brand = new Models_Model_Brand($brandRow->current()->toArray());
				$entry->setBrand($brand);
			}
			
			$categorySet = $row->findManyToManyRowset('Models_DbTable_Category','Models_DbTable_ProductCategory');
			if ($categorySet->count()){
				$categories = array();
				foreach ($categorySet as $cat) {
					array_push($categories, new Models_Model_Category($cat->toArray()));
				}
				
				$entry->setCategories($categories);
			}
			
			array_push($entries, $entry);
		}
		return $entries;
	}

	public function find($id) {
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return null;
		}
		$row = $result->current();
		return new $this->_model($row->toArray());
	}


}