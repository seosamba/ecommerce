<?php

/**
 * CompanyProductsMapper.php
 *
 * @method Store_Mapper_CompanyProductsMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_CompanyProductsMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_CompanyProducts';

    protected $_dbTable = 'Store_DbTable_CompanyProducts';

    public function save($model)
    {

    }

    /**
     * Process companies product data
     *
     * @param int $productId product id
     * @param array $companyIds company id
     *
     */
    public function processData($productId, array $companyIds)
    {
        foreach ($companyIds as $companyId) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
            $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('company_id = ?', $companyId);
            $select = $this->getDbTable()->getAdapter()->select()->from('shopping_company_products')->where($where);
            $recordExits = $this->getDbTable()->getAdapter()->fetchRow($select);
            if (empty($recordExits)) {
                $data = array('product_id' => $productId, 'company_id' => $companyId);
                $this->getDbTable()->getAdapter()->insert('shopping_company_products', $data);
            }
        }
    }

    /**
     * Get all connected companies by product ids
     *
     * @param array $productIds product ids
     * @return array
     */
    public function getByProductIds(array $productIds)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id IN (?)', $productIds);
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_company_products',
            array('product_id'))->where($where);

        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }

    /**
     * Get product ids grouped by supplier id
     *
     * @param array $productIds product ids
     * @return array
     */
    public function getGroupedBySupplierData(array $productIds)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id IN (?)', $productIds);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('scp' => 'shopping_company_products'), array())
            ->join(array('scs' => 'shopping_company_suppliers'), 'scs.company_id=scp.company_id',
            array('supplier_id', new Zend_Db_Expr('GROUP_CONCAT(scp.product_id) as productsIds')))
            ->group('scs.supplier_id')
            ->where($where);

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Delete by product id
     *
     * @param int $productId product id
     * @return int
     */
    public function deleteByProductId($productId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id  = ?', $productId);

        return $this->getDbTable()->getAdapter()->delete('shopping_company_products', $where);
    }

    /**
     * Fetch companies data
     *
     * @param string $where mysql query where
     * @param array $order mysql query order
     * @param string $groupBy group by field
     * @return array
     */
    public function fetchAllData($where = '', $order = array(), $groupBy = '')
    {
        $entries = array();
        $select = $this->getDbTable()->getAdapter()->select()->from(array('ssp' => 'shopping_company_products'));
        if (!empty($where)) {
            $select->where($where);
        }
        if (!empty($order)) {
            $select->order($order);
        }
        if (!empty($groupBy)) {
            $select->group($groupBy);
        }
        $resultSet = $this->getDbTable()->getAdapter()->fetchAll($select);
        if (empty($resultSet)) {
            return array();
        }
        foreach ($resultSet as $row) {
            $entries[] = new $this->_model($row);
        }

        return $entries;

    }

}
