<?php
/**
 * DiscountProductMapper.php
 *
 * @method Store_Mapper_DiscountProductMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_DiscountProductMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_DiscountProduct';

    protected $_dbTable = 'Store_DbTable_DiscountProduct';

    /**
     * Save discount model to DB
     * @param $model Store_Model_DiscountProduct
     * @return Store_Model_DiscountProduct
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'product_id' => $model->getProductId(),
            'quantity' => $model->getQuantity(),
            'price_sign' => $model->getPriceSign(),
            'price_type' => $model->getPriceType(),
            'status' => $model->getStatus(),
            'amount' => $model->getAmount()
        );
        if (!empty($data['product_id'])) {
            $where[] = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $data['product_id']);
            $where[] = $this->getDbTable()->getAdapter()->quoteInto('quantity = ?', $data['quantity']);
            $discountExists = $this->getDbTable()->fetchRow($where);
        }

        if (!empty($discountExists)) {
            return $this->getDbTable()->update($data, $where);
        }

        return $this->getDbTable()->insert($data);

    }

    /**
     * Delete discount
     * @param int $id
     * @param int $quantity product quantity
     * @return bool Result of operation
     */
    public function delete($id, $quantity)
    {
        if ($id && $quantity) {
            $where[] = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $id);
            $where[] = $this->getDbTable()->getAdapter()->quoteInto('quantity = ?', $quantity);
            return (bool)$this->getDbTable()->delete($where);
        }
        return false;
    }

    /**
     * Delete All local discounts
     * @param int $quantity
     * @return bool Result of operation
     */
    public function deleteAll($quantity)
    {
        if ($quantity) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('quantity = ?', $quantity);
            return (bool)$this->getDbTable()->delete($where);
        }
        return false;
    }
}
