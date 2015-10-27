<?php


class Forms_Shipping_ShippingRestriction extends Zend_Form
{

    const DESTINATION_NATIONAL = 'national';

    const DESTINATION_INTERNATIONAL = 'international';

    const DESTINATION_BOTH = 'both';

    const DESTINATION_ZONE = 'zone';

    public function init()
    {

        $this->addElement('hidden', 'shipper', array(
            'ignore' => true,
            'value' => Shopping::SHIPPING_RESTRICTION_ZONES
        ));

        $destinationOptions = array(
            0 => 'No restriction',
            self::DESTINATION_NATIONAL => 'National',
            self::DESTINATION_INTERNATIONAL => 'International',
            self::DESTINATION_ZONE => 'Zones'

        );

        $zoneOptionsOptions = array();
        $zones = Models_Mapper_Zone::getInstance()->fetchAll();
        if (!empty($zones)) {
            foreach ($zones as $zone) {
                $zoneOptionsOptions[$zone->getId()] = $zone->getName();
            }
        }

        $this->addElement('multiCheckbox', 'restrictZones', array(
            'ignore' => true,
            'label' => 'Restrict buyers by zone',
            'multiOptions' => $zoneOptionsOptions,
            'label_class' => array(
                'class' => 'inline-block'
            ),
            'label_style' => 'margin-right:15px; white-space:nowrap',
            'class' => 'mr5'

        ));

        $this->getElement('restrictZones')->setSeparator('');

        $this->addElement('radio', 'restrictDestination', array(
            'label' => 'Restrict to buyers located in',
            'multiOptions' => $destinationOptions,
            'value' => 0,
            'class' => 'restrict-destination',
            'label_class' => array(
                'class' => 'grid_3'
            ),

        ));

        $this->addElement('text', 'restrictionMessage', array(
            'label' => 'Restricted with message'
        ));


        $this->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            'Label',
            array('HtmlTag', array('tag' => 'p'))
        ));

    }


}
