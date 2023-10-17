<?php
/**
 * Config
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_Config extends Zend_Form {

	public function init() {
		$this->setAction($this->getView()->websiteUrl . 'plugin/shopping/run/config' )
			->setMethod(Zend_Form::METHOD_POST)
			->setDecorators(array(
			'FormElements'
				));
		$general = new Forms_GeneralSettings();
		$display = new Forms_BasicsSettings();
		$storeNotifications = new Forms_StoreNotifications();
		$operationsSettings = new Forms_OperationsSettings();

		$this->addSubForms(array(
			'general' => $general,
			'display' => $display,
            'notifications' => $storeNotifications,
            'operations'    => $operationsSettings
		));
		
		foreach ($this->getSubForms() as $id => $form){
			$form
				->addDecorator('Form')
				->addDecorator('HtmlTag', array('tag' => 'div', 'id' => $id.'-tab'))
				->setElementDecorators(
					array(
						'ViewHelper'
						, array('Label', array('class' => 'grid_6' ))
						, array('HtmlTag', array('tag' => 'div', 'class' => 'mt5px'))
						)
					);
		}
	}

}