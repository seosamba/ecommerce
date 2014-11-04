<?php
/**
 * PickupLocation.php
 *
 * @method Store_Mapper_PickupLocationMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_PickupLocationMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_PickupLocation';

    protected $_dbTable = 'Store_DbTable_PickupLocation';

    protected static $_lastQueryResultCount = false;

    public function lastQueryResultCount($flag)
    {
        self::$_lastQueryResultCount = (bool)$flag;
        return $this;
    }

    /**
     * Save pickup locations data
     *
     * @param Store_Model_PickupLocation $model
     * @return bool|mixed
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }
        $data = array(
            'address1' => $model->getAddress1(),
            'address2' => $model->getAddress2(),
            'zip' => $model->getZip(),
            'country' => $model->getCountry(),
            'city' => $model->getCity(),
            'working_hours' => $model->getWorkingHours(),
            'phone' => $model->getPhone(),
            'location_category_id' => $model->getLocationCategoryId(),
            'name' => $model->getName(),
            'lat' => $model->getLat(),
            'lng' => $model->getLng(),
            'weight' => $model->getWeight()
        );
        if ($model->getId() === null) {
            $result = $this->getDbTable()->insert($data);
            $model->setId($result);
        } else {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
            $result = (bool)$this->getDbTable()->update($data, $where);
        }
        return $result;
    }

    /**
     * Get pickup locations data
     *
     * @param int $categoryId (category(zone id) of the pickup locations)
     * @param string $order OPTIONAL An SQL ORDER clause.
     * @param int $limit OPTIONAL An SQL LIMIT count.
     * @param int $offset OPTIONAL An SQL LIMIT offset.
     * @return array
     */
    public function fetchAll($categoryId = null, $order = null, $limit = null, $offset = null)
    {
        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)
            ->setIntegrityCheck(false)
            ->from(array('shopping_pickup_location'));
        if (!empty($order)) {
            $select->order($order);
        }

        if ($categoryId) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('location_category_id = ?', $categoryId);
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
     * Delete single pickup location
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
}