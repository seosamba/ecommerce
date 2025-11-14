<?php

class Api_Productsdashboard_Productsgridinfo extends Api_Service_Abstract
{



    /**
     * System response helper
     *
     * @var null
     */
    protected $_responseHelper = null;

    /**
     * User restricted id access
     *
     * @var null
     */
    protected $_restrictedUserId = null;

    /**
     * User restricted lifecycle id
     *
     * @var null
     */
    protected $_restrictedLifeCycleId = 0;

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

    public function init()
    {
        parent::init();
        $this->_responseHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('response');
        $this->_restrictedUserId = Tools_LeadTools::getUserIdIfActionNotAllowed(Shopping::ROLE_SALESPERSON);
        $this->_loggedUserRoleId = Zend_Controller_Action_HelperBroker::getStaticHelper('session')->getCurrentUser()->getRoleId();
        $this->_restrictedLifeCycleId = Tools_LeadTools::getLifecycleIdIfActionNotAllowed(Shopping::ROLE_SALESPERSON);

    }


    /**
     * Leads grid info
     *
     * Resource:
     * : api/productsdashboard/productsgridinfo/
     *
     * HttpMethod:
     * : GET
     *
     * @return JSON
     */
    public function getAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        if (!empty($id)) {

        }

        $brands = Models_Mapper_Brand::getInstance()->getBrands(array('name'));
        $tags = Models_Mapper_Tag::getInstance()->getTags(array('name'));
        $inventoryData = array(array('id' => 'unlimited', 'name' => 'unlimited', 'label' => 'unlimited'));

        $productsData = Models_Mapper_ProductMapper::getInstance()->getProductsInventory();

        if(!empty($productsData['inventory'])){
            $inventory =  explode(',' , $productsData['inventory']);
            sort($inventory, SORT_NUMERIC);
            foreach($inventory as $inventoryItem){
                array_push($inventoryData, array('id' => $inventoryItem, 'name' => $inventoryItem, 'label' => $inventoryItem));
            }
        }


        $data = array();
        $data['data'] = array();
        $data['gridInfo'] = array(
            'brands' => $brands,
            'tags' => $tags,
            'inventory' => $inventoryData,
        );

        $data['gridInfo']['restrictedUserId'] = Tools_LeadTools::getUserIdIfActionNotAllowed(Shopping::ROLE_SALESPERSON);
        $data['currencyInfo'] = Tools_LeadWelcomeScreenTools::getCurrencyInfo();
        $currency = Zend_Registry::get('Zend_Currency');
        $currencySymbol = preg_replace('~[\w]~', '', $currency->getSymbol());
        $data['currencyInfo']['currencySymbol'] = $currencySymbol;

        return $data;
    }

    /**
     *
     * Resource:
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON
     */
    public function postAction()
    {

    }

    /**
     *
     * Resource:
     *
     * HttpMethod:
     * : PUT
     *
     * ## Parameters:
     * id (source integer)
     *
     * @return JSON
     */
    public function putAction()
    {

    }

    /**
     *
     * Resource:
     *
     * HttpMethod:
     * : DELETE
     *
     * ## Parameters:
     * id (source integer)
     *
     * @return JSON
     */
    public function deleteAction()
    {


    }

}
