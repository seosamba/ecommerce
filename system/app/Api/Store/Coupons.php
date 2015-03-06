<?php
/**
 * Coupons REST API controller
 * @author  Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @package Store
 * @since   2.0.0
 */
class Api_Store_Coupons extends Api_Service_Abstract {

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
		if ($id) {
			// return $model;
		} else {
			$productId = filter_var($this->getRequest()->getParam('productId'), FILTER_SANITIZE_NUMBER_INT);
			$where = array();
			if ($productId) {
				$data = Store_Mapper_CouponMapper::getInstance()->findByProductId($productId);
			} else {
				$data = Store_Mapper_CouponMapper::getInstance()->fetchAll();
			}
			return array_map(function ($coupon) {
				return $coupon->toArray();
			}, $data);
		}
	}

	public function postAction() {
		$data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);

		if (empty($data)) {
			$this->_error();
		}

        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, Shopping::SHOPPING_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        unset($data[Tools_System_Tools::CSRF_SECURE_TOKEN]);
        $translator = Zend_Registry::get('Zend_Translate');
		if (!isset($data['code']) || empty($data['code'])) {
			$this->_error($translator->translate('Missing mandatory code parameter'));
		} else {
			$validator = new Zend_Validate_Db_RecordExists(array(
				'table' => 'shopping_coupon',
				'field' => 'code'
			));

			if ($validator->isValid($data['code'])) {
				$this->_error($translator->translate('Coupon with such code already exists'));
			}
		}

		if (isset($data['startDate'])) {
			if (!empty($data['startDate'])) {
				$data['startDate'] = date(Tools_System_Tools::DATE_MYSQL, strtotime($data['startDate']));
			} else {
				unset($data['startDate']);
			}
		} else {
			$this->_error($translator->translate('Coupon start date should be specified'));
		}

		if (isset($data['endDate'])) {
			if (!empty($data['endDate'])) {
				$data['endDate'] = date(Tools_System_Tools::DATE_MYSQL, strtotime($data['endDate']));
			} else {
				unset($data['endDate']);
			}
		} else {
			$this->_error($translator->translate('Coupon end date should be specified'));
		}

		if (isset($data['scope']) && empty($data['scope'])) {
			unset($data['scope']);
		}

		$model = new Store_Model_Coupon($data);
		if (isset($data['data']) && is_array($data['data'])) {
			foreach ($data['data'] as $key => $value) {
				$model->{'set' . ucfirst($key)}($value);
			}
		}

		if (Store_Mapper_CouponMapper::getInstance()->save($model)) {
		}

		return $model->toArray();
	}

	public function putAction() {
	}

	public function deleteAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

		if (!$id) {
			$this->_error();
		}

		return Store_Mapper_CouponMapper::getInstance()->delete($id);
	}


}
