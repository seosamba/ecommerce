<?php


class Models_Mapper_DraggableMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Models_Model_Draggable';

    protected $_dbTable = 'Models_DbTable_Draggable';

    public function save($model) {
        if (!$model instanceof $this->_model){
            $model = new $this->_model($model);
        }

        if ($model->getId()){
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
            $this->getDbTable()->update($model->toArray(), $where);
        } else {
            try{
                $id = $this->getDbTable()->insert($model->toArray());
            } catch (Exception $e){
                error_log($e->getMessage());
                return null;
            }
            if ($id){
                $model->setId($id);
            }
        }

        return $model;
    }



}