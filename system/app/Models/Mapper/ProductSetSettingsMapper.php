<?php
/**
 *
 * @author Eugene I. Nezhuta <eugene@seotaoster.com>
 */
class Models_Mapper_ProductSetSettingsMapper extends Application_Model_Mappers_Abstract {

    protected $_dbTable = 'Models_DbTable_ProductSetSettings';

    public function save($model) {
        $productId = null;
        if(isset($model['productId']))  {
            $productId = $model['productId'];
        }

        if($this->find($productId)) {
            $this->getDbTable()->update($model, array('productId=?' => $productId));
        } else {
            $model['productId'] = $productId;
            $this->getDbTable()->insert($model);
        }
    }

    public function find($id) {
        $row = $this->getDbTable()->find($id);
        $row = $row->current();
        if(!$row) {
            return null;
        }
        return $row->toArray();
    }

    public function fetchAll($where = null, $order = array()) {

    }

}
