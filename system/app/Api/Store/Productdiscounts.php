<?php
/**
 * Coupons REST API controller
 * @author  Vitaly Vyrodov <gtopbox@gmail.com>
 *
 * @package Store
 * @since   2.3.2
 */
class Api_Store_Productdiscounts extends Api_Service_Abstract {
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
        $quantity = filter_var($this->_request->getParam('quantity'), FILTER_SANITIZE_NUMBER_INT);
        if ($id && empty($quantity)) {
            $discountMapper = Store_Mapper_DiscountMapper::getInstance();
            $data = $discountMapper->getDiscountDataConfig($id);
            foreach ($data as &$discount) {
                $discount['id'] = null;
                $discount['productId'] = $id;
                $discount['priceSign'] = $discount['price_sign'];
                $discount['priceType'] = $discount['price_type'];
                if(empty($discount['status']))
                    $discount['status'] = '';
                unset($discount['price_type'], $discount['price_sign'], $discount['product_id']);
            }
            return  array_merge(array(), $data);

        } else {
            $discountMapper = Store_Mapper_DiscountProductMapper::getInstance();
            $where[] = $discountMapper->getDbTable()->getAdapter()->quoteInto('product_id = ?', $id);
            $where[] = $discountMapper->getDbTable()->getAdapter()->quoteInto('quantity = ?', $quantity);
            $data = $discountMapper->fetchAll($where);
            return array_map(function ($discount) {
                    return $discount->toArray();
                }, $data);
        }

    }

    /**
     * New discount creation
     *
     * Resource:
     * : /api/store/productdiscounts/
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON New discount model
     */
    public function postAction() {
        $data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);

        $translator = Zend_Registry::get('Zend_Translate');
        if (!isset($data['quantity']) && !isset($data['amount'])) {
            $this->_error($translator->translate('Quantity value must be numeric'));
        }

        if(!is_int(intval($data['quantity']))){
            $this->_error($translator->translate('Quantity value must be numeric'));
        }

        if(!is_numeric($data['amount'])){
            $this->_error($translator->translate('Price value must be numeric'));
        }

        if(empty($data['status'])){
            $data['status'] = 'enabled';
        }

        $model = new Store_Model_DiscountProduct($data);
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $model->{'set' . ucfirst($key)}($value);
            }
        }
        Store_Mapper_DiscountProductMapper::getInstance()->save($model);
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
        $rawParams = $this->_request->getParams();
        $params['productId'] = (int) $rawParams['id'];
        $params['quantity'] = (int) $rawParams['quantity'];
        if (!$params['productId'] || !$params['quantity']) {
            $this->_error();
        }
        $res = Store_Mapper_DiscountProductMapper::getInstance()->delete($params['productId'], $params['quantity']);
        if(!$res) {
            $model = new Store_Model_DiscountProduct($params);
            if (is_array($params)) {
                $params['amount'] = (int) $rawParams['amount'];
                $params['priceSign'] = filter_var($rawParams['priceSign'], FILTER_SANITIZE_STRING);
                $params['priceType'] = filter_var($rawParams['priceType'], FILTER_SANITIZE_STRING);
                $params['status'] = 'disabled';
                foreach ($params as $key => $value) {
                    $model->{'set' . ucfirst($key)}($value);
                }
            }
            Store_Mapper_DiscountProductMapper::getInstance()->save($model);
            $res = $model->toArray();
        }
        return $res;
    }


}
