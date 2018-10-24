<?php

/**
 * Store suppliers REST API controller
 *
 *
 * @package Store
 * @since 2.5.2
 */
class Api_Store_Suppliers extends Api_Service_Abstract
{

    /**
     * @var array Access Control List
     */
    protected $_accessList = array(
        Tools_Security_Acl::ROLE_SUPERADMIN => array(
            'allow' => array('get', 'post')
        ),
        Tools_Security_Acl::ROLE_ADMIN => array(
            'allow' => array('get', 'post')
        ),
        Shopping::ROLE_SALESPERSON => array(
            'allow' => array('get', 'post')
        )
    );

    /**
     * Returns assigned suppliers list
     *
     * /api/store/suppliers/
     *
     *
     * @return json Set of suppliers
     */
    public function getAction()
    {
        $userId = $this->_request->getParam('id', false);
        $userMapper = Application_Model_Mappers_UserMapper::getInstance();
        $where = $userMapper->getDbTable()->getAdapter()->quoteInto('role_id = ?', Shopping::ROLE_SUPPLIER);
        if (!empty($userId)) {
            $where .= ' AND ' . $userMapper->getDbTable()->getAdapter()->quoteInto('id = ?', $userId);

        }
        $suppliersList = Application_Model_Mappers_UserMapper::getInstance()->fetchAll($where);

        return array_map(function ($supplier) {
            $item = $supplier->toArray();

            return $item;
        }, $suppliersList);
    }

    /**
     * Reserved for future usage
     */
    public function postAction()
    {

    }

    /**
     * Reserved for future usage
     */
    public function putAction()
    {

    }

    /**
     * Reserved for future usage
     */
    public function deleteAction()
    {

    }

}
