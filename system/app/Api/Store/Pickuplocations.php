<?php
/**
 * Class Api_Store_Pickuplocations
 */
class Api_Store_Pickuplocations extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN      => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Shopping::ROLE_SALESPERSON          => array(
			'allow' => array('get', 'post', 'put', 'delete')
		)
	);

	public function getAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        $categoryId = filter_var($this->_request->getParam('categoryId'), FILTER_SANITIZE_NUMBER_INT);
        $pickupLocationMapper = Store_Mapper_PickupLocationMapper::getInstance();
        if($id) {
		    $data = $pickupLocationMapper->find($id);
		}elseif($categoryId) {
			$data = $pickupLocationMapper->fetchByCategory($categoryId);
		}else{
            $data = $pickupLocationMapper->fetchAll();
        }
		return array_map(function ($pickupLocation) {
			return $pickupLocation->toArray();
		}, $data);
    }

	public function postAction() {
		$data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);
        $pickupLocationMapper = Store_Mapper_PickupLocationMapper::getInstance();
        $pickupLocationModel = new Store_Model_PickupLocation();
		if (empty($data)) {
			$this->_error();
		}
        $pickupLocationModel->setAddress1($data['address1']);
        $pickupLocationModel->setAddress2($data['address2']);
        $pickupLocationModel->setCountry($data['country']);
        $pickupLocationModel->setCity($data['city']);
        $pickupLocationModel->setPhone($data['phone']);
        $pickupLocationModel->setZip($data['zip']);
        $pickupLocationModel->setWorkingHours(serialize($data['working_hours']));
        $pickupLocationModel->setName($data['location-name']);
        $pickupLocationModel->setLocationCategoryId($data['categoryId']);
        $pickupLocationMapper->save($pickupLocationModel);

        $translator = Zend_Registry::get('Zend_Translate');


	}

	public function putAction() {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            $this->_error();
        }
	}

	public function deleteAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

		if (!$id) {
			$this->_error();
		}

		return Store_Mapper_PickupLocationMapper::getInstance()->delete($id);
	}


}
