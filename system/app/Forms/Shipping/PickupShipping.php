<?php
/**
 * Class Forms_Shipping_PickupShipping
 */
class Forms_Shipping_PickupShipping extends Zend_Form
{

    const COMPARE_BY_AMOUNT = 'amount';

    const COMPARE_BY_WEIGHT = 'weight';

    public function init()
    {

        $this->addElement(
            'hidden',
            'shipper',
            array(
                'ignore' => true,
                'value' => Shopping::SHIPPING_PICKUP
            )
        );

        $this->setDecorators(array('Form', 'FormElements'));
        $this->setElementDecorators(
            array(
                array('Label', array('class' => '')),
                'ViewHelper'
            )
        );

        $this->addElement(
            'text',
            'title',
            array(
                'label' => 'Custom title'
            )
        );

        $this->addElement(
            'checkbox',
            'defaultPickupConfig',
            array(
                'label' => 'Default pickup behaviour'
            )
        );

        $this->addElement(
            'checkbox',
            'searchEnabled',
            array(
                'label' => 'Display location search on checkout'
            )
        );

        $this->addElement(
            'select',
            'units',
            array(
                'label' => 'Units',
                'value' => 'amount',
                'multiOptions' => array(
                    self::COMPARE_BY_AMOUNT => 'total amount',
                    self::COMPARE_BY_WEIGHT => 'order weight'
                )
            )
        );

    }
}
