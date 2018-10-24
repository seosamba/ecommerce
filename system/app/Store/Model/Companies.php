<?php

/**
 * Companies
 *
 */
class Store_Model_Companies extends Application_Model_Models_Abstract
{

    protected $companyName;

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param mixed $companyName
     * @return mixed
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }


}