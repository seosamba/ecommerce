<?php

class Store_Model_GatewayLabelModel extends Application_Model_Models_Abstract
{
    protected $_gateway = '';

    protected $_gatewayLabel = '';

    /**
     * @return string
     */
    public function getGateway(): string
    {
        return $this->_gateway;
    }

    /**
     * @param string $gateway
     */
    public function setGateway(string $gateway): void
    {
        $this->_gateway = $gateway;
    }

    /**
     * @return string
     */
    public function getGatewayLabel(): string
    {
        return $this->_gatewayLabel;
    }

    /**
     * @param string $gatewayLabel
     */
    public function setGatewayLabel(string $gatewayLabel): void
    {
        $this->_gatewayLabel = $gatewayLabel;
    }



}