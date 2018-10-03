<?php

/**
 * Store suppliers products REST API controller
 * Assign suppliers to specific product
 *
 * @package Store
 * @since 2.5.2
 */
class Api_Store_Companyproducts extends Api_Service_Abstract
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
     * /api/store/companyproducts/
     *
     *
     * @return json Set of suppliers
     */
    public function getAction()
    {
        $groupByCompany = filter_var($this->_request->getParam('groupByCompany', false), FILTER_SANITIZE_NUMBER_INT);
        $productIds = array_unique(array_filter(explode(',', filter_var($this->_request->getParam('productIds', false), FILTER_SANITIZE_STRING))));
        $companyProductsMapper = Store_Mapper_CompanyProductsMapper::getInstance();
        $where = null;
        if (!empty($productIds)) {
            $where = $companyProductsMapper->getDbTable()->getAdapter()->quoteInto('product_id IN (?)', $productIds);
        }
        if (!empty($groupByCompany)) {
            $companyProductsData = $companyProductsMapper->fetchAllData($where, array(), array('ssp.company_id'));
        } else {
            $companyProductsData = $companyProductsMapper->fetchAll();
        }
        return array_map(function ($company) {
            $item = $company->toArray();

            return $item;
        }, $companyProductsData);
    }

    /**
     * Reserved for future usage
     */
    public function postAction()
    {
        $postData =  filter_var_array($this->_request->getParams(), FILTER_SANITIZE_STRING);

        if (!empty($postData['companies']) && !empty($postData['productIds'])) {
            $companyProductsMapper = Store_Mapper_CompanyProductsMapper::getInstance();
            foreach ($postData['productIds'] as $productId) {
                if (!empty($postData['removeOldCompanies'])) {
                    $companyProductsMapper->deleteByProductId($productId);
                }
                $companyProductsMapper->processData($productId, $postData['companies']);
            }

        } else {
            $translator = Zend_Registry::get('Zend_Translate');
            $responseHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('response');
            $responseHelper->fail($translator->translate('No data provided'));
        }
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
