<?php

/**
 * Operations settings
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_OperationsSettings extends Zend_Form {

	public function init() {
		$this->setLegend('Operations')
			 ->setDecorators(array('Form', 'FormElements'));

        $translator = Zend_Registry::get('Zend_Translate');

        $this->addElement('checkbox', 'disabledStore', array(
            'label' => $translator->translate('Stop taking online orders'),
            'class' => 'grid_6 alpha'
        ));

        $this->addElement('text', 'disabledStoreMessage', array(
            'label' => $translator->translate('Stop taking online orders message'),
            'class' => 'grid_6 alpha hidden',
            'placeholder' => $translator->translate('Online ordering unavailable')
        ));

        $this->addElement('text', 'operationalHours', array(
            'label' => $translator->translate('store operational hours')
        ));

        $this->addElement('checkbox', 'useOperationalHoursForOrders', array(
            'label' => $translator->translate('Limit online orders taking to store hours above'),
            'class' => 'grid_6 alpha'
        ));

        $this->addElement('text', 'useOperationalHoursForOrdersMessage', array(
            'label' => $translator->translate('Use operational hours for online orders message'),
            'class' => 'grid_6 alpha'
        ));

        $pickupSlaGaOptions = Tools_Misc::$_pickupSlaGm;
        $pickupSlaGaOptions = array_merge(array('0' => $translator->translate('-- Not Set --')), $pickupSlaGaOptions);
        foreach ($pickupSlaGaOptions as $key => $pickupSlaGaOption) {
            $pickupSlaGaOptions[$key] = $translator->translate($pickupSlaGaOption);
        }

        $this->addElement('select', 'pickupSlaGa', array(
            'label'             => 'Pickup Sla',
            'class'             => '',
            'disableTranslator' => 'true',
            'multiOptions'      => $pickupSlaGaOptions
        ));

        $pickupMethodGmOptions = Tools_Misc::$_pickupMethodGm;
        foreach ($pickupMethodGmOptions as $key => $pickupMethodGmOption) {
            $pickupMethodGmOptions[$key] = $translator->translate($pickupMethodGmOption);
        }
        $pickupMethodGmOptions = array_merge(array('0' => $translator->translate('-- Not Set --')), $pickupMethodGmOptions);
        $this->addElement('select', 'pickupMethodGa', array(
            'label'             => 'Pickup method',
            'class'             => '',
            'disableTranslator' => 'true',
            'multiOptions'      => $pickupMethodGmOptions
        ));

        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        array_pop($timezones);

        $this->addElement(new Zend_Form_Element_Select(
            array(
                'name' => 'timezone',
                'id' => 'user-timezone',
                'label' => $translator->translate('Timezone'),
                'class' => 'grid_6 alpha mb10px',
                'multiOptions' => array('0' => $translator->translate('Select timezone')) + array_combine($timezones, $timezones)
            )
        ));

        $locations = Tools_Misc::getLocationsData();

        $locationsList = array();
        if(!empty($locations)) {
            foreach($locations as $location) {
                $locationsList[$location['id']] = $location['name'];
            }
        }

        $this->addElement(new Zend_Form_Element_Select(
            array(
                'name' => 'defaultLocationId',
                'id' => 'default-location-id',
                'label' => $translator->translate('Global default location'),
                'class' => 'grid_6 alpha mb10px',
                'multiOptions' => array('0' => $translator->translate('Select global default location')) + $locationsList
            )
        ));


    }

}
