<?php
/**
 * StoreMailWatchdog
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_StoreMailWatchdog implements Interfaces_Observer  {

	const TRIGGER_NEW_CUSTOMER  = 'store_newcustomer';

	const TRIGGER_NEW_ORDER     = 'store_neworder';

	const TRIGGER_SHIPPING_TRACKING_NUMBER = 'store_trackingnumber';

    const TRIGGER_REFUND = 'store_refund';

	const RECIPIENT_SALESPERSON = 'sales person';

	const RECIPIENT_CUSTOMER    = 'customer';

    const RECIPIENT_ADMIN    = 'admin';

    const TRIGGER_CUSTOMERCHANGEATTR = 't_userchangeattr';

    const TRIGGER_NEW_USER_ACCOUNT = 'store_newuseraccount';

    const SHIPPING_TYPE = 'shipping';

    const BILLING_TYPE = 'billing';

	private $_options;

	/**
	 * @var Tools_Mail_Mailer instance of mailer
	 */
	private $_mailer;

	/**
	 * @var Helpers_Action_Config
	 */
	private $_configHelper;

	/**
	 * @var Helpers_Action_Website
	 */
	private $_websiteHelper;

	private $_storeConfig;

    /**
     * Customer model
     *
     * @var null
     */
    private $_customer = null;

	/**
	 * @var Instance of watched object
	 */
	private $_object;

	public function __construct($options = array()) {
		$this->_storeConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
		$this->_configHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('config');
		$this->_websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
		$this->_options = $options;
		$this->_initMailer();
		$this->_entityParser = new Tools_Content_EntityParser();
	}

	private function _initMailer(){
		$config = $this->_configHelper->getConfig();
		$this->_mailer = new Tools_Mail_Mailer();

		if ((bool)$config['useSmtp']){
			$smtpConfig = array(
				'host'      => $config['smtpHost'],
				'username'  => $config['smtpLogin'],
				'password'  => $config['smtpPassword']
			);
			if ((bool)$config['smtpSsl']){
				$smtpConfig['ssl'] = $config['smtpSsl'];
			}
			if (!empty($config['smtpPort'])){
				$smtpConfig['port'] = $config['smtpPort'];
			}
			$this->_mailer->setSmtpConfig($smtpConfig);
			$this->_mailer->setTransport(Tools_Mail_Mailer::MAIL_TYPE_SMTP);
		} else {
			$this->_mailer->setTransport(Tools_Mail_Mailer::MAIL_TYPE_MAIL);
		}
	}

	public function notify($object) {
        if (!$object || $this->_options['service'] !== Application_Model_Models_TriggerAction::SERVICE_TYPE_EMAIL){
			return false;
		}

		$this->_object = $object;

		if (isset($this->_options['template']) && !empty($this->_options['template']) ){
			$this->_template = $this->_preparseEmailTemplate();
		} else {
			throw new Exceptions_SeotoasterException('Missing template for action email trigger');
		}

        $this->_subject = $this->_options['subject'];
		$this->_mailer->setMailFromLabel($this->_storeConfig['company']);

		if (!empty($this->_options['from'])){
			$this->_mailer->setMailFrom($this->_options['from']);
		} elseif (!empty($this->_storeConfig['email'])) {
			$this->_mailer->setMailFrom($this->_storeConfig['email']);
		} else {
			$this->_mailer->setMailFrom($this->_configHelper->getAdminEmail());
		}

		if (isset($this->_options['trigger'])){
			$methodName = str_replace('store_', '', $this->_options['trigger']);
			$methodName = '_send'.ucfirst(strtolower(preg_replace('/\s*/', '', $methodName))).'Mail';
			if (method_exists($this, $methodName)){
				$this->$methodName();
			}
		}
	}

	protected function _send(){
		if (!$this->_mailer->getMailFrom() || !$this->_mailer->getMailTo()) {
			throw new Exceptions_SeotoasterException('Missing required "from" and "to" fields');
		}

        $this->_mailer->setSubject($this->_entityParser->parse($this->_subject));
        $this->_mailer->setBody($this->_entityParser->parse($this->_template));

		return ($this->_mailer->send() !== false);
	}


	private function _sendNewcustomerMail(){
		$systemConfig = $this->_configHelper->getConfig();
        $userMapper = Application_Model_Mappers_UserMapper::getInstance();
        $adminBccArray = array();
        $customerBccArray = array();
        $adminEmail = isset($systemConfig['adminEmail'])?$systemConfig['adminEmail']:'admin@localhost';
        switch ($this->_options['recipient']) {
           case Tools_Security_Acl::ROLE_ADMIN:
                $this->_mailer->setMailToLabel('Admin')
						->setMailTo($adminEmail);
                $where = $userMapper->getDbTable()->getAdapter()->quoteInto("role_id = ?", Tools_Security_Acl::ROLE_ADMIN);
                $adminUsers = $userMapper->fetchAll($where);
                if(!empty($adminUsers)){
                    foreach($adminUsers as $admin){
                        array_push($adminBccArray, $admin->getEmail());
                    }
                    if(!empty($adminBccArray)){
                        $this->_mailer->setMailBcc($adminBccArray);
                    }
                }
				break;
            case self::RECIPIENT_SALESPERSON:
				$this->_mailer->setMailToLabel($this->_object->getFullName())
						->setMailTo(!empty($this->_storeConfig['email'])?$this->_storeConfig['email']:$adminEmail);
                $where = $userMapper->getDbTable()->getAdapter()->quoteInto("role_id = ?", Shopping::ROLE_SALESPERSON);
                $salesPersons = $userMapper->fetchAll($where);
                if(!empty($salesPersons)){
                    foreach($salesPersons as $salesPerson){
                        array_push($customerBccArray, $salesPerson->getEmail());
                    }
                    if(!empty($customerBccArray)){
                        $this->_mailer->setMailBcc($customerBccArray);
                    }
                }
				break;
			case self::RECIPIENT_CUSTOMER:
				$this->_mailer->setMailToLabel($this->_object->getFullName())
						->setMailTo($this->_object->getEmail());
                //create link for password generation and send e-mail to the user
                $resetToken = Tools_System_Tools::saveResetToken( $this->_object->getEmail(), $this->_object->getId(), '+1 week');
				break;
			default:
				error_log('Unsupported recipient '.$this->_options['recipient'].' given');
				return false;
				break;
		}
        
        $this->_entityParser->objectToDictionary($this->_object);
        if ($resetToken instanceof Application_Model_Models_PasswordRecoveryToken) {
            $this->_entityParser->addToDictionary( array(
                'customer:passwordLink' => '<a href="' . $resetToken->getResetUrl() . '/new/customer">link</a>',
                'customer:passwordLinkRaw'  => $resetToken->getResetUrl()
            ));
        }
        $this->_entityParser->addToDictionary(array('store:name'=>!empty($this->_storeConfig['company'])?$this->_storeConfig['company']:''));
        $this->_entityParser->addToDictionary(array('website:url'=>$this->_websiteHelper->getUrl()));
        $this->_addAddressToDictionary($this->_object);
        return $this->_send();
	}

	private function _preparseEmailTemplate(){
		$tmplName = $this->_options['template'];
		$tmplMessage = $this->_options['message'];
		$mailTemplate = Application_Model_Mappers_TemplateMapper::getInstance()->find($tmplName);

		if (!empty($mailTemplate)){
			$this->_entityParser->setDictionary(array(
				'emailmessage' => !empty($tmplMessage) ? $tmplMessage : ''
			));
			//pushing message template to email template and cleaning dictionary
			$mailTemplate = $this->_entityParser->parse($mailTemplate->getContent());
			$this->_entityParser->setDictionary(array());

			$mailTemplate = $this->_entityParser->parse($mailTemplate);

			$themeData = Zend_Registry::get('theme');
			$extConfig = Zend_Registry::get('extConfig');
			$parserOptions = array(
				'websiteUrl'   => $this->_websiteHelper->getUrl(),
				'websitePath'  => $this->_websiteHelper->getPath(),
				'currentTheme' => $extConfig['currentTheme'],
				'themePath'    => $themeData['path'],
			);
			$parser = new Tools_Content_Parser($mailTemplate, Tools_Misc::getCheckoutPage()->toArray(), $parserOptions);

			return Tools_Content_Tools::stripEditLinks($parser->parseSimple());
		}

		return false;
	}

	private function _sendNeworderMail() {
		$customer = Models_Mapper_CustomerMapper::getInstance()->find($this->_object->getUserId());
        $userMapper = Application_Model_Mappers_UserMapper::getInstance();
        $adminBccArray = array();
        $customerBccArray = array();
        $systemConfig = $this->_configHelper->getConfig();
        $adminEmail = isset($systemConfig['adminEmail'])?$systemConfig['adminEmail']:'admin@localhost';
        switch ($this->_options['recipient']) {
			case Tools_Security_Acl::ROLE_ADMIN:
                $this->_mailer->setMailToLabel('Admin')
						->setMailTo($adminEmail);
                $where = $userMapper->getDbTable()->getAdapter()->quoteInto("role_id = ?", Tools_Security_Acl::ROLE_ADMIN);
                $adminUsers = $userMapper->fetchAll($where);
                if(!empty($adminUsers)){
                    foreach($adminUsers as $admin){
                        array_push($adminBccArray, $admin->getEmail());
                    }
                    if(!empty($adminBccArray)){
                        $this->_mailer->setMailBcc($adminBccArray);
                    }
                }
                break;
			case self::RECIPIENT_SALESPERSON:
				$this->_mailer->setMailToLabel('Sales person')
						->setMailTo(!empty($this->_storeConfig['email'])?$this->_storeConfig['email']:$adminEmail);
                $where = $userMapper->getDbTable()->getAdapter()->quoteInto("role_id = ?", Shopping::ROLE_SALESPERSON);
                $salesPersons = $userMapper->fetchAll($where);
                if(!empty($salesPersons)){
                    foreach($salesPersons as $salesPerson){
                        array_push($customerBccArray, $salesPerson->getEmail());
                    }
                    if(!empty($customerBccArray)){
                        $this->_mailer->setMailBcc($customerBccArray);
                    }
                }
				break;
			case self::RECIPIENT_CUSTOMER:
				if ($customer && $customer->getEmail()){
					$this->_mailer->setMailToLabel($customer->getFullName())
							->setMailTo($customer->getEmail());
				} else {
					return false;
				}
				break;
			default:
				error_log('Unsupported recipient '.$this->_options['recipient'].' given');
				return false;
				break;
		}

		$this->_entityParser
				->objectToDictionary($customer)
				->objectToDictionary($this->_object, 'order');
        $withBillingAddress = $this->_prepareAdddress($customer, $this->_object->getBillingAddressId(), self::BILLING_TYPE);
        $withShippingAddress = $this->_prepareAdddress($customer, $this->_object->getShippingAddressId(), self::SHIPPING_TYPE);
        if(isset($withBillingAddress)){
            $this->_entityParser->addToDictionary(array('order:billingaddress'=> $withBillingAddress));
        }
        if(isset($withShippingAddress)){
            $this->_entityParser->addToDictionary(array('order:shippingaddress'=> $withShippingAddress));
        }
        $currency = '';
        if(Zend_Registry::isRegistered('Zend_Currency')){
            $currencyHelper = Zend_Registry::get('Zend_Currency');
            $currency = $currencyHelper->getSymbol();
        }
        $this->_entityParser->addToDictionary(array('order:currency'=>$currency));
        $this->_entityParser->addToDictionary(array('store:name'=>!empty($this->_storeConfig['company'])?$this->_storeConfig['company']:''));
		return $this->_send();
	}

    /**
     * Send email for when tracking number assigned or changed
     *
     * @return bool
     * @throws Exceptions_SeotoasterException
     */
    private function _sendTrackingnumberMail()
    {
        $this->_prepareEmailToSend();
        $this->_entityParser
            ->objectToDictionary($this->_object, 'order')
            ->objectToDictionary($this->_customer);
        $withBillingAddress = $this->_prepareAdddress($this->_customer, $this->_object->getBillingAddressId(),
            self::BILLING_TYPE);
        $withShippingAddress = $this->_prepareAdddress($this->_customer, $this->_object->getShippingAddressId(),
            self::SHIPPING_TYPE);
        if (isset($withBillingAddress)) {
            $this->_entityParser->addToDictionary(array('order:billingaddress' => $withBillingAddress));
        }
        if (isset($withShippingAddress)) {
            $this->_entityParser->addToDictionary(array('order:shippingaddress' => $withShippingAddress));
        }
        $this->_entityParser->addToDictionary(array('store:name' => !empty($this->_storeConfig['company']) ? $this->_storeConfig['company'] : ''));

        return $this->_send();
    }

    /**
     * Send email when user change account info
     *
     * @return bool
     * @throws Exceptions_SeotoasterException
     */
    private function _sendNewuseraccountMail()
    {
        $this->_mailer->setMailToLabel($this->_object->getFullName())
            ->setMailTo($this->_object->getEmail());
        $this->_entityParser
            ->objectToDictionary($this->_object);

        return $this->_send();
    }

    /**
     * Send email for refund order
     *
     * @return bool
     * @throws Exceptions_SeotoasterException
     */
    private function _sendRefundMail()
    {
        $this->_prepareEmailToSend();
        $this->_entityParser->addToDictionary(
            array(
                'refund:message' => $this->_options['refundAmount'],
                'refund:notes' => $this->_options['refundNotes']
            )
        );

        return $this->_send();
    }

    /**
     *
     * Prepare admin, salesperson, customers emails
     *
     * @return bool
     */
    private function _prepareEmailToSend()
    {
        $this->_customer = Models_Mapper_CustomerMapper::getInstance()->find($this->_object->getUserId());
        $userMapper = Application_Model_Mappers_UserMapper::getInstance();
        $adminBccArray = array();
        $customerBccArray = array();
        $systemConfig = $this->_configHelper->getConfig();
        $adminEmail = isset($systemConfig['adminEmail']) ? $systemConfig['adminEmail'] : 'admin@localhost';
        switch ($this->_options['recipient']) {
            case Tools_Security_Acl::ROLE_ADMIN:
                $this->_mailer->setMailToLabel('Admin')
                    ->setMailTo($adminEmail);
                $where = $userMapper->getDbTable()->getAdapter()->quoteInto("role_id = ?",
                    Tools_Security_Acl::ROLE_ADMIN);
                $adminUsers = $userMapper->fetchAll($where);;
                if (!empty($adminUsers)) {
                    foreach ($adminUsers as $admin) {
                        array_push($adminBccArray, $admin->getEmail());
                    }
                    if (!empty($adminBccArray)) {
                        $this->_mailer->setMailBcc($adminBccArray);
                    }
                }
                break;
            case self::RECIPIENT_SALESPERSON:
                $this->_mailer->setMailToLabel('Sales person')
                    ->setMailTo(!empty($this->_storeConfig['email']) ? $this->_storeConfig['email'] : $adminEmail);
                $where = $userMapper->getDbTable()->getAdapter()->quoteInto("role_id = ?", Shopping::ROLE_SALESPERSON);
                $salesPersons = $userMapper->fetchAll($where);
                if (!empty($salesPersons)) {
                    foreach ($salesPersons as $salesPerson) {
                        array_push($customerBccArray, $salesPerson->getEmail());
                    }
                    if (!empty($customerBccArray)) {
                        $this->_mailer->setMailBcc($customerBccArray);
                    }
                }
                break;
            case self::RECIPIENT_CUSTOMER:
                if ($this->_customer && $this->_customer->getEmail()) {
                    $this->_mailer->setMailToLabel($this->_customer->getFullName())->setMailTo($this->_customer->getEmail());
                } else {
                    return false;
                }
                break;
            default:
                error_log('Unsupported recipient ' . $this->_options['recipient'] . ' given');

                return false;
                break;
        }

        if (false === ($body = $this->_preparseEmailTemplate())) {
            return false;
        }
    }

    private function _prepareAdddress($address, $addressId, $type){
       foreach($address->getAddresses() as $addressData){
           if($addressData['id'] == $addressId){
               foreach($addressData as $el => $value){
                   $this->_entityParser->addToDictionary(array('order:'.$type.$el => $value));
               }
               if(isset($addressData['state']) && $addressData['state'] != ''){
                    $state = Tools_Geo::getStateById($addressData['state']);
                    return $addressData['firstname'].' '.$addressData['lastname'].' '.$addressData['address1'].' '.$addressData['address2'].' '.$addressData['city'].' '.$state['state'].' '.$addressData['zip'].' '.$addressData['country'];
               }
               return $addressData['firstname'].' '.$addressData['lastname'].' '.$addressData['address1'].' '.$addressData['address2'].' '.$addressData['city'].' '.$addressData['zip'].' '.$addressData['country'];
           }
       }
        
    }
    
    private function _addAddressToDictionary($address){
        foreach($address->getAddresses() as $addressData){
           $this->_entityParser->addToDictionary(array('customer:phone'=>$addressData['phone']));
       }
    }

}