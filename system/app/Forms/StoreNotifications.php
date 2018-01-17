<?php

class Forms_StoreNotifications extends Zend_Form {

    public function init() {
        $translator = Zend_Registry::get('Zend_Translate');

        $this->setLegend('Store notifications')
            ->setDecorators(array('Form', 'FormElements'));


        $this->addElement('textarea', 'outOfStock', array(
            'id'    => 'out-of-stock',
            'label' => $translator->translate('Out of stock product message:'),
            'class' => 'out-of-stock',
            'cols'  => '20',
            'rows'  => '3',
            'maxlength' => '250'
        ));

        $this->addElement('textarea', 'limitQty', array(
            'id'    => 'limit-qty',
            'label' => $translator->translate('Limit quantity product message:'),
            'class' => 'limit-qty',
            'cols'  => '20',
            'rows'  => '3',
            'maxlength' => '250'
        ));
    }

}