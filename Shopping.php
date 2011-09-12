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
	
	public function  __construct($options, $seotoasterData) {
		parent::__construct($options, $seotoasterData);
		$this->_view->setScriptPath(__DIR__ . '/system/views/');
	}
	
	public function run($requestedParams = array()) {
		$dispatchersResult = parent::run($requestedParams);
		if($dispatchersResult) {
			return $dispatchersResult;
		}
	}
	
	protected function configAction(){
		$form = new Forms_Settings();
		if ($this->_request->isPost()){
			if ($form->isValid($this->_requestedParams)){
				echo 'form valid';
			} else {
				echo 'form not valid';
			}
		}
		$this->_view->settingsForm = $form;
		
		echo $this->_view->render('config.phtml');
	}
	
	protected function productAction(){
		
	}
	
}