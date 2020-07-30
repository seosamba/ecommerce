<?php

class Api_Store_Throttletransactions extends Api_Service_Abstract
{
    const THROTTLE_TRANSACTIONS_SECURE_TOKEN = 'ThrottletransactionsToken';

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

    public function getAction()
    {
    }

    public function postAction()
    {
        $data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);

        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::THROTTLE_TRANSACTIONS_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        unset($data[Tools_System_Tools::CSRF_SECURE_TOKEN]);

        $throttleConfigParams = array();

        if (isset($data['throttleTransactions'])) {
            $throttleConfigParams['throttleTransactions'] = $data['throttleTransactions'];
        }
        if (isset($data['throttleTransactionsLimit'])) {
            $throttleConfigParams['throttleTransactionsLimit'] = $data['throttleTransactionsLimit'];
        }
        if (isset($data['throttleTransactionsLimitMessage'])) {
            $throttleConfigParams['throttleTransactionsLimitMessage'] = $data['throttleTransactionsLimitMessage'];
        }
        if (!empty($throttleConfigParams)) {
            Models_Mapper_ShoppingConfig::getInstance()->save($throttleConfigParams);

            return $throttleConfigParams;
        }

        $this->_error();
    }

    public function putAction()
    {
    }

    public function deleteAction()
    {
    }

}
