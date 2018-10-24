<?php

/**
 * Store suppliers products REST API controller
 * Assign suppliers to specific product
 *
 * @package Store
 * @since 2.5.2
 */
class Api_Store_Companysuppliers extends Api_Service_Abstract
{

    /**
     * @var array Access Control List
     */
    protected $_accessList = array(
        Tools_Security_Acl::ROLE_SUPERADMIN => array(
            'allow' => array('get', 'post', 'delete')
        ),
        Tools_Security_Acl::ROLE_ADMIN => array(
            'allow' => array('get', 'post', 'delete')
        ),
        Shopping::ROLE_SALESPERSON => array(
            'allow' => array('get', 'post', 'delete')
        )
    );

    /**
     * Returns assigned suppliers list
     *
     * /api/store/companysuppliers/
     *
     *
     * @return json Set of suppliers
     */
    public function getAction()
    {

    }

    /**
     * Reserved for future usage
     */
    public function postAction()
    {
        $supplierIds = $this->_request->getParam('suppliersIds');
        $companyName = trim(filter_var($this->_request->getParam('companyName'), FILTER_SANITIZE_STRING));
        $companyId = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        $responseHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('response');
        $translator = Zend_Registry::get('Zend_Translate');
        if (empty($supplierIds)) {
            $responseHelper->fail($translator->translate('Missing suppliers ids'));
        }

        if (empty($companyId) && empty($companyName)) {
            $responseHelper->fail($translator->translate('No data provided'));
        }

        $companyMapper = Store_Mapper_CompaniesMapper::getInstance();

        if (empty($companyId)) {
            $companyModel = $companyMapper->getByCompanyName($companyName);
            if ($companyModel instanceof Store_Model_Companies) {
                $responseHelper->fail($translator->translate('Company already exists'));
            }
            $companyModel = new Store_Model_Companies();
            $companyModel->setCompanyName($companyName);
            $companyModel = $companyMapper->save($companyModel);

        } else {
            $companyModel = $companyMapper->find($companyId);
            if (!$companyModel instanceof Store_Model_Companies) {
                $responseHelper->fail($translator->translate('Company doesn\'t exists'));
            }
        }

        $companyMapper->deleteBySupplierIds($supplierIds);

        $companyId = $companyModel->getId();
        $companyMapper->assignSuppliers($companyId, $supplierIds);
        $responseHelper->success($translator->translate('Saved'));

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
        $supplierId = filter_var($this->_request->getParam('supplierId'), FILTER_SANITIZE_NUMBER_INT);

        if (!$supplierId) {
            $this->_error();
        }

        $companiesMapper = Store_Mapper_CompaniesMapper::getInstance();

        $supplierCompanyExists = $companiesMapper->getBySupplierId($supplierId);
        if (!empty($supplierCompanyExists)) {
            $companiesMapper->deleteBySupplierId($supplierId);
        }


    }

}
