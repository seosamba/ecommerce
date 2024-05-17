<?php

/**
 * GatewayLabelMapper.php
 *
 * @method Store_Mapper_GatewayLabelMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_GatewayLabelMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_GatewayLabelModel';

    protected $_dbTable = 'Store_DbTable_GatewayLabelDbtable';

    /**
     * @param $model Store_Model_GatewayLabelModel
     * @return mixed
     * @throws Exceptions_SeotoasterException
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            throw new Exceptions_SeotoasterException('Given parameter should be ' . $this->_model . ' instance');
        }

        $data = array(
            'gateway' => $model->getGateway(),
            'gateway_label' => $model->getGatewayLabel()
        );

        $gatewayExists = $this->getGateway($data['gateway']);
        if (!$gatewayExists instanceof Store_Model_GatewayLabelModel) {
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);

        } else {
            $where = $this->getDbTable()->getAdapter()->quoteInto("gateway = ?", $data['gateway']);
            $this->getDbTable()->update($data, $where);
        }

        return $model;
    }

    /**
     * Get gateways labels
     *
     * @return array
     */
    public function getLabelsList()
    {
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_gateway_label', array('gateway', 'gateway_label'));
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Get by gateway name
     *
     * @param string $gatewayName gateway name
     * @return Store_Model_GatewayLabelModel|null
     */
    public function getGateway($gatewayName)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('gateway = ?', $gatewayName);

        return $this->_findWhere($where);
    }

}
