<?php

/**
 * CartSessionOptionMapper.php
 *
 * @method Store_Mapper_CartSessionOptionMapper  getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_CartSessionOptionMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_CartSessionOption';

    protected $_dbTable = 'Store_DbTable_CartSessionOption';

    /**
     *
     **/
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'cart_id' => $model->getCartId(),
            'cart_content_id' => $model->getCartContentId(),
            'product_id' => $model->getProductId(),
            'option_id' => $model->getOptionId(),
            'option_title' => $model->getOptionTitle(),
            'option_type' => $model->getOptionType(),
            'title' => $model->getTitle(),
            'priceSign' => $model->getPriceSign(),
            'priceValue' => $model->getPriceValue(),
            'priceType' => $model->getPriceType(),
            'weightSign' => $model->getWeightSign(),
            'weightValue' => $model->getWeightValue(),
            'cart_item_key' => $model->getCartItemKey(),
            'cart_item_option_key' => $model->getCartItemOptionKey(),
            'option_selection_id' => $model->getOptionSelectionId()
        );

        if ($model->getId()) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
            $this->getDbTable()->update($data, $where);
        } else {
            $id = $this->getDbTable()->insert($data);
            if ($id) {
                $model->setId($id);
            } else {
                throw new Exceptions_SeotoasterException('Can\'t save product archive option');
            }
        }

        return $model;
    }

    /**
     * get options by cart id
     *
     * @param int $cartId cart id
     * @return array
     */
    public function getByCartId($cartId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_cart_session_options')->where($where);

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);

    }

    /**
     * Get data options by unique keys
     *
     * @param string $cartItemKey md5 key cartId, productId http_build_query(all cart options)
     * @param string $cartItemOptionKey md5 key cartId, productId, option id, option selection id
     * @return null
     */
    public function getByUniqueKeys($cartItemKey, $cartItemOptionKey)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_item_key = ?', $cartItemKey);
        $where .= ' AND '. $this->getDbTable()->getAdapter()->quoteInto('cart_item_option_key = ?', $cartItemOptionKey);
        return $this->_findWhere($where);
    }

    /**
     * @param string $cartItemKeys cart item key
     * @param string $cartId cart id
     * @return int
     */
    public function deleteNotUsedProductOptions($cartItemKeys, $cartId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_item_key NOT IN (?)', $cartItemKeys);
        $where .= ' AND '. $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);

        return $this->getDbTable()->getAdapter()->delete('shopping_cart_session_options', $where);
    }

    /**
     * Delete by cart session content id
     *
     * @param int $cartSessionContentId cart session content id
     * @return bool
     */
    public function deleteByCartSessionContentId($cartSessionContentId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_content_id = ?', $cartSessionContentId);

        return (bool)$this->getDbTable()->delete($where);
    }


}
