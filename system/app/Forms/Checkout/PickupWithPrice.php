<?php
/**
 * Class Forms_Checkout_PickupWithPrice
 */
class Forms_Checkout_PickupWithPrice extends Forms_Checkout_Pickup
{

    public function init()
    {
        parent::init();

        $this->addElement(
            new Zend_Form_Element_Hidden(array(
                'name' => 'pickupLocationId',
                'required' => true,
                'class' => array('required')
            ))
        );

        $this->getElement('submitpickup')->setOrder(count($this->getElements()));

        $this->setElementDecorators(
            array(
                'ViewHelper',
                'Errors',
                'Label',
                array('HtmlTag', array('tag' => 'p'))
            )
        );

        $this->setElementFilters(
            array(
                new Zend_Filter_StripTags(),
                new Zend_Filter_StringTrim()
            )
        );

        $this->getElement('submitpickup')->removeDecorator('Label');
        $this->getElement('mobilecountrycode')->removeDecorator('HtmlTag');
        $this->getElement('mobile')->removeDecorator('HtmlTag');

    }

}
