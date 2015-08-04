<?php
/**
 * Class Store_Model_DigitalProducts
 */
class Store_Model_DigitalProduct extends Application_Model_Models_Abstract
{

    /**
     * digital product type for download
     */
    const PRODUCT_TYPE_DOWNLOADABLE = 'downloadable';

    /**
     * digital product type for display
     */
    const PRODUCT_TYPE_VIEWABLE = 'viewable';

    protected $_fileStoredName;

    protected $_fileHash;

    protected $_originalFileName;

    protected $_productId;

    protected $_uploadedAt;

    protected $_startDate;

    protected $_endDate = '2038-01-19';

    protected $_downloadLimit = 0;

    protected $_ipAddress;

    protected $_productType;

    protected $_displayFileName;

    /**
     * @return mixed
     */
    public function getFileStoredName()
    {
        return $this->_fileStoredName;
    }

    /**
     * @param mixed $fileStoredName
     * @return mixed
     */
    public function setFileStoredName($fileStoredName)
    {
        $this->_fileStoredName = $fileStoredName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileHash()
    {
        return $this->_fileHash;
    }

    /**
     * @param mixed $fileHash
     * @return mixed
     */
    public function setFileHash($fileHash)
    {
        $this->_fileHash = $fileHash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalFileName()
    {
        return $this->_originalFileName;
    }

    /**
     * @param mixed $originalFileName
     * @return mixed
     */
    public function setOriginalFileName($originalFileName)
    {
        $this->_originalFileName = $originalFileName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * @param mixed $productId
     * @return mixed
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUploadedAt()
    {
        return $this->_uploadedAt;
    }

    /**
     * @param mixed $uploadedAt
     * @return mixed
     */
    public function setUploadedAt($uploadedAt)
    {
        $this->_uploadedAt = $uploadedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->_startDate;
    }

    /**
     * @param mixed $startDate
     * @return mixed
     */
    public function setStartDate($startDate)
    {
        $this->_startDate = $startDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->_endDate;
    }

    /**
     * @param mixed $endDate
     * @return mixed
     */
    public function setEndDate($endDate)
    {
        $this->_endDate = $endDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDownloadLimit()
    {
        return $this->_downloadLimit;
    }

    /**
     * @param mixed $downloadLimit
     * @return mixed
     */
    public function setDownloadLimit($downloadLimit)
    {
        $this->_downloadLimit = $downloadLimit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->_ipAddress;
    }

    /**
     * @param mixed $ipAddress
     * @return mixed
     */
    public function setIpAddress($ipAddress)
    {
        $this->_ipAddress = $ipAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductType()
    {
        return $this->_productType;
    }

    /**
     * @param mixed $productType
     * @return mixed
     */
    public function setProductType($productType)
    {
        $this->_productType = $productType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDisplayFileName()
    {
        return $this->_displayFileName;
    }

    /**
     * @param mixed $displayFileName
     * @return mixed
     */
    public function setDisplayFileName($displayFileName)
    {
        $this->_displayFileName = $displayFileName;

        return $this;
    }

}