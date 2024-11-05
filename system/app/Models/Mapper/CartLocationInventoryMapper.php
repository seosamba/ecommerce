<?php

/**
 * Class Models_Mapper_CartLocationInventoryMapper
 *
 * @method Models_Mapper_CartLocationInventoryMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Models_Mapper_CartLocationInventoryMapper extends Application_Model_Mappers_Abstract
{

    protected $_dbTable = 'Models_DbTable_CartLocationInventoryDbtable';

    protected $_model = 'Models_Model_CartLocationInventoryModel';

    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            throw new Exceptions_SeotoasterException('Given parameter should be ' . $this->_model . ' instance');
        }
        $data = array(
            'cart_id'            => $model->getCartId(),
            'product_id'         => $model->getProductId(),
            'location_id'        => $model->getLocationId(),
            'location_inventory' => $model->getLocationInventory(),
            'product_status'     => $model->getProductStatus(),
        );

        $id = $model->getId();
        if ($id) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
            $this->getDbTable()->update($data, $where);
        } else {
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);
        }
        return $model;
    }

    public function findByCartProductLocationId($cartId, $productId, $locationId) {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('location_id = ?', $locationId);
        return $this->_findWhere($where);
    }

    public function findByCartProductId($cartId, $productId) {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);

        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_cart_location_inventory');
        $select->where($where);
        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }

    public function delete($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
        return $this->getDbTable()->getAdapter()->delete('shopping_cart_location_inventory', $where);
    }

    public function deleteByCartIdAndProductId($cartId, $productId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        return $this->getDbTable()->getAdapter()->delete('shopping_cart_location_inventory', $where);
    }

    public function deleteByCartId($cartId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        return $this->getDbTable()->getAdapter()->delete('shopping_cart_location_inventory', $where);
    }

    public function getLocationInventoryInfo($cartId) {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('pscli' => 'shopping_cart_location_inventory'), array(
            'pscli.id',
            'pscli.cart_id',
            'pscli.product_id',
            'product_name' => 'sp.name',
            'pscli.location_id',
            'location_name' => 'spl.name',
            'pscli.location_inventory',
            'pscli.product_status',
            'spl.email',
            'spl.send_email_notification',
        ))
        ->joinLeft(array('spl' => 'shopping_pickup_location'), 'pscli.location_id = spl.id', array())
        ->joinLeft(array('sp' => 'shopping_product'), 'pscli.product_id = sp.id', array());
        $select->where($where);
        return $this->getDbTable()->getAdapter()->fetchAll($select);

    }

    public function getLocationInventoryInfoByCartIds($cartIds) {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id IN (?)', $cartIds);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('pscli' => 'shopping_cart_location_inventory'), array(
            'pscli.cart_id',
            'pscli.product_id',
            'pscli.location_id',
            'pscli.location_inventory',
            'pscli.product_status',
        ));
        $select->where($where);
        return $this->getDbTable()->getAdapter()->fetchAll($select);

    }


}

