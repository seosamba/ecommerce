<?php

class Store_Model_GatewayLabelModel extends Application_Model_Models_Abstract
{
    protected $_gateway = '';

    protected $_gatewayLabel = '';

    /**
     * @return string
     */
    public function getGateway()
    {
        return $this->_gateway;
    }

    /**
     * @param string $gateway
     */
    public function setGateway($gateway)
    {
        $this->_gateway = $gateway;
    }

    /**
     * @return string
     */
    public function getGatewayLabel()
    {
        return $this->_gatewayLabel;
    }

    /**
     * @param string $gatewayLabel
     */
    public function setGatewayLabel($gatewayLabel)
    {
        $this->_gatewayLabel = $gatewayLabel;
    }



}