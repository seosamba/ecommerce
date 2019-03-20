<?php

/**
 * Class Store_Mapper_WishedProductsMapper
 */
class Store_Mapper_WishedProductsMapper extends Application_Model_Mappers_Abstract {

    protected $_model   = 'Store_Model_WishedProducts';

    protected $_dbTable = 'Store_DbTable_WishedProducts';

    /**
     * @param $model
     * @return mixed
     * @throws Exceptions_SeotoasterException
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            throw new Exceptions_SeotoasterException('Given parameter should be ' . $this->_model . ' instance');
        }

        $data = array(
            'user_id'    => $model->getUserId(),
            'product_id' => $model->getProductId(),
            'added_date' => $model->getAddedDate(),
        );

        if ($model->getId()) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
            return $this->getDbTable()->update($data, $where);
        }

        $result = $this->getDbTable()->insert($data);
        $model->setId($result);

        return $result;
    }

    /**
     * @param $model
     * @return bool
     * @throws Exception
     */
    public function delete($model){
        if ($model instanceof $this->_model){
            $id = $model->getId();
        } elseif (is_numeric($model)) {
            $id = intval($model);
        }

        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
        return (bool) $this->getDbTable()->delete($where);
    }

    /**
     * @param $userId
     * @param $productId
     * @return |null
     * @throws Exception
     */
    public function findByUserIdProductId($userId, $productId){
        $where = $this->getDbTable()->getAdapter()->quoteInto('user_id = ?', $userId);
        $where .= ' AND '.$this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        return $this->_findWhere($where);
    }

    /**
     * @param $userId
     * @return mixed
     * @throws Exception
     */
    public function findProductsByUserId($userId){
        $where = $this->getDbTable()->getAdapter()->quoteInto('wlp.user_id = ?', $userId);

        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('wlp' => 'shopping_wishlist_wished_products'), array(
                'productId' => 'wlp.product_id',
                'img' => 'p.photo',
                'alt' => 'p.name',
                'name' => 'p.name',
                'sku' => 'p.sku',
                'page' => 'page.url'

            ))
            ->joinLeft(array('p' => 'shopping_product'), 'wlp.product_id = p.id', array())
            ->joinLeft(array('page' => 'page'), 'p.page_id = page.id', array())
            ->where($where);
        $result = $this->getDbTable()->getAdapter()->fetchAll($select);

        return $result;
    }

    /**
     * Find last added under Wishlist products
     *
     * @param $productId
     * @return mixed
     * @throws Exception
     */
    public function findLastUserAdded($productId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        $select = $this->getDbTable()->getAdapter()->select()->distinct()->from(array('wp' => 'shopping_wishlist_wished_products'), array(
            'u.full_name'
        ))
         ->joinLeft(array('u' => 'user'), 'wp.user_id = u.id', array())
         ->where($where)->order('wp.added_date DESC')->limit(1);

        return $this->getDbTable()->getAdapter()->fetchRow($select);
    }


}
