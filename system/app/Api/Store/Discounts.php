<?php
/**
 * Discounts REST API controller
 *
 * Class Api_Store_Discounts
 * @since 2.3.1
 *
 */
class Api_Store_Discounts extends Api_Service_Abstract {


    const QUANTITY_DISCOUNT_GLOBAL = 'global';

    const QUANTITY_DISCOUNT_LOCAL = 'local';

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

    /**
     * Get discounts data
     *
     * Resource:
     * : /api/store/discounts/id/:id
     *
     * HttpMethod:
     * : GET
     *
     * ## Parameters:
     * id (type integer)
     * : Id
     *
     * pairs (type sting)
     * : If given data will be returned as key-value array
     *
     * @return JSON List of discounts
     */
	public function getAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        $discountMapper = Store_Mapper_DiscountMapper::getInstance();
        if ($id) {
            $where = $discountMapper->getDbTable()->getAdapter()->quoteInto('id=?', $id);
            $data = $discountMapper->fetchAll($where);
		} else {
			$data = $discountMapper->fetchAll();
		}
        return array_map(function ($discount) {
				return $discount->toArray();
		}, $data);

	}

    /**
     * New discount creation
     *
     * Resource:
     * : /api/store/discounts/
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON New discount model
     */
	public function postAction() {
		$data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);

        $translator = Zend_Registry::get('Zend_Translate');
		if (!isset($data['discountQuantity']) && !isset($data['discountAmount'])) {
			$this->_error($translator->translate('Quantity value must be numeric'));
		}

        if(!is_int(intval($data['discountQuantity']))){
            $this->_error($translator->translate('Quantity value must be numeric'));
        }

        if(!is_numeric($data['discountAmount'])){
            $this->_error($translator->translate('Price value must be numeric'));
        }
        if($data['applyScope']){
            $data['applyScope'] = self::QUANTITY_DISCOUNT_GLOBAL;
        }else{
            $data['applyScope'] = self::QUANTITY_DISCOUNT_LOCAL;
        }

		$model = new Store_Model_Discount($data);
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$model->{'set' . ucfirst($key)}($value);
			}
		}
        Store_Mapper_DiscountMapper::getInstance()->save($model);
		return $model->toArray();
	}

	public function putAction() {


    }

    /**
     * Delete discount
     *
     * Resource:
     * : /api/store/discounts/
     *
     * HttpMethod:
     * : DELETE
     *
     * ## Parameters:
     * id (type integer)
     * : discount ID to delete
     *
     * @return JSON Result of operations
     */
	public function deleteAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

		if (!$id) {
			$this->_error();
		}
		return Store_Mapper_DiscountMapper::getInstance()->delete($id);
	}


}
