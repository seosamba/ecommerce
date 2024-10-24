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
        if (!$tokenValid) {
            $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
            $websiteUrl = $websiteHelper->getUrl();
            //$this->_error($translator->translate('Your session has timed-out. Please Log back in ' . '<a href="' . $websiteUrl . 'go">here</a>'));
        }

        $sambaToken = $this->_configHelper->getConfig('sambaToken');
        $isRegistered = $this->_configHelper->getConfig('registered');
        if (empty($isRegistered) || empty($sambaToken)) {
            return array(
                'error' => '1',
                'message' => ''
            );
        }

        $imageUrl = $this->getRequest()->getParam('imageUrl');

        $imageUrl = 'https://cdn.webshopapp.com/shops/212063/files/429043524/650x650x2/santa-cruz-v-10-cc-s.jpg';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);

        if (empty($data)) {
            return array(
                'error' => '1',
                'message' => ''
            );
        }

        $encodedImage = base64_encode($data);

        $promt = 'Analyze the image and generate a detailed product description for [the product titled [ZINUS Josh Loveseat Sofa]]. Highlight key features such as size, material, color, design, and functionality. Emphasize the product\'s unique characteristics and potential uses. If applicable, mention any aesthetic or practical benefits. Write in a tone suitable for an online product listing, ensuring the description appeals to potential buyers.';

        $info = array(
            'promt' => $promt,
            'image_url' => $encodedImage,
            'temperature' => 1,
            'presence_penalty' => 0.6,
            'response_format' => 'json_object',
        );

        $result = Apps::apiCall('POST', 'aipPoductDescription', array(), $info);



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer $apiKey",
        ]);
        $data = '{
    "model": "gpt-4o",
    "messages": [
      {
        "role": "user",
        "content": [
          {
            "type": "text",
            "text": "' . $promt . '"
          },
          {
            "type": "image_url",
            "image_url": {
              "url": "data:image/jpg;base64,' . $encodedImage . '"
            }
          }
          
        ]
      },
      {
        "role": "system",
        "content": "You are a helpful assistant. Response should be in json format"
      }
    ],
    "temperature":1,    
    "presence_penalty":0.6, 
    "response_format": {"type" : "json_object"}, 
    "max_tokens": 3000
  }';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        $res = json_decode($response, true);

        return array(
            'error' => '0',
            'message' => ''
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
