<?php

/**
 * Class Api_Store_Digitalproductsdownload
 */
class Api_Store_Digitalproductsdownload extends Api_Service_Abstract
{

    /**
     * @var array Access Control List
     */
    protected $_accessList = array(
        Tools_Security_Acl::ROLE_SUPERADMIN => array(
            'allow' => array('get')
        ),
        Tools_Security_Acl::ROLE_ADMIN => array(
            'allow' => array('get')
        ),
        Shopping::ROLE_SALESPERSON => array(
            'allow' => array('get')
        ),
        Tools_Security_Acl::ROLE_MEMBER => array(
            'allow' => array('get')
        ),
        Shopping::ROLE_CUSTOMER => array(
            'allow' => array('get')
        )
    );

    /**
     * @return mixed
     */
    public function getAction()
    {
        $fileHash = filter_var($this->_request->getParam('id', false), FILTER_SANITIZE_STRING);
        $cartId = filter_var($this->_request->getParam('cartId', false), FILTER_SANITIZE_NUMBER_INT);
        $productId = filter_var($this->_request->getParam('productId', false), FILTER_SANITIZE_NUMBER_INT);
        $sessionHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('session');
        $userId = $sessionHelper->getCurrentUser()->getId();
        $digitalProductsMapper = Store_Mapper_DigitalProductMapper::getInstance();
        $digitalProduct = array();
        if ($cartId && $fileHash && $productId && $userId) {
            $digitalProduct = $digitalProductsMapper->findDigitalProduct($cartId, $productId, $fileHash, $userId);
        }

        if ($fileHash && Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
            $digitalProduct = $digitalProductsMapper->getByHash($fileHash);
        }

        if (!empty($digitalProduct)) {
            $fileStoredName = $digitalProduct['file_stored_name'];
            $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
            $filePath = $websiteHelper->getPath() . 'plugins' . DIRECTORY_SEPARATOR . 'shopping' .
                DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . Api_Store_Digitalproducts::FILES_FOLDER
                . DIRECTORY_SEPARATOR . $fileStoredName;

            if (!Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
                $digitalProductsMapper->decreaseDownloadLimit($digitalProduct['id']);
            }

            if (file_exists($filePath)) {
                $this->getResponse()->setHeader('Content-Disposition',
                    'attachment; filename=' . $digitalProduct['display_file_name'] . '.' . pathinfo($fileStoredName,
                        PATHINFO_EXTENSION))
                    ->setHeader('Content-type', 'application/force-download');
                readfile($filePath);
                $this->getResponse()->sendResponse();
                exit;
            }
        }
        $this->_error('Wrong params');
    }


    public function postAction()
    {

    }

    public function putAction()
    {

    }

    public function deleteAction()
    {

    }
}