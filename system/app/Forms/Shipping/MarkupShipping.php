<?php


class Forms_Shipping_MarkupShipping extends Zend_Form {

	public function init() {
		//markup shipping
		$this->addElement('hidden', 'shipper', array(
			'ignore' => true,
			'value' => Shopping::SHIPPING_MARKUP
		));

		$this->addElement('text', 'currency', array(
			'label' => 'Currency'
		));

		$this->addElement('text', 'price', array(
			'label' => 'Markup Price'
		));
	}


}
