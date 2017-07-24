<?php

class Store_Mapper_DigitalProductMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_DigitalProduct';

    protected $_dbTable = 'Store_DbTable_DigitalProduct';

    protected static $_lastQueryResultCount = false;

    /**
     * Accepted statuses
     *
     * @var array
     */
    protected static $_acceptedStatuses = array(
        Models_Model_CartSession::CART_STATUS_COMPLETED,
        Models_Model_CartSession::CART_STATUS_DELIVERED
    );

    /**
     * @param bool $flag
     * @return $this
     */
    public function lastQueryResultCount($flag)
    {
        self::$_lastQueryResultCount = (bool)$flag;

        return $this;
    }

    /**
     * Save digital products
     *
     * @param Store_Model_DigitalProduct $model
     * @return bool|mixed
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }
        $data = array(
            'file_stored_name' => $model->getFileStoredName(),
            'file_hash' => $model->getFileHash(),
            'original_file_name' => $model->getOriginalFileName(),
            'product_id' => $model->getProductId(),
            'uploaded_at' => $model->getUploadedAt(),
            'start_date' => $model->getStartDate(),
            'end_date' => $model->getEndDate(),
            'download_limit' => $model->getDownloadLimit(),
            'product_type' => $model->getProductType(),
            'ip_address' => $model->getIpAddress(),
            'display_file_name' => $model->getDisplayFileName()
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
     * Get digital products data
     *
     * @param int $productId product id (shopping_product table)
     * @param string $order OPTIONAL An SQL ORDER clause.
     * @param int $limit OPTIONAL An SQL LIMIT count.
     * @param int $offset OPTIONAL An SQL LIMIT offset.
     * @return array
     */
    public function fetchAll($productId = null, $order = null, $limit = null, $offset = null)
    {
        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)
            ->setIntegrityCheck(false)
            ->from(array('shopping_product_digital_goods'));
        if (!empty($order)) {
            $select->order($order);
        }

        if ($productId) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
            $select->where($where);
        }

        if (self::$_lastQueryResultCount) {
            $data = $this->getDbTable()->fetchAll($select)->toArray();

            return array(
                'totalRecords' => sizeof($data),
                'data' => array_slice($data, $offset, $limit),
                'offset' => $offset,
                'limit' => $limit
            );

        }
        $select->limit($limit, $offset);

        return $this->getDbTable()->fetchAll($select)->toArray();
    }

    /**
     * Delete digital product
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        $result = array();
        $rowset = $this->getDbTable()->find($id);
        foreach ($rowset as $row) {
            $result[$row->id] = $row->delete();
        }

        return $result;
    }

    /**
     * Get file by hash name
     *
     * @param string $hash File hash
     * @return array|null
     * @throws Exception
     */
    public function getByHash($hash)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto("file_hash = ?", $hash);

        $result = $this->getDbTable()->fetchRow($where);

        return $result->toArray();
    }

    /**
     * Get digital product from cart
     *
     * @param int $cartId cart id
     * @param string $fileHash digital product hash
     * @param int $productId product id
     * @param int $userId user id
     * @param string $productType product type (downloadable,viewable)
     * @throws Exception
     */
    public function findDigitalProduct(
        $cartId,
        $productId,
        $fileHash,
        $userId,
        $productType = Store_Model_DigitalProduct::PRODUCT_TYPE_DOWNLOADABLE
    ) {

        $where = $this->getDbTable()->getAdapter()->quoteInto('scs.id = ?', $cartId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('shcc.is_digital = ?', '1');
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('shcc.product_id = ?', $productId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('scs.status IN (?)', self::$_acceptedStatuses);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('spdg.file_hash = ?', $fileHash);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('u.id = ?', $userId);
        $where .= ' AND ' . new Zend_Db_Expr('spdg.start_date <= scs.created_at');
        $where .= ' AND ' . new Zend_Db_Expr('spdg.end_date >= scs.created_at');
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('spdg.download_limit > ?', 0);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('spdg.product_type = ?', $productType);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('scs' => 'shopping_cart_session'), array())
            ->joinLeft(array('shcc' => 'shopping_cart_session_content'), 'scs.id=shcc.cart_id', array())
            ->joinLeft(array('spdg' => 'shopping_product_digital_goods'), 'shcc.product_id=spdg.product_id')
            ->joinLeft(array('u' => 'user'), 'u.id=scs.user_id', array())
            ->where($where);

        return $this->getDbTable()->getAdapter()->fetchRow($select);

    }


    /**
     * Get all acceptable for user digital products
     *
     * @param int $userId user id
     * @return mixed
     * @throws Exception
     */
    public function findDigitalProductsByUserId($userId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('u.id = ?', $userId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('shcc.is_digital = ?', '1');
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('scs.status IN (?)', self::$_acceptedStatuses);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('spdg.download_limit > ?', 0);
        $where .= ' AND ' . new Zend_Db_Expr('spdg.start_date <= scs.created_at');
        $where .= ' AND ' . new Zend_Db_Expr('spdg.end_date >= scs.created_at');
        $select = $this->getDbTable()->getAdapter()->select()->from(array('scs' => 'shopping_cart_session'),
            array('orderDate' => 'scs.created_at', 'cartId' => 'scs.id'))
            ->joinLeft(array('shcc' => 'shopping_cart_session_content'), 'scs.id=shcc.cart_id', array())
            ->joinLeft(array('spdg' => 'shopping_product_digital_goods'), 'shcc.product_id=spdg.product_id')
            ->joinLeft(array('u' => 'user'), 'u.id=scs.user_id', array())
            ->where($where)->group('spdg.file_hash');
        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }

    /**
     * Check if product was sold
     *
     * @param int $productId product id
     * @return mixed
     * @throws Exception
     */
    public function checkDigitalProductInCart($productId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('shcc.is_digital = ?', '1');
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('shcc.product_id = ?', $productId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('scs.status IN (?)', self::$_acceptedStatuses);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('scs' => 'shopping_cart_session'), array())
            ->joinLeft(array('shcc' => 'shopping_cart_session_content'), 'scs.id=shcc.cart_id', array())
            ->joinLeft(array('spdg' => 'shopping_product_digital_goods'), 'shcc.product_id=spdg.product_id')
            ->where($where);
        return $this->getDbTable()->getAdapter()->fetchRow($select);
    }


    /**
     * Decrease download limit
     *
     * @param int $fileId stored file id
     */
    public function decreaseDownloadLimit($fileId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $fileId);
        $data = array('download_limit' => new Zend_Db_Expr('download_limit - 1'));

        return $this->getDbTable()->update($data, $where);

    }
}