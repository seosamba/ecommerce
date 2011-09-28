<?php

/**
 * Shopping
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(__DIR__ . '/system/app'),
    get_include_path(),
)));

class Shopping extends Tools_Plugins_Abstract {
	/**
	 * json helper for sending well-formated json response
	 * @var Object 
	 */
	protected $_jsonHelper;
	
	public function  __construct($options, $seotoasterData) {
		parent::__construct($options, $seotoasterData);
		$this->_view->setScriptPath(__DIR__ . '/system/views/');
		$this->_jsonHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
	}
	
	public function run($requestedParams = array()) {
		$dispatchersResult = parent::run($requestedParams);
		if($dispatchersResult) {
			return $dispatchersResult;
		}
	}
	
	/** 
	 * Method renders shopping config screen and handling config saving.
	 */
	protected function configAction(){
		$configMapper = Models_Mapper_ShoppingConfig::getInstance();
		$config = $configMapper->getConfig();
		
		$form = new Forms_Config();
		$form->populate($config);
		if ($this->_request->isPost()){
			if ($form->isValid($this->_requestedParams)){
				foreach ($form->getValues() as $key => $subFormValues) {
					$configMapper->save($subFormValues);
				}
				$this->_jsonHelper->direct($form->getValues());
			} else {
				$this->_jsonHelper->direct($form->getMessages());
			}
		}
		$this->_view->form = $form;

		echo $this->_view->render('config.phtml');
	}
	
	/** 
	 * Method renders zones screen and handling zone saving
	 * @return html|json
	 * @todo better response
	 */
	protected function zonesAction(){
		if ($this->_request->isPost()){
			$zonesMapper = Models_Mapper_Zone::getInstance();
			$toRemove = $this->_request->getParam('toRemove');
			if (is_array($toRemove) && !empty ($toRemove)){
				$deleted = $zonesMapper->delete($toRemove);
			}
			$zones = $this->_request->getParam('zones');
			if (is_array($zones) && !empty ($zones)){
				$result = array();
				foreach ($zones as $id => $zone) {
					$zone = $zonesMapper->createModel($zone);
					$result[$id] = $zonesMapper->save($zone);
				}
			}
			$this->_jsonHelper->direct(array(
				'done'=>true,
				'id' => $result,
				'deleted' => isset($deleted) ? $deleted : null 
				));
		}
		echo $this->_view->render('zones.phtml');
	}
	
	/**
	 * Method renders tax configuration screen and handling tax saving
	 * @return html
	 */
	protected function taxesAction() {
		echo $this->_view->render('taxes.phtml');
	}
	
	/**
	 * Method used to get prepopulated data from db
	 * for usage in AJAX scope 
	 * @return json 
	 */
	protected function getdataAction(){
		$data = array();
		switch ( strtolower($this->_requestedParams['type']) ) {
			case 'countrylist':
				$data = Tools_Geo::getCountries();
				asort($data);
				break;
			case 'stateslist':
				$data = Tools_Geo::getState();
				break;
			case 'states':
				$country = $this->_request->getParam('country');
				$data = Tools_Geo::getState($country?$country:null, true);	
				break;
			case 'zones':
				$zonesMapper = Models_Mapper_Zone::getInstance();
				$zones = $zonesMapper->fetchAll();
				$data = array();
				foreach ($zones as $zone) {
					$data[] = $zone->toArray();
				}
				break;
			default :
				break;
		}
		$this->_jsonHelper->direct($data);
	}

	protected function demoAction(){
		$y = new Models_DbTable_Zone();
		if ($this->_requestedParams['filldb'] == 'state'){
			$t = new Models_DbTable_State();
			$t->getAdapter()->beginTransaction();
			foreach (Tools_Geo::$_states as $country => $states){
				foreach ($states as $name => $state) {
					$t->insert(array(
						'country'	=> $country,
						'state'		=> $name,
						'name'		=> $state
					));
				}
			}
			$t->getAdapter()->commit();
		}
	}
}