<?php


class Models_Mapper_DraggableMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Models_Model_Draggable';

    protected $_dbTable = 'Models_DbTable_Draggable';

    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            throw new Exceptions_SeotoasterPluginException('Wrong model type given.');
        }

        $data = array(
            'id' => $model->getId(),
            'data' => $model->getData()
        );

        $recordExists = $this->find($data['id']);

        if ($recordExists instanceof Models_Model_Draggable) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
            $this->getDbTable()->update($data, $where);
        } else {
            $this->getDbTable()->insert($data);

        }

        return $model;
    }


}