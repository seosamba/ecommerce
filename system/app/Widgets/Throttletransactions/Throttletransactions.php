<?php

class Widgets_Throttletransactions_Throttletransactions extends Widgets_Abstract {

    protected function _load()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }
        $method = strtolower(array_shift($this->_options));
        try {
            return $this->{'_render' . ucfirst($method)}();
        } catch (Exception $e) {
            return '<b>Method ' . $method . ' doesn\'t exist</b>';
        }
    }

    protected function _init() {
        parent::_init();
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }
        $this->_view = new Zend_View();
        $this->_view->websiteUrl = Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl();
        $this->_view->setScriptPath(realpath(__DIR__.DIRECTORY_SEPARATOR.'views'));
        $this->_websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
    }


    private function _renderBanner()
    {
        return $this->_view->render('throttle-limit-banner.phtml');
    }
}
