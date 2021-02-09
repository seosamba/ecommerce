<?php

/**
 * Class Store_Mapper_PartialNotificationLogMapper
 */
class Store_Mapper_PartialNotificationLogMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_PartialNotificationLog';

    protected $_dbTable = 'Store_DbTable_PartialNotificationLog';

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
            'cart_id' => $model->getCartId(),
            'notified_at' => $model->getNotifiedAt()
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
     * find by card id
     *
     * @param int $cartId cart id
     * @return null
     * @throws Exception
     */
    public function findByCartId($cartId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        return $this->_findWhere($where);
    }

    /**
     * @param $model
     * @return bool
     * @throws Exception
     */
    public function delete($model)
    {
        if ($model instanceof $this->_model) {
            $id = $model->getId();
        } elseif (is_numeric($model)) {
            $id = intval($model);
        }

        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);

        return (bool)$this->getDbTable()->delete($where);
    }

}
