<?php
/**
 * StoreMailWatchdog.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_EmailTriggerWatchdog implements Interfaces_Observer  {

	const TRIGGER_NEW_CUSTOMER  = 'newcustomer';

	const TRIGGER_NEW_ORDER     = 'neworder';

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

	public function __construct($options = array()) {
		$this->_storeConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
		$this->_configHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('config');
		$this->_websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
		$this->_options = $options;
		$this->_initMailer();
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
		// TODO: Implement notify() method.
		if (!$object){
			return false;
		}
		if (isset($this->_options['trigger'])){
			$methodName = '_send'.ucfirst(strtolower($this->_options['trigger'])).'Mail';
			if (method_exists($this, $methodName)){
				$this->$methodName($object);
			}
		}
	}


	private function _sendNewcustomerMail(Models_Model_Customer $customer){
		if (!$this->_prepareEmailBody(array($customer))) {
			return false;
		}

		$this->_mailer->setMailToLabel($customer->getFullName())
			->setMailTo($customer->getEmail())
			->setMailFrom(!empty($this->_storeConfig['email'])?$this->_storeConfig['email']:'admin@localhost')
			->setMailFromLabel($this->_storeConfig['company']);
		return ($this->_mailer->send() !== false);
	}

	private function _prepareEmailBody($objects){
		if (!is_array($objects)){
			$objects = array($objects);
		}
		$tmplName = strtolower($this->_options['trigger']).'template';
		$tmplMessage = strtolower($this->_options['trigger']).'message';
		$mailTemplate = isset($this->_storeConfig[$tmplName]) ? Application_Model_Mappers_TemplateMapper::getInstance()->find($this->_storeConfig[$tmplName]) : null;

		if (!empty($mailTemplate)){
			$entityParser = new Tools_Content_EntityParser();
			$entityParser->setDictionary(array(
				'emailmessage' => isset($this->_storeConfig[$tmplMessage]) ? $this->_storeConfig[$tmplMessage] : ''
			));
			$mailTemplate = $entityParser->parse($mailTemplate->getContent());

			$entityParser->setDictionary(array());
			foreach ($objects as $object) {
				$entityParser->objectToDictionary($object);
			}

			$mailTemplate = $entityParser->parse($mailTemplate);

			$themeData = Zend_Registry::get('theme');
			$extConfig = Zend_Registry::get('extConfig');
			$parserOptions = array(
				'websiteUrl'   => $this->_websiteHelper->getUrl(),
				'websitePath'  => $this->_websiteHelper->getPath(),
				'currentTheme' => $extConfig['currentTheme'],
				'themePath'    => $themeData['path'],
			);
			$parser = new Tools_Content_Parser($mailTemplate, Tools_Page_Tools::getCheckoutPage()->toArray(), $parserOptions);
			$this->_mailer->setBody($parser->parseSimple());
			return true;
		}

		return false;
	}

	private function _sendNeworderMail(Models_Model_CartSession $order) {

	}

}
