<?php
/**
 * Shipping configuration form
 *
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 */

class Forms_Shipping extends Zend_Form {

	public function init() {
		$this->setAction('/plugin/shopping/run/shipping/')
			->setMethod(Zend_Form::METHOD_POST)
			->setDecorators(array(
				'FormElements'
			));

		$this->addElement(new Zend_Form_Element_Textarea(array(
			'label' => 'Notice for user',
			'name'  => 'shippingNotice',
			'id'    => 'shipping-notice',
		)));

		$this->addElement(new Zend_Form_Element_Radio(array(
			'name'         => 'shippingType',
			'id'           => 'shipping-type',
			'multiOptions' => array(
				0 => 'Pickup: (no shipping)',
//				1 => 'Shipping per order amount',
//				2 => 'Shipping per total weight'
			)
		)));

		$perAmount = new Forms_PerAmount();

		$this->addSubForms(array(
			'perAmount' => $perAmount,
		));


		$this->addElement(new Zend_Form_Element_Submit(array(
			'name'  => 'saveShipping',
			'label' => 'Save'
		)));


		foreach ($this->getSubForms() as $id => $form){
			$form
				->addDecorator('Form')
				->addDecorator('HtmlTag', array('tag' => 'div', 'id' => $id . '-frm'))
				->setElementDecorators(
					array(
						'ViewHelper'
						, 'Label'
						, array('HtmlTag', array('tag' => 'div'))
						)
					);
		}
	}

}
