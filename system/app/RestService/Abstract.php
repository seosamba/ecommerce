<?php
/**
 * RestService_Abstract
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
abstract class RestService_Abstract {

	const REST_STATUS_OK = 200;
	const REST_STATUS_CREATED = 201;
	const REST_STATUS_ACCEPTED = 202;
	const REST_STATUS_NO_CONTENT = 204;
	const REST_STATUS_BAD_REQUEST = 400;
	const REST_STATUS_UNAUTHORIZED = 401;
	const REST_STATUS_FORBIDDEN = 403;
	const REST_STATUS_NOT_FOUND = 404;

	protected $_statusCodes = array(
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		204 => 'No content',
		400 => 'Bad request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found'
	);

	/**
	 * @var Zend_Controller_Request_Http
	 */
	protected $_request;

	/**
	 * @var Zend_Controller_Response_Http
	 */
	protected $_response;

	protected $_format;

	protected $_responseHelper;

	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response){
		$this->setRequest($request)->setResponse($response);

		$this->_responseHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('response');
		$this->_responseHelper->init();

		$this->_jsonHelper      = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$this->_jsonHelper->init();
		$this->init();
	}

	public function init() {
	}

	public function dispatch(){
		$method = $this->_request->getMethod();
		if ($method === 'POST' && null !== ($extraMethod = $this->_request->getParam('_method', null))){
			$extraMethod = strtoupper(filter_var($extraMethod, FILTER_SANITIZE_STRING));
			if (in_array($extraMethod, array('PUT', 'DELETE'))){
				$method = $extraMethod;
			}
		}
		$method = strtolower($method).'Action';
		if (method_exists($this, $method)){
			$this->_jsonHelper->direct($this->$method());
		} else {
			throw new Exceptions_SeotoasterPluginException(get_called_class().' doesn\'t have '.$method.' implemented');
		}
	}

	protected function _error($message = null, $statusCode = self::REST_STATUS_BAD_REQUEST){
		if (is_numeric($statusCode)){
			$statusCode = intval($statusCode);
		}
		$this->_response->clearAllHeaders()->clearBody();
		$this->_response->setHttpResponseCode(intval($statusCode))
						->setHeader('Content-Type', 'application/json', true);
		if (!empty($message)){
			$this->_response->setBody(json_encode($message));
		}

		$this->_response->sendResponse();
		exit();
	}

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
	 */
	abstract public function getAction();

	/**
	 * The post action handles POST requests; it should accept and digest a
	 * POSTed resource representation and persist the resource state.
	 */
	abstract public function postAction();

	/**
	 * The put action handles PUT requests and receives an 'id' parameter; it
	 * should update the server resource state of the resource identified by
	 * the 'id' value.
	 */
	abstract public function putAction();

	/**
	 * The delete action handles DELETE requests and receives an 'id'
	 * parameter; it should update the server resource state of the resource
	 * identified by the 'id' value.
	 */
	abstract public function deleteAction();

	public function setResponse($response) {
		$this->_response = $response;
		return $this;
	}

	public function getResponse() {
		return $this->_response;
	}

	public function setRequest($request) {
		$this->_request = $request;
		return $this;
	}

	public function getRequest() {
		return $this->_request;
	}

	public function setFormat($format) {
		$this->_format = $format;
		return $this;
	}

	public function getFormat() {
		return $this->_format;
	}

}
