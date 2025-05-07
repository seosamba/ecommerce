<?php

class Api_Store_Productdescriptionai extends Api_Service_Abstract
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
     * : /api/store/productdescriptionai/
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
        $imageUrl = $this->getRequest()->getParam('imageUrl');
        $wordCount = $this->getRequest()->getParam('wordCount');
        $imageUrlData = parse_url($imageUrl);
        $websiteUrlData = parse_url($websiteUrl);
        if ($websiteUrlData['host'] === $imageUrlData['host']) {
            $imageUrl = str_replace('/small/', '/original/', $imageUrl);
        }

        $productName = $this->getRequest()->getParam('productName');

        if (empty($imageUrl)) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Please upload product image')
            );
        }

        if (empty($productName)) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Please specify product name')
            );
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch) ;

        $encodedImage = base64_encode($res);

        $info = array(
            'image_url' => $encodedImage,
            'product_title' => $productName,
            'wordCount' => $wordCount
        );

        $result = Apps::apiCall('POST', 'openaiProductDescription', array(), $info);
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

        $data = json_decode($result['data'], true);

        $finalMessage = '';
        if ($responseType === 'no_formatting') {
            $finalMessage .= $data['overview'];
            $finalMessage .= $data['unique_characteristics'].' ';
            $finalMessage .= $data['aesthetic_benefits'].' ';
            $finalMessage .= 'Features:'.' ';
            $finalMessage .= 'Size - '.$data['features']['size'].' ';
            $finalMessage .= 'Material - '.$data['features']['material'].' ';
            $finalMessage .= 'Color - '.$data['features']['color'].' ';
            $finalMessage .= 'Design - '.$data['features']['design'].' ';
            $finalMessage .= 'Functionality - '.$data['features']['functionality'].' ';
            $finalMessage .= $data['potential_uses'];
        } else {
            $finalMessage .= $data['overview'];
            $finalMessage .= $data['unique_characteristics'].' <br/>';
            $finalMessage .= $data['aesthetic_benefits'].' <br/>';
            $finalMessage .= '<b>Features</b>:'.' <br/>';
            $finalMessage .= '<b>Size</b> - '.$data['features']['size'].' <br/>';
            $finalMessage .= '<b>Material</b> - '.$data['features']['material'].' <br/>';
            $finalMessage .= '<b>Color</b> - '.$data['features']['color'].' <br/>';
            $finalMessage .= '<b>Design</b> - '.$data['features']['design'].' <br/>';
            $finalMessage .= '<b>Functionality</b> - '.$data['features']['functionality'].' <br/>';
            $finalMessage .= $data['potential_uses'];
        }

        return array(
            'error' => '0',
            'message' => $finalMessage
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
