<?php

/**
 * CompaniesMapper.php
 *
 * @method Store_Mapper_CompaniesMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_CompaniesMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_Companies';

    protected $_dbTable = 'Store_DbTable_Companies';

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
            'company_name' => $model->getCompanyName()
        );

        $companyExists = $this->getByCompanyName($data['company_name']);
        if (!$companyExists instanceof Store_Model_Companies) {
            $companyId = $this->getDbTable()->insert($data);
            $model->setId($companyId);

        } else {
            $where = $this->getDbTable()->getAdapter()->quoteInto("company_name = ?", $data['company_name']);
            $this->getDbTable()->update($data, $where);
        }

        return $model;
    }

    /**
     * Get by company name
     *
     * @param string $companyName company name
     * @return null
     */
    public function getByCompanyName($companyName)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('company_name = ?', $companyName);

        return $this->_findWhere($where);
    }

    /**
     * Add su[pliers to company if not exists
     *
     * @param int $companyId company id
     * @param array $supplierIds supplier ids (user ids)
     * @throws Zend_Db_Adapter_Exception
     */
    public function assignSuppliers($companyId, array $supplierIds)
    {
        foreach ($supplierIds as $supplierId) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('company_id = ?', $companyId);
            $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('supplier_id = ?', $supplierId);
            $select = $this->getDbTable()->getAdapter()->select()->from('shopping_company_suppliers')->where($where);
            $recordExits = $this->getDbTable()->getAdapter()->fetchRow($select);
            if (empty($recordExits)) {
                $data = array('company_id' => $companyId, 'supplier_id' => $supplierId);
                $this->getDbTable()->getAdapter()->insert('shopping_company_suppliers', $data);
            }
        }
    }

    /**
     * Get company id by supplier id
     *
     * @param int $supplierId supplier id
     * @return null
     */
    public function getBySupplierId($supplierId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('supplier_id = ?', $supplierId);

        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_company_suppliers')->where($where);

        return $this->getDbTable()->getAdapter()->fetchRow($select);
    }

    /**
     * Delete supplier from the company
     *
     * @param int $supplierId supplier id
     * @return int
     */
    public function deleteBySupplierId($supplierId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('supplier_id = ?', $supplierId);

        return $this->getDbTable()->getAdapter()->delete('shopping_company_suppliers', $where);
    }

    /**
     * Delete by supplier ids
     *
     * @param array $supplierIds supplier ids
     * @return int
     */
    public function deleteBySupplierIds(array $supplierIds)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('supplier_id IN (?)', $supplierIds);

        return $this->getDbTable()->getAdapter()->delete('shopping_company_suppliers', $where);
    }
}
