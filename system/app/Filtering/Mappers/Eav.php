<?php

/**
 * Filtering_Mappers_Eav
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Filtering_Mappers_Eav
{

    /**
     * @var string Name of tables containing attributes
     */
    protected $_attributesTable = 'shopping_filtering_attributes';

    /**
     * @var string Name of table containing values
     */
    protected $_valuesTable = 'shopping_filtering_values';

    /**
     * @var string Name of table containing relation records from tags to attributes
     */
    protected $_tagsRelationTable = 'shopping_filtering_tags_has_attributes';

    /**
     * @var Filtering_Mappers_Eav
     */
    protected static $_instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return Filtering_Mappers_Eav
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Filtering_Mappers_Eav();
        }
        return self::$_instance;
    }

    /**
     * Fetch attributes for entity
     * @param      $entityId
     * @return array List of attributes
     */
    public function getAttributes($productId = null)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $select = $dbAdapter->select()
            ->from(array('eav' => $this->_valuesTable))
            ->join(
                array('attr' => $this->_attributesTable),
                'attr.id = eav.attribute_id',
                array('attr.name', 'attr.label')
            );

        if ($productId !== null) {
            $productId = intval($productId);
            $select->where('eav.product_id = ?', $productId);
        }

        return $dbAdapter->fetchAll($select);
    }

    /**
     * Save entity-attributev-value container into database
     * @param $productId     Entity ID
     * @param $attributeId   Attribute ID
     * @param $value         Value
     * @return array Saved EAV container data
     * @throws Exceptions_SeotoasterException
     */
    public function saveEavContainer($productId, $attributeId, $value)
    {
        $attributeId = intval($attributeId);
        $productId = intval($productId);
        $value = (string)$value;

        $dbTable = new Filtering_DbTables_Eav();

        $data = array(
            'product_id'   => $productId,
            'attribute_id' => $attributeId,
            'value'        => $value
        );

        $row = $dbTable->find($productId, $attributeId);
        if ($row->count()) {
            $row = $row->current();
        } else {
            $row = $dbTable->createRow();
        }
        $row->setFromArray($data)
            ->save();
        return $row->toArray();
    }

    public function findFiltersByTags($tags)
    {
        if (!is_array($tags)) {
            $tags = (array)$tags;
        }
        $filters = array();

        if (!empty($tags)) {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $select = $dbAdapter->select()->from(
                array('v' => $this->_valuesTable),
                array('v.attribute_id', 'v.value')
            )
                ->join(array('a' => $this->_attributesTable), 'a.id = v.attribute_id', array('a.name', 'a.label'))
                ->join(
                    array('tha' => $this->_tagsRelationTable),
                    'tha.attribute_id = v.attribute_id',
                    null
                )
                ->where('tha.tag_id IN (?)', $tags);

            $data = $dbAdapter->fetchAll($select);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $id = $item['attribute_id'];
                    if (!array_key_exists($id, $filters)) {
                        $filters[$id] = $item;
                    }
                    if (!is_array($filters[$id]['value'])) {
                        $filters[$id]['value'] = (array)$filters[$id]['value'];
                    } else {
                        array_push($filters[$id]['value'], $item['value']);
                    }

                }
            }
        }
        return $filters;
    }

    public function findProductIdsByAttributes($attributes, $strictMatch = true)
    {
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $select = $dbAdapter->select()
            ->from(array('eav' => $this->_valuesTable), 'eav.product_id')
            ->join(
                array('attr' => $this->_attributesTable),
                'attr.id = eav.attribute_id',
                null
            );

        foreach ($attributes as $name => $value) {
            $nameWhere = $dbAdapter->quoteInto('attr.name = ?', $name);
            $valueWhere = $dbAdapter->quoteInto('eav.value IN (?)', $value);
            $select->orWhere($nameWhere.' AND '.$valueWhere);
        }

        $select->group('eav.product_id');

        if ($strictMatch) {
            $select->having('COUNT(eav.product_id) = ?', sizeof($attributes));
        }

        return $dbAdapter->fetchCol($select);
    }
}
