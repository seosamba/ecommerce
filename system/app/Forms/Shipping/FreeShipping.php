<?php
/**
 * Free.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_Shipping_FreeShipping extends Zend_Form {

	public function init() {
		//free shipping
		$this->addElement('text', 'amount', array(
			'name'      => 'price',
			'belongsTo' => 'free',
			'label'     => 'For orders over'
		));

		$this->addElement('select', 'destination', array(
			'label'         => 'delivery to',
			'name'          => 'destination',
			'belongsTo'     => 'free',
			'multiOptions'  => array(
				'0'             => 'select',
				'national'      => 'National',
				'international' => 'International',
				'both'          => 'Both'
			)
		));
	}


}
