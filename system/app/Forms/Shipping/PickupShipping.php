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
        $translator = Zend_Registry::get('Zend_Translate');

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
                'label' => $translator->translate('Custom title')
            )
        );

        $this->addElement(
            'checkbox',
            'defaultPickupConfig',
            array(
                'label' => $translator->translate('Default pickup behaviour')
            )
        );

        $this->addElement(
            'checkbox',
            'searchEnabled',
            array(
                'label' => $translator->translate('Display location search on checkout')
            )
        );

        $this->addElement(
            'text',
            'gmapsZoom',
            array(
                'label' => $translator->translate('Map zoom'),
                'placeholder' => $translator->translate('Zoom range (6-18)')
            )
        );

        $this->addElement(
            'select',
            'units',
            array(
                'label' => $translator->translate('Units'),
                'value' => 'amount',
                'multiOptions' => array(
                    self::COMPARE_BY_AMOUNT => $translator->translate('total amount'),
                    self::COMPARE_BY_WEIGHT => $translator->translate('order weight')
                )
            )
        );

    }
}
