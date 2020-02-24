<?php

/**
 * Class Store_Mapper_NotifiedProductsMapper
 */
class Store_Mapper_NotifiedProductsMapper extends Application_Model_Mappers_Abstract {

    protected $_model   = 'Store_Model_NotifiedProductsModel';

    protected $_dbTable = 'Store_DbTable_NotifiedProductsDbTable';

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
            'user_id'           => $model->getUserId(),
            'product_id'        => $model->getProductId(),
            'added_date'        => $model->getAddedDate(),
            'send_notification' => $model->getSendNotification()
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
     * @param $productId
     * @return mixed
     * @throws Exception
     */
    public function findCustomersByProductId($productId){
        $where = $this->getDbTable()->getAdapter()->quoteInto('nnp.product_id = ?', $productId);

        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('nnp' => 'shopping_notification_notified_products'), array(
                'nnp.id',
                'productId'        => 'nnp.product_id',
                'userEmail'        => 'u.email',
                'userFullName'     => 'u.full_name',
                'addedNotifyDate'  => 'nnp.added_date',
                'productName'      => 'sp.name',
                'shortDescription' => 'sp.short_description',
                'productUrl'       => 'p.url'
            ))
            ->joinLeft(array('sp' => 'shopping_product'), 'nnp.product_id = sp.id', array())
            ->joinLeft(array('p' => 'page'), 'sp.page_id = p.id', array())
            ->joinLeft(array('u' => 'user'), 'nnp.user_id = u.id', array())
            ->where($where);
        $result = $this->getDbTable()->getAdapter()->fetchAll($select);

        return $result;
    }

    /**
     * @param $userId
     * @return mixed
     * @throws Exception
     */
    public function findProductsByUserId($userId){
        $where = $this->getDbTable()->getAdapter()->quoteInto('nnp.user_id = ?', $userId);

        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('nnp' => 'shopping_notification_notified_products'), array(
                'productId' => 'nnp.product_id',
                'img' => 'p.photo',
                'alt' => 'p.name',
                'name' => 'p.name',
                'sku' => 'p.sku',
                'page' => 'page.url'

            ))
            ->joinLeft(array('p' => 'shopping_product'), 'nnp.product_id = p.id', array())
            ->joinLeft(array('page' => 'page'), 'p.page_id = page.id', array())
            ->where($where);
        $result = $this->getDbTable()->getAdapter()->fetchAll($select);

        return $result;
    }

}
