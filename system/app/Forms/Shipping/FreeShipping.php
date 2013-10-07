<?php
/**
 * Free.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_Shipping_FreeShipping extends Zend_Form {

	const DESTINATION_NATIONAL = 'national';

	const DESTINATION_INTERNATIONAL = 'international';

	const DESTINATION_BOTH = 'both';

	public function init() {
		//free shipping
		$this->addElement('hidden', 'shipper', array(
			'ignore' => true,
			'value' => Shopping::SHIPPING_FREESHIPPING
		));

		$this->addElement('text', 'cartamount', array(
			'label' => 'For orders over'
		));

		$this->addElement('select', 'destination', array(
			'label'         => 'delivery to',
			'multiOptions'  => array(
				'0'             => 'select',
				self::DESTINATION_NATIONAL      => 'National',
				self::DESTINATION_INTERNATIONAL => 'International',
				self::DESTINATION_BOTH          => 'Both'
			)
		));

        $this->addElement('text', 'errormessage', array(
            'label' => 'Error message at the checkout page'
        ));
	}


}
