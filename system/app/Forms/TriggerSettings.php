<?php
/**
 * TriggerSettings.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_TriggerSettings extends Zend_Form {

	public function init() {
		$this->setLegend('Action Emails')
			->setDecorators(array('Form', 'FormElements'));

		$templates = Application_Model_Mappers_TemplateMapper::getInstance()->findByType(Application_Model_Models_Template::TYPE_MAIL);
		$templateList = array();
		if (!empty($templates)){
			foreach ($templates as $tmpl) {
				$templateList[$tmpl->getName()] = $tmpl->getName();
			}
		}

		$this->addDisplayGroup(array(
			new Zend_Form_Element_Select('new-customer-template', array('label' => 'When new customer signup, use template','multiOptions' => $templateList)),
			new Zend_Form_Element_Textarea('new-customer-message', array('label' => 'with following message', 'class' => 'h100'))
		), 'new-customer-trigger', array(
			'class' => 'email-trigger',
			'decorators' => array(new Zend_Form_Decorator_FormElements(), new Zend_Form_Decorator_Fieldset())
		));

		$this->addDisplayGroup(array(
			new Zend_Form_Element_Select('new-order-template', array('label' => 'When clien placed an order, use template','multiOptions' => $templateList)),
			new Zend_Form_Element_Textarea('new-order-message', array('label' => 'with following message', 'class' => 'h100'))
		), 'new-order-trigger', array(
			'class' => 'email-trigger',
			'decorators' => array(new Zend_Form_Decorator_FormElements(), new Zend_Form_Decorator_Fieldset())
		));

	}
}
