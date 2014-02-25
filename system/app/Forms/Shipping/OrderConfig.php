<?php

/**
 * Order Config Form
 *
 * Class Forms_Shipping_OrderConfig
 */

class Forms_Shipping_OrderConfig extends Zend_Form {

	public function init() {
		//order config
		$this->addElement('hidden', 'shipper', array(
			'ignore' => true,
			'value' => Shopping::ORDER_CONFIG
		));

		$this->addElement('text', 'quantity', array(
			'label' => 'Products quantity in cart'
		));
	}

}
