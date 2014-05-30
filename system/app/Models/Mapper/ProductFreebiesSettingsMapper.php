<?php
/**
 *
 */
class Models_Mapper_ProductFreebiesSettingsMapper extends Application_Model_Mappers_Abstract {

    protected $_dbTable = 'Models_DbTable_ProductFreebiesSettings';

    public function save($data) {
        $productId = null;
        if(isset($data['prod_id']))  {
            $productId = $data['prod_id'];
        }
        if($this->find($productId)) {
            $this->getDbTable()->update($data, array('prod_id=?' => $productId));
        } else {
            $data['prod_id'] = $productId;
            $this->getDbTable()->insert($data);
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

   public function freebiesAssoc(){
       $select = $this->getDbTable()->getAdapter()->select()->from('shopping_product_freebies_settings', array('prod_id', 'price_value', 'quantity'));
       return $this->getDbTable()->getAdapter()->fetchAssoc($select);
   }

   public function getFreebies($id){
       $where = $this->getDbTable()->getAdapter()->quoteInto('spfs.prod_id = ?', $id);
       $select = $this->getDbTable()->getAdapter()->select()
           ->from(array('spfs'=>'shopping_product_freebies_settings'))
           ->joinleft(array('sphp' => 'shopping_product_has_freebies'), 'spfs.prod_id = sphp.product_id')
           ->where($where);
       return $this->getDbTable()->getAdapter()->fetchAll($select);

   }

   public function getFreebiesByProdIds($ids){
        $where = $this->getDbTable()->getAdapter()->quoteInto('spfs.prod_id IN (?)', $ids);
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('spfs'=>'shopping_product_freebies_settings'))
            ->joinleft(array('sphp' => 'shopping_product_has_freebies'), 'spfs.prod_id = sphp.product_id')
            ->where($where);
        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }

    public function getProductHasFreebiesByPageId($productId){
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('shopping_product_has_freebies'))
            ->where($where);
        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }

    public function getFreebiesIdsByProductId($productId){
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        $select = $this->getDbTable()->getAdapter()->select()
            ->from('shopping_product_has_freebies', array('freebies_id'))
            ->where($where);
        return $this->getDbTable()->getAdapter()->fetchCol($select);
    }

}
