<?php
/**
 * PickupLocationCategory.php
 *
 * @method Store_Mapper_PickupLocationCategoryMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_PickupLocationCategoryMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_PickupLocationCategory';

    protected $_dbTable = 'Store_DbTable_PickupLocationCategory';


    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }
        $data = array(
            'name' => $model->getName(),
            'img'  => $model->getImg()
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
