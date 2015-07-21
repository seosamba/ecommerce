<?php
/**
 * Class Api_Store_Pickuplocationcategories
 */
class Api_Store_Pickuplocationcategories extends Api_Service_Abstract
{

    const PICKUPLOCATIONS_SECURE_TOKEN = 'PickupLocationsToken';

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
            'allow' => array('get', 'post', 'put', 'delete')
        )
    );

    public function getAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        $pickupLocationMapper = Store_Mapper_PickupLocationCategoryMapper::getInstance();
        if ($id) {
            $data = $pickupLocationMapper->find($id);
        } else {
            $data = $pickupLocationMapper->fetchAll();
        }
        return array_map(
            function ($pickupLocationCategory) {
                return $pickupLocationCategory->toArray();
            },
            $data
        );
    }

    public function postAction()
    {
        $data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);
        $pickupLocationCategoryMapper = Store_Mapper_PickupLocationCategoryMapper::getInstance();
        $pickupLocationModel = new Store_Model_PickupLocationCategory();
        if (empty($data)) {
            $this->_error();
        }
        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::PICKUPLOCATIONS_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        $pickupLocationModel->setName($data['name']);
        $result = $pickupLocationCategoryMapper->save($pickupLocationModel);
        return $result;
    }

    public function putAction()
    {
        $data = Zend_Json::decode($this->_request->getRawBody());
        $id           = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
        $categoryName = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $categoryImg  = filter_var($data['img'], FILTER_SANITIZE_STRING);

        if (!$id || !$categoryName) {
            $this->_error();
        }
        $pickupLocationCategoryMapper = Store_Mapper_PickupLocationCategoryMapper::getInstance();
        $pickupLocationCategory = $pickupLocationCategoryMapper->find($id);
        if ($pickupLocationCategory instanceof Store_Model_PickupLocationCategory) {
            $pickupLocationCategory->setName($categoryName);
            if (!empty($categoryImg)) {
                $pickupLocationCategory->setImg($categoryImg);
            }

            $pickupLocationCategoryMapper->save($pickupLocationCategory);
            return $pickupLocationCategory->toArray();
        } else {
            $this->_error();
        }
    }

    public function deleteAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            $this->_error();
        }

        return Store_Mapper_PickupLocationCategoryMapper::getInstance()->delete($id);
    }


}
