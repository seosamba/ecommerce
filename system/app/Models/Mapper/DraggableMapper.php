<?php


class Models_Mapper_DraggableMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Models_Model_Draggable';

    protected $_dbTable = 'Models_DbTable_Draggable';

    public function save($model)
    {
    }

    public function find($id)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        return $row;
    }


}