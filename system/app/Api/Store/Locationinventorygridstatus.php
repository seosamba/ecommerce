<?php
class Api_Store_LocationInventoryGridStatus extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN => array(
            'allow' => array('get', 'post', 'put', 'delete')
		),
		Shopping::ROLE_SALESPERSON => array(
            'allow' => array('get', 'post')
		)
	);

    public function init()
    {
        parent::init();
        $this->_responseHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('response');
    }

	public function getAction() {
        $orderIds = filter_var($this->_request->getParam('orderIds'), FILTER_SANITIZE_STRING);

        $data = array();
        if (!empty($orderIds)) {
            $cartLocationInventoryMapper = Models_Mapper_CartLocationInventoryMapper::getInstance();
            $orderIds = array_filter(array_map('trim', explode(',', $orderIds)));

            $cartLocationInventoryData = $cartLocationInventoryMapper->getLocationInventoryInfoByCartIds($orderIds);

            if(!empty($cartLocationInventoryData)) {
                foreach ($cartLocationInventoryData as $cartLocationInventory) {
                    if($cartLocationInventory['product_status'] != 'new') {
                        if(!empty($data[$cartLocationInventory['cart_id']]) && $data[$cartLocationInventory['cart_id']] == 'new') {
                            continue;
                        }
                        $data[$cartLocationInventory['cart_id']] = 'fulfilled';
                    } else {
                        $data[$cartLocationInventory['cart_id']] = 'new';
                    }
                }
            }
        }

        return $data;
	}

	/**
	 * Reserved for future usage
	 */
	public function postAction() {
		// TODO: Implement postAction() method.
	}

	/**
	 * Reserved for future usage
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

	/**
	 * Reserved for future usage
	 */
	public function deleteAction() {
		// TODO: Implement deleteAction() method.
	}

}
