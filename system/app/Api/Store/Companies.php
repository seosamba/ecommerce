<?php

/**
 * Store companies REST API controller
 *
 *
 * @package Store
 * @since 2.5.2
 */
class Api_Store_Companies extends Api_Service_Abstract
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
     * /api/store/companies/
     *
     *
     * @return json Set of suppliers
     */
    public function getAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_STRING);
        if ($id) {
            $storeMapper = Store_Model_Companies::getInstance();
            $where = $storeMapper->getDbTable()->getAdapter()->quoteInto('id=?', $id);
            $data = $storeMapper->fetchAll($where);
        } else {
            $data = Store_Mapper_CompaniesMapper::getInstance()->fetchAll();
        }

        return array_map(function ($group) {
            return $group->toArray();
        }, $data);

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
