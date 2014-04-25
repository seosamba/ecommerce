<?php
/**
 * PickupLocationConfig.php
 *
 * @method Store_Mapper_PickupLocationConfigMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_PickupLocationConfigMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_PickupLocationConfig';

    protected $_dbTable = 'Store_DbTable_PickupLocationConfig';


    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }
        $data = array(
            'id' => $model->getId(),
            'amount_type_limit' => $model->getAmountTypeLimit(),
            'amount_limit' => $model->getAmountLimit()
        );

        $locationZones = $model->getLocationZones();
        $where = $this->getDbTable()->getAdapter()->quoteInto("id=?", $data['id']);
        $existRow = $this->fetchAll($where);
        if (!empty($existRow)) {
            $this->getDbTable()->update($data, $where);
        }else{
            $this->getDbTable()->insert($data);
        }
        $this->_saveLocationZones($data['id'], $locationZones);

    }

    private function _saveLocationZones($configId, $zones)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto("config_id=?", $configId);
        $this->getDbTable()->getAdapter()->delete('shopping_pickup_location_zones', $where);
        foreach ($zones as $confZoneId => $value) {
            if (is_array($value)) {
                $data = array(
                    'config_id' => $configId,
                    'pickup_location_category_id' => $value['zoneId'],
                    'amount_location_category' => $value['zoneAmount'],
                    'config_zone_id' => $confZoneId
                );
                $this->getDbTable()->getAdapter()->insert('shopping_pickup_location_zones', $data);
            }
        }
    }

    public function getConfig()
    {
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('shopping_pickup_location_config', array('id', 'amount_type_limit', 'amount_limit')));
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    public function getLocationZones()
    {
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(
                'shopping_pickup_location_zones',
                array(
                    'conf_key' => new Zend_Db_Expr("CONCAT(config_id, '_', config_zone_id)"),
                    'pickup_location_category_id',
                    'amount_location_category',
                    'config_zone_id',
                    'config_id'
                )
            );
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }


    public function deleteConfig($configId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto("id = ?", $configId);
        $this->getDbTable()->delete($where);
    }
}
