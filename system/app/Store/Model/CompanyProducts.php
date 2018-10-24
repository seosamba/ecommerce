<?php

/**
 * Company products
 *
 */
class Store_Model_CompanyProducts extends Application_Model_Models_Abstract
{

    protected $productId;

    protected $companyId;

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     * @return mixed
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param mixed $companyId
     * @return mixed
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }


}