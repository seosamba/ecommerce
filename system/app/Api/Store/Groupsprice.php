<?php
/**
 * Groups REST API controller
 *
 *
 * @package Store
 * @since   2.0.0
 */
class Api_Store_Groupsprice extends Api_Service_Abstract {

    const GROUPS_PRICE_SECURE_TOKEN = 'GroupsPriceToken';

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
     * Get groups price data
     *
     * Resourse:
     * : /api/store/groupsprice/
     *
     * HttpMethod:
     * : GET
     *
     * ## Parameters:
     * * productId (type integer)
     * : productID
     *
     * pairs (type sting)
     * : If given data will be returned as key-value array
     *
     * @return JSON List of groups price
     */
	public function getAction() {
		$productId = filter_var($this->_request->getParam('productId'), FILTER_SANITIZE_NUMBER_INT);
        $groupPriceMapper = Store_Mapper_GroupPriceMapper::getInstance();
        $allGroups = Store_Mapper_GroupMapper::getInstance()->fetchAll();
        if ($productId) {
		    $where = $groupPriceMapper->getDbTable()->getAdapter()->quoteInto('productId = ?', $productId);
            $data = $groupPriceMapper->fetchAll($where);
		} else {
			$data = $groupPriceMapper->fetchAll();
		}

        if(empty($data)){
            return array_map(function ($groups) {
                return $groups->toArray();
            }, $allGroups);
        }else{
            $allGroup = array();
            foreach($allGroups as $group){
                $allGroup[$group->getId()]['groupName']  = $group->getGroupName();
                $allGroup[$group->getId()]['priceValue'] = $group->getPriceValue();
                $allGroup[$group->getId()]['priceSign']  = $group->getPriceSign();
                $allGroup[$group->getId()]['priceType']  = $group->getPriceType();
                $allGroup[$group->getId()]['id'] = $group->getId();
            }
            foreach($data as $groupProduct){
                $groupId = $groupProduct->getGroupId();
                if(array_key_exists($groupId, $allGroup)){
                    $allGroup[$groupId]['priceValue'] = $groupProduct->getPriceValue();
                    $allGroup[$groupId]['priceSign'] = $groupProduct->getPriceSign();
                    $allGroup[$groupId]['priceType'] = $groupProduct->getPriceType();
                }
            }
            $result = array_values($allGroup);
            return $result;
        }


	}

    /**
     * New group price creation
     *
     * Resourse:
     * : /api/store/groupsprice/
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON New group price model
     */
	public function postAction() {
		$data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);
        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
		if (empty($data)) {
			$this->_error();
		}

        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::GROUPS_PRICE_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        unset($data[Tools_System_Tools::CSRF_SECURE_TOKEN]);

        if(!is_numeric($data['priceValue'])){
            return false;
        }

        $model = new Store_Model_GroupPrice($data);
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$model->{'set' . ucfirst($key)}($value);
			}
		}

		Store_Mapper_GroupPriceMapper::getInstance()->save($model);
        $cache->clean('', '', array('0'=>'product_price', '1'=>'prodid_'.$data['productId']));
        $cache->clean('products_groups_price', 'store_');
        $cache->clean('customers_groups', 'store_');
		return $model->toArray();
	}

	public function putAction() {
	}

    /**
     * Delete group price
     *
     * Resourse:
     * : /api/store/groupsprice/
     *
     * HttpMethod:
     * : DELETE
     *
     * ## Parameters:
     * id (type integer)
     * : Group Id
     *
     * productId (type integer)
     * : product Id
     *
     * @return JSON Result of operations
     */
	public function deleteAction() {
        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
        $groupId = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        $productId = filter_var($this->_request->getParam('productId'), FILTER_SANITIZE_NUMBER_INT);

		if (!$groupId || !$productId) {
			$this->_error();
		}

        $cache->clean('', '', array('0'=>'product_price', '1'=>'prodid_'.$productId));
        $cache->clean('products_groups_price', 'store_');
        $cache->clean('customers_groups', 'store_');
		return Store_Mapper_GroupPriceMapper::getInstance()->delete($groupId, $productId);
	}


}
