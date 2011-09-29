<?php

/**
 * Tax
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Tax extends Application_Model_Mappers_Abstract{

	protected $_dbTable	= 'Models_DbTable_Tax';

	protected $_model	= 'Models_Model_Tax';

	public function save($data) {
		if (!$data instanceof $this->_model){
			$data = new $this->_model($data);
		}
		
		return $data;
	}

}