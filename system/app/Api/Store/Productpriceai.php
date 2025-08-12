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

        $responseType = $this->getRequest()->getParam('responseType');
        $productName = $this->getRequest()->getParam('productName');
        $productCondition = $this->getRequest()->getParam('productCondition');

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
        );

        //$result = Apps::apiCall('POST', 'openaiProductRecommendedPrice', array(), $info);

        $result['error'] = 0;
        $result['message'] = '';
        $result['done'] = true;

        //$data = json_decode($result['data'], true);
        $result['data']['pricingAnalysis'] = "<em>Monaco Heart Casino Magnet</em> (New Condition)</h4>";
        $result['data']['priceRange'] = "$7.47 – $21.88 USD";
        $result['data']['sources']['description'] = "The Monaco Heart Casino Magnet is available in various designs and materials, leading to a broad price range:";
        $result['data']['sources']['links'] = array(
            array(
                'name' => 'Monaco-Addict.com – Monaco Heart Casino Magnet',
                'link' => 'https://monaco-addict.com/monaco-heart-casino-magnet/',
                'price' => '7.20',
            ),
            array(
                'name' => 'eBay – MONACOMETAL Fridge Magnet',
                'link' => 'https://www.ebay.com/itm/126737461735',
                'price' => '21.88',
            ),
            array(
                'name' => 'eBay – Casino de Monte Carlo 3D Resin Magnet',
                'link' => 'https://www.ebay.com/itm/177237778400',
                'price' => '8.98',
            ),
            array(
                'name' => 'Amazon.de – Monaco Heart Shaped 3D Magnet',
                'link' => 'https://www.amazon.de/-/en/Monaco-Shaped-Stickers-Souvenirs-Kitchen/dp/B07JHBC6L7',
                'price' => '7.99',
            ),
            array(
                'name' => 'Bonanza – Vintage Casino de Monte-Carlo Hand-Painted Magnet',
                'link' => 'https://www.bonanza.com/listings/Vintage-Monaco-Monte-Carlo-Metal-Magnet-Casino-de-Monte-Carlo-2-Hand-Painted/1753011406',
                'price' => '12.99',
            ),
        );

        $result['data']['justification'] = "The recommended price reflects similar market offerings while providing a slight premium for uniqueness. It targets souvenir shoppers and collectors interested in Monaco-themed items without being overpriced.";
        $result['data']['recommendedPrice'] = '9.99';

        if (empty($result)) {
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

        return array(
            'error' => '0',
            //'content' => $result['data']['overview'],
            //'price' => $result['data']['price'],
            'pricingAnalysis' => $result['data']['pricingAnalysis'],
            'priceRange' => $result['data']['priceRange'],
            'description' => $result['data']['sources']['description'],
            'links' => $result['data']['sources']['links'],
            'justification' => $result['data']['justification'],
            'recommendedPrice' => $result['data']['recommendedPrice'],
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
