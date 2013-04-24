<?php
/**
 * Group
 *
 *
 */
class Store_Model_Group extends Application_Model_Models_Abstract {

	protected $_groupName;

	public function setGroupName($groupName) {
		$this->_groupName = $groupName;
		return $this;
	}

	public function getGroupName() {
		return $this->_groupName;
	}

}