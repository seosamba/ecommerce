<?php


class Forms_Shipping_MarkupShipping extends Zend_Form {

	public function init() {
		//markup shipping
		$this->addElement('hidden', 'shipper', array(
			'ignore' => true,
			'value' => Shopping::SHIPPING_MARKUP
		));

		$this->addElement('text', 'price', array(
			'label' => 'Markup amount'
		));
        
        $this->addElement('select', 'modifierSign', array(
			'label' => 'Modifier sign',
            'multiOptions'  => array(
				'+'  => '+',
                '-' => '-'
			)
		));
        
        $currency = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('currency');
        $currency = isset($currency) ? $currency : '';
        $this->addElement('select', 'modifierType', array(
			'label' => 'Modifier type',
            'multiOptions'  => array(
				'percent'  => '%',
                'unit'  => $currency
			)
		));
	}


}
