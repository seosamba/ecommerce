<?php

/**
 * Filtering_Mappers_Filter
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Filtering_Mappers_Filter
{
    const TABLE_NAME = 'shopping_filtering_widget_settings';

    /**
     * @var Filtering_Mappers_Filter
     */
    protected static $_instance;

    private function __construct()
    {
        $this->_dbTable = new Zend_Db_Table(self::TABLE_NAME);
    }

    private function __clone()
    {
    }

    /**
     * @return Filtering_Mappers_Filter
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (self::$_instance === null) {
            self::$_instance = new Filtering_Mappers_Filter();
        }
        return self::$_instance;
    }

    public function saveSettings($filterId, $settings)
    {
        $row = $this->_dbTable->fetchRow(array('filter_id = ?' => $filterId));
        if ($row === null) {
            $row = $this->_dbTable->createRow();
        }
        $row->setFromArray(
            array(
                'filter_id' => $filterId,
                'settings'  => json_encode($settings)
            )
        );

        return $row->save();
    }

    public function getSettings($filterId)
    {
        $row = $this->_dbTable->fetchRow(array('filter_id = ?' => $filterId));
        if ($row !== null) {
            $data = $row->toArray();
        }
        if (!empty($data['settings'])) {
            return json_decode($data['settings'], true);
        }

        return array();
    }
}
