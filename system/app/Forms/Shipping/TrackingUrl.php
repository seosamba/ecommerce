<?php
class Forms_Shipping_TrackingUrl extends Zend_Form {


    public function init() {
        $trackingUrlDataMapper =   Models_Mapper_ShoppingShippingUrlMapper::getInstance();
        $trackingData = $trackingUrlDataMapper->fetchAll();

        $arrData = array('Select to edit');
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
            'multiOptions' => $arrData,
            'style'        => 'width: 40%;'
        )));
    }

}