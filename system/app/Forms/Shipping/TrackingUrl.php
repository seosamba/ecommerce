<?php
class Forms_Shipping_TrackingUrl extends Zend_Form {


    public function init() {
        $translator = Zend_Registry::get('Zend_Translate');
        $trackingUrlDataMapper =   Models_Mapper_ShoppingShippingUrlMapper::getInstance();
        $trackingData = $trackingUrlDataMapper->fetchAll();
        $defaultSelect = $translator->translate('Select to edit');

        $arrData = array($defaultSelect);
        if(!empty($trackingData)) {
            foreach ($trackingData as $dataValue) {
                    $arrData[$dataValue['id']] = $dataValue['name'];
            }
        }

        $this->addElement('hidden', 'shipper', array(
            'ignore' => true
        ));

        $this->addElement('text', 'addNew', array(
            'label' => 'Add new'
        ));
        $this->addElement(new Zend_Form_Element_Select(array(
            'name'         => 'quantity',
            'id'          => 'shipping-url',
            'multiOptions' => $arrData
        )));
    }

}