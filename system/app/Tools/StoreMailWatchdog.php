<?php
/**
 * StoreMailWatchdog
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_StoreMailWatchdog implements Interfaces_Observer  {

	const TRIGGER_NEW_CUSTOMER  = 'store_newcustomer';

	const TRIGGER_NEW_ORDER     = 'store_neworder';

	const TRIGGER_SHIPPING_TRACKING_NUMBER = 'store_trackingnumber';

	const RECIPIENT_SALESPERSON = 'sales person';

	const RECIPIENT_CUSTOMER    = 'customer';

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
		if (!$object){
			return false;
		}

		$this->_object = $object;

		if (isset($this->_options['template']) && !empty($this->_options['template']) ){
			$this->_template = $this->_preparseEmailTemplate();
		} else {
			throw new Exceptions_SeotoasterException('Missing template for action email trigger');
		}

		$this->_mailer
				->setMailFromLabel($this->_storeConfig['company'])
				->setSubject($this->_options['subject']);

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
		$this->_mailer->setBody($this->_entityParser->parse($this->_template));

		return ($this->_mailer->send() !== false);
	}


	private function _sendNewcustomerMail(){
		switch ($this->_options['recipient']) {
			case self::RECIPIENT_SALESPERSON:
				$this->_mailer->setMailToLabel($this->_object->getFullName())
						->setMailTo(!empty($this->_storeConfig['email'])?$this->_storeConfig['email']:$this->_configHelper->getAdminEmail());
				break;
			case self::RECIPIENT_CUSTOMER:
				$this->_mailer->setMailToLabel($this->_object->getFullName())
						->setMailTo($this->_object->getEmail());
				break;
			default:
				error_log('Unsupported recipient '.$this->_options['recipient'].' given');
				return false;
				break;
		}

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

			return $parser->parseSimple();
		}

		return false;
	}

	private function _sendNeworderMail() {
		$customer = Models_Mapper_CustomerMapper::getInstance()->find($this->_object->getUserId());
		switch ($this->_options['recipient']) {
			case Tools_Security_Acl::ROLE_ADMIN:
			case self::RECIPIENT_SALESPERSON:
				$this->_mailer->setMailToLabel('Sales person')
						->setMailTo(!empty($this->_storeConfig['email'])?$this->_storeConfig['email']:$this->_configHelper->getAdminEmail());
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

		return $this->_send();
	}

	private function _sendTrackingnumberMail(){
		$customer = Models_Mapper_CustomerMapper::getInstance()->find($this->_object->getUserId());
		switch ($this->_options['recipient']) {
			case self::RECIPIENT_SALESPERSON:
				$this->_mailer->setMailToLabel('Sales person')
						->setMailTo(!empty($this->_storeConfig['email'])?$this->_storeConfig['email']:$this->_configHelper->getAdminEmail());
				break;
			case self::RECIPIENT_CUSTOMER:
				if ($customer && $customer->getEmail()){
					$this->_mailer->setMailToLabel($customer->getFullName())->setMailTo($customer->getEmail());
				} else {
					return false;
				}
				break;
			default:
				error_log('Unsupported recipient '.$this->_options['recipient'].' given');
				return false;
				break;
		}

		if (false === ($body = $this->_preparseEmailTemplate())) {
			return false;
		}

		$this->_entityParser
			->objectToDictionary($this->_order, 'order')
			->objectToDictionary($customer);

		return $this->_send();
	}

}