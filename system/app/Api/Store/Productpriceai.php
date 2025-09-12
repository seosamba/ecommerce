<?php

class Api_Store_Productpriceai extends Api_Service_Abstract
{

    /**
     * System response helper
     *
     * @var null
     */
    protected $_responseHelper = null;

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


    public function init()
    {
        parent::init();
        $this->_responseHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('response');
        $this->_configHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('config');

    }


    /**
     *
     * Resource:
     * : /api/store/productpriceai/
     *
     * HttpMethod:
     * : GET
     *
     * @return JSON
     */
    public function getAction()
    {
        $this->postAction();
    }

    /**
     *
     * Resource:
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON
     */
    public function postAction()
    {
        $translator = Zend_Registry::get('Zend_Translate');

        $secureToken = $this->getRequest()->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $tokenValid = Tools_System_Tools::validateToken($secureToken, Shopping::SHOPPING_SECURE_TOKEN);
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $websiteUrl = $websiteHelper->getUrl();
        if (!$tokenValid) {
            $this->_error($translator->translate('Your session has timed-out. Please Log back in ' . '<a href="' . $websiteUrl . 'go">here</a>'));
        }

        $sambaToken = $this->_configHelper->getConfig('sambaToken');
        $isRegistered = $this->_configHelper->getConfig('registered');
        if (empty($isRegistered) || empty($sambaToken)) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Create your').' '.'<a href="https://mojo.seosamba.com/register.html" target="_blank">'.$translator->translate('SeoSamba Free account').'</a>'
            );
        }

        $productName = $this->getRequest()->getParam('productName');
        $productCondition = $this->getRequest()->getParam('productCondition');
        $productMpn = $this->getRequest()->getParam('productMpn');
        $productBrand = $this->getRequest()->getParam('productBrand');
        $productNewBrand = $this->getRequest()->getParam('productNewBrand');
        $productGtin = $this->getRequest()->getParam('productGtin');
        $productShortDescription = $this->getRequest()->getParam('productShortDescription');

        if (empty($productName)) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Please specify product name')
            );
        }

        if (empty($productCondition)) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Please specify product condition')
            );
        }

        $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
        $currency = $shoppingConfig['currency'];
        if (empty($currency)) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Please specify store currency')
            );
        }

        $info = array(
            'product_title' => $productName,
            'product_condition' => $productCondition,
            'product_currency' => $currency,
            'product_mpn' => '',
            'product_brand' => '',
            'product_gtin' => '',
            'product_short_description' => '',
        );

        if (!empty($productMpn)) {
            $info['product_mpn'] = $productMpn;
        }

        if (!empty($productBrand) && $productBrand != '-1') {
            $info['product_brand'] = $productBrand;
        }

        if (!empty($productNewBrand)) {
            $info['product_brand'] = $productNewBrand;
        }

        if (!empty($productGtin)) {
            $info['product_gtin'] = $productGtin;
        }

        if (!empty($productShortDescription)) {
            $info['product_short_description'] = $productShortDescription;
        }

        $result = Apps::apiCall('POST', 'openaiProductRecommendedPrice', array(), $info);

        if (empty($result['data'])) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Service not available')
            );
        }

        if (!empty($result['error'])) {
            return array(
                'error' => '1',
                'message' => $result['message']
            );
        }

        if ($result['done'] === false) {
            return array(
                'error' => '1',
                'message' => $result['message']
            );
        }

        $data = json_decode($result['data'], true);

        if (empty($data['sources']['links'])) {
            return array(
                'error' => '1',
                'message' => $data['sources']['description']
            );
        }

        $response = array();

        $response['priceRange'] = preg_replace('/[\x00-\x1F\x7F]/u', '', $data['priceRange']);
        $response['justification'] = preg_replace('/[\x00-\x1F\x7F]/u', '', $data['justification']);

        $currency = Zend_Registry::get('Zend_Currency');

        foreach ($data['sources']['links'] as $key => $source) {
            $response['links'][$key]['link'] = $source['link'];
            $response['links'][$key]['name'] = $source['name'];
            $response['links'][$key]['price'] = $source['price'];
            $response['links'][$key]['priceWithCurrency'] = $currency->toCurrency($source['price']);
        }

        $response['priceRange'] = $data['priceRange'];
        $response['justification'] = $data['justification'];
        $response['pricingAnalysis'] = $data['pricingAnalysis'];
        $response['recommendedPrice'] = $data['recommendedPrice'];
        $response['recommendedPriceWithCurrency'] = $currency->toCurrency($data['recommendedPrice']);


        return array(
            'error' => '0',
            'pricingAnalysis' => $response['pricingAnalysis'],
            'priceRange' => $response['priceRange'],
            'description' => $response['description'],
            'links' => $response['links'],
            'justification' => $response['justification'],
            'recommendedPrice' => $response['recommendedPrice'],
            'recommendedPriceWithCurrency' => $response['recommendedPriceWithCurrency'],
        );

    }

    /**
     *
     * Resource:
     *
     * HttpMethod:
     * : PUT
     *
     * ## Parameters:
     * id (source integer)
     *
     * @return JSON
     */
    public function putAction()
    {
    }

    /**
     *
     * Resource:
     *
     * HttpMethod:
     * : DELETE
     *
     * ## Parameters:
     * id (source integer)
     *
     * @return JSON
     */
    public function deleteAction()
    {

    }

}
