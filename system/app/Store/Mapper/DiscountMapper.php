<?php
/**
 * DiscountMapper.php
 *
 * @method Store_Mapper_DiscountMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_DiscountMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_Discount';

    protected $_dbTable = 'Store_DbTable_Discount';

    /**
     * Save discount model to DB
     * @param $model Store_Model_Discount
     * @return Store_Model_Discount
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'discount_quantity' => $model->getDiscountQuantity(),
            'discount_price_sign' => $model->getDiscountPriceSign(),
            'discount_price_type' => $model->getDiscountPriceType(),
            'apply_scope' => $model->getApplyScope(),
            'discount_amount' => $model->getDiscountAmount()
        );
        if (!empty($data['id'])) {
            $discountExists = $this->find($data['id']);
            if ($discountExists instanceof Store_Model_Discount) {
                $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $data['id']);
                unset($data['id']);
                return $this->getDbTable()->update($data, $where);
            }
        }

        $where = $this->getDbTable()->getAdapter()->quoteInto('discount_quantity = ?', $data['discount_quantity']);
        $discountExists = $this->fetchAll($where);
        if (!empty($discountExists)) {
            return $this->getDbTable()->update($data, $where);
        }

        $id = $this->getDbTable()->insert($data);
        if ($id) {
            $model->setId($id);
        } else {
            throw new Exceptions_SeotoasterException('Can\'t save discount');
        }
        return $model;
    }

    /**
     * Get discounts config data
     *
     * @param int $quantity
     * @param int $productId
     * @return array
     */
    public function getDiscountDataConfig($quantity, $productId)
    {
        $whereLocal = $this->getDbTable()->getAdapter()->quoteInto('discount_quantity <= ?', $quantity);
        $whereLocal .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        $whereLocal .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('status = ?', 'enabled');
        $selectLocal = $this->getDbTable()->getAdapter()->select()->from(
            'shopping_quantity_discount_product',
            array('discount_quantity', 'discount_price_sign', 'discount_price_type', 'amount')
        )
            ->where($whereLocal);
        $localProductConfig = $this->getDbTable()->getAdapter()->fetchAssoc($selectLocal);
        $whereGlobal = $this->getDbTable()->getAdapter()->quoteInto('discount_quantity <= ?', $quantity);
        $selectGlobal = $this->getDbTable()->getAdapter()->select()->from(
            'shopping_quantity_discount',
            array('discount_quantity', 'discount_price_sign', 'discount_price_type', 'amount' => 'discount_amount')
        )
            ->where($whereGlobal);
        $globalProductConfig = $this->getDbTable()->getAdapter()->fetchAssoc($selectGlobal);
        return $localProductConfig + $globalProductConfig;
    }

    /**
     * Delete discount
     * @param int $id
     * @return bool Result of operation
     */
    public function delete($id)
    {
        if ($id) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
            return (bool)$this->getDbTable()->delete($where);
        }
    }
}