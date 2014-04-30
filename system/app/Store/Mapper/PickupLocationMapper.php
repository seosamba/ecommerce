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
            'lat'  => $model->getLat(),
            'lng'  => $model->getLng()
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

    public function fetchByCategory($categoryId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('location_category_id = ?', $categoryId);
        return $this->fetchAll($where);
    }

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
