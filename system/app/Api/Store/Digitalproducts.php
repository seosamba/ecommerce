<?php

/**
 * Class Api_Store_Digitalproduct
 */
class Api_Store_Digitalproducts extends Api_Service_Abstract
{

    const DIGITAL_PRODUCTS_SECURE_TOKEN = 'DigitalProductToken';

    /**
     * General files folder
     */
    const FILES_FOLDER = 'files';

    /**
     * Accepted file Mime types for upload
     *
     * @var array
     */
    public static $_acceptedFilesMimeTypes = array(
        'application/pdf',
        'application/xml',
        'application/zip',
        'text/csv',
        'text/plain',
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/bmp',
        'application/msword',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'audio/mpeg3',
        'audio/mpeg',
        'video/avi',
        'video/x-msvideo',
        'video/mp4',
        'video/mpeg',
        'video/mp4'

    );

    /**
     * Accepted file types for images
     *
     * @var array
     */
    public static $_acceptedFileTypes = array(
        'xml,csv,doc,zip,jpg,png,bmp,gif,xls,pdf,docx,txt,xlsx,mp3,avi,mpeg,mp4,webm'
    );


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

    /**
     * @return mixed
     */
    public function getAction()
    {
        $productId = filter_var($this->_request->getParam('productId'), FILTER_SANITIZE_NUMBER_INT);
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        $digitalProductsMapper = Store_Mapper_DigitalProductMapper::getInstance();

        if ($id) {
            return $digitalProductsMapper->find($id);
        } elseif ($productId) {
            $limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
            $offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
            $sortOrder = filter_var($this->_request->getParam('order'), FILTER_SANITIZE_STRING);
            $count = (bool)$this->_request->has('count');
            if ($count) {
                $digitalProductsMapper->lastQueryResultCount($count);
            }
            $data = $digitalProductsMapper->fetchAll($productId, $sortOrder, $limit, $offset);

            return $data;

        } else {
            return $digitalProductsMapper->fetchAll();
        }

    }

    /**
     * Save new digital product file
     *
     * @return array|string
     * @throws Zend_Exception
     * @throws Zend_File_Transfer_Exception
     */
    public function postAction()
    {
        $data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);
        if (empty($data) || empty($data['productId'])) {
            $this->_error();
        }

        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate,
            self::DIGITAL_PRODUCTS_SECURE_TOKEN);
        if (!$valid) {
            $this->_error();
        }

        $productId = filter_var($data['productId'], FILTER_SANITIZE_NUMBER_INT);

        $translator = Zend_Registry::get('Zend_Translate');

        $productExists = Models_Mapper_ProductMapper::getInstance()->find($productId);
        if (!$productExists instanceof Models_Model_Product) {
            $this->_error($translator->translate('Product not exists'));
        }

        $uploader = new Zend_File_Transfer_Adapter_Http();
        $uploader->clearValidators();
        $uploader->clearFilters();
        $fileInfo = $uploader->getFileInfo();
        $file = reset($fileInfo);
        preg_match('~[^\x00-\x1F"<>\|:\*\?/]+\.[\w\d]{2,8}$~iU', $file['name'], $match);
        if (!$match) {
            return array('result' => 'Corrupted filename', 'error' => true);
        }
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = pathinfo($file['name'], PATHINFO_FILENAME);
        $fileStoredName = sha1($fileName . uniqid(microtime())) . '_' . date('Y-m-d',
                strtotime('now')) . '.' . $fileExtension;

        $fileHash = sha1($fileName . uniqid(microtime()));
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $savePath = $websiteHelper->getPath() . 'plugins' . DIRECTORY_SEPARATOR . 'shopping' . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . self::FILES_FOLDER;

        $uploader->addFilter('Rename', array(
            'target' => $savePath . DIRECTORY_SEPARATOR . $fileStoredName,
            'overwrite' => true
        ));
        $acceptedFileTypes = self::$_acceptedFileTypes;
        $acceptedMimeTypes = self::$_acceptedFilesMimeTypes;


        //Adding file extension validation
        $uploader->addValidator('Extension', false, $acceptedFileTypes);
        //Adding mime types validation
        $uploader->addValidator('MimeType', true, $acceptedMimeTypes);

        if ($uploader->isUploaded() && $uploader->isValid()) {
            try {
                $uploader->receive();
                $digitalProductMapper = Store_Mapper_DigitalProductMapper::getInstance();
                $digitalProductModel = new Store_Model_DigitalProduct();
                $digitalProductModel->setOriginalFileName($fileName . '.' . $fileExtension);
                $digitalProductModel->setProductId($productId);
                $digitalProductModel->setIpAddress($_SERVER['REMOTE_ADDR']);
                $digitalProductModel->setFileStoredName($fileStoredName);
                $digitalProductModel->setProductType(Store_Model_DigitalProduct::PRODUCT_TYPE_DOWNLOADABLE);
                $digitalProductModel->setFileHash($fileHash);
                $digitalProductModel->setUploadedAt(date(Tools_System_Tools::DATE_MYSQL));
                $digitalProductModel->setStartDate(date(Tools_System_Tools::DATE_MYSQL));
                $digitalProductModel->setDisplayFileName($fileName);
                $digitalProductMapper->save($digitalProductModel);

                return array('error' => 0, 'message' => $translator->translate('File uploaded'));

            } catch (Exceptions_SeotoasterException $e) {
                $this->_error($e->getMessage());
            }
        } else {
            return array('error' => 1, 'message' => $translator->translate('File type not supported'));
        }


    }

    /**
     * Update digital product
     *
     * @return array
     * @throws Zend_Exception
     */
    public function putAction()
    {
        $srcData = json_decode($this->_request->getRawBody(), true);
        $digitalProductMapper = Store_Mapper_DigitalProductMapper::getInstance();
        $digitalProductModel = $digitalProductMapper->find($srcData['id']);
        $translator = Zend_Registry::get('Zend_Translate');
        if ($digitalProductModel instanceof Store_Model_DigitalProduct) {
            $digitalProductModel->setStartDate(date(Tools_System_Tools::DATE_MYSQL, strtotime($srcData['start_date'])));
            $digitalProductModel->setEndDate(date(Tools_System_Tools::DATE_MYSQL, strtotime($srcData['end_date'])));
            $downloadLimit = filter_var($srcData['download_limit'], FILTER_SANITIZE_NUMBER_INT);
            $displayFileName = filter_var($srcData['display_file_name'], FILTER_SANITIZE_STRING);
            $digitalProductModel->setDownloadLimit($downloadLimit);
            $digitalProductModel->setDisplayFileName($displayFileName);
            $digitalProductMapper->save($digitalProductModel);

            return array('error' => 0, 'message' => $translator->translate('Saved'));
        }
    }

    /**
     * Delete file attached to product
     *
     * @return array
     * @throws Exceptions_SeotoasterException
     * @throws Zend_Exception
     */
    public function deleteAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            $this->_error();
        }
        $digitalProductsMapper = Store_Mapper_DigitalProductMapper::getInstance();
        $digitalProductFileExists = $digitalProductsMapper->find($id);
        $translator = Zend_Registry::get('Zend_Translate');

        if ($digitalProductFileExists instanceof Store_Model_DigitalProduct) {
            $fileStoredName = $digitalProductFileExists->getFileStoredName();
            $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
            $savePath = $websiteHelper->getPath() . 'plugins' . DIRECTORY_SEPARATOR . 'shopping' .
                DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . self::FILES_FOLDER . DIRECTORY_SEPARATOR . $fileStoredName;
            if (file_exists($savePath)) {
                if (!Tools_Filesystem_Tools::deleteFile($savePath)) {
                    $this->_error($translator->translate('File remove issue'));
                }
            }
            $digitalProductsMapper->delete($id);
        }


        return array('error' => 0, 'message' => $translator->translate('File removed'));
    }


}