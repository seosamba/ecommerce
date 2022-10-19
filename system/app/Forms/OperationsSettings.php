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


    }

}
