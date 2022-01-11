<?php


class Models_Mapper_FilterPresetMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Models_Model_FilterPresetModel';

    protected $_dbTable = 'Models_DbTable_FilterPresetDbTable';

    /**
     * Save filter preset model to DB
     * @param $model Models_Model_FilterPresetModel
     * @return Models_Model_FilterPresetModel
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'creator_id' => $model->getCreatorId(),
            'filter_preset_name' => $model->getFilterPresetName(),
            'filter_preset_data' => $model->getFilterPresetData(),
            'is_default' => $model->getIsDefault(),
            'access' => $model->getAccess()
        );

        $id = $model->getId();
        if (!empty($id)) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
            $this->getDbTable()->update($data, $where);
        } else {
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);
        }

        return $model;
    }

    /**
     * Find preset by id and creator id
     *
     * @param int $id preset id
     * @param int $creatorId system user id
     * @return null
     */
    public function findByIdRole($id, $creatorId = 0)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
        if (!empty($creatorId)) {
            $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('creator_id = ?', $creatorId);
        }

        return $this->_findWhere($where);
    }

    /**
     * Reset default preset state
     *
     * @param int $creatorId system user id
     * @return int
     * @throws Zend_Db_Adapter_Exception
     */
    public function resetDefaultByCreatorId($creatorId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('creator_id = ?', $creatorId);

        return $this->getDbTable()->getAdapter()->update('shopping_filter_preset', array('is_default' => '0'),
            $where);
    }

    /**
     * Get default preset by creator id
     *
     * @param int $creatorId system user id
     * @return null
     */
    public function getDefaultPreset($creatorId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('creator_id = ?', $creatorId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('is_default = ?', '1');

        return $this->_findWhere($where);
    }

    /**
     * @param $creatorId
     * @param $access
     * @return mixed|null
     * @throws Exception
     */
    public function getDefaultAndAllAccessPreset($creatorId, $access = 'all')
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('creator_id = ?', $creatorId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('access = ?', $access);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('is_default = ?', '1');

        return $this->_findWhere($where);
    }

    /**
     * Find preset by name using preset type
     *
     * @param string $presetName filter preset name
     * @return null
     */
    public function getByName($presetName)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('filter_preset_name = ?', $presetName);

        return $this->_findWhere($where);
    }

    /**
     * Delete filter preset record
     *
     * @param int $id filter preset id
     * @return mixed
     * @throws Exception
     */
    public function delete($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);

        return $this->getDbTable()->getAdapter()->delete('shopping_filter_preset', $where);

    }

}
