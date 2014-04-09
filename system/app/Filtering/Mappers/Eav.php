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

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;

    private function __construct()
    {
        $this->_dbAdapter = Zend_Db_Table::getDefaultAdapter();
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
            ->from(
                array('attr' => $this->_attributesTable),
                array('attr.name', 'attr.label')
            );

        if ($productId !== null) {
            $productId = intval($productId);
            $select->from(array('eav' => $this->_valuesTable))
                ->where('attr.id = eav.attribute_id')
                ->where('eav.product_id = ?', $productId);
        }

        return $dbAdapter->fetchAll($select);
    }

    /**
     * Get all existing attributes names
     * @return array
     */
    public function getAttributeNames()
    {
        $select = $this->_dbAdapter->select()
            ->from($this->_attributesTable, array('name'))
            ->order('name ASC');

        return $this->_dbAdapter->fetchCol($select);
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

    /**
     * Returns array of product filters relevant to specified tags
     * @param      $tags    List of tags
     * @param null $exclude List of filters to exclude
     * @return array
     */
    public function findListFiltersByTags($tags, $exclude = null)
    {
        if (!is_array($tags)) {
            $tags = (array)$tags;
        }
        $filters = array();

        if (!empty($tags)) {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $select = $dbAdapter->select()->from(
                array('eav' => $this->_valuesTable),
                array('eav.attribute_id', 'eav.value', 'count' => 'COUNT(eav.product_id)')
            )
                ->join(
                    array('tha' => $this->_tagsRelationTable),
                    'tha.attribute_id = eav.attribute_id',
                    null
                )
                ->join(array('a' => $this->_attributesTable), 'a.id = eav.attribute_id', array('a.name', 'a.label'))
                ->where('tha.tag_id IN (?)', $tags)
                ->where('a.name NOT IN (?)', Filtering_Tools::$_rangeFilters)
                ->where(
                    'eav.product_id IN (?)',
                    $dbAdapter->fetchCol(
                        $dbAdapter->select()
                            ->from(array('p' => 'shopping_product'), array('DISTINCT(p.id)'))
                            ->join(array('pht' => 'shopping_product_has_tag'), 'pht.product_id = p.id', null)
                            ->where('pht.tag_ID IN (?)', $tags)
                    )
                )
                ->group(array('eav.attribute_id', 'eav.value'))
                ->order('a.label ASC');

            $data = $dbAdapter->fetchAll($select);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $id = $item['attribute_id'];
                    if (!array_key_exists($id, $filters)) {
                        $filters[$id] = array(
                            'attribute_id' => $id,
                            'values'       => array(),
                            'name'         => $item['name'],
                            'label'        => $item['label']
                        );
                    }
                    $filters[$id]['values'][$item['value']] = $item['count'];
                }
            }
        }
        return $filters;
    }

    /**
     * Returns array of range product filters relevant to specified tags
     * @param      $tags    List of tags
     * @param null $exclude List of filters to exclude
     * @return array
     */
    public function findRangeFiltersByTags($tags, $exclude = null)
    {
        if (!is_array($tags)) {
            $tags = (array)$tags;
        }
        $filters = array();

        if (!empty($tags)) {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $select = $dbAdapter->select()->from(
                array('eav' => $this->_valuesTable),
                array('eav.attribute_id', 'min' => 'MIN(eav.value)', 'max' => 'MAX(eav.value)')
            )
                ->join(
                    array('tha' => $this->_tagsRelationTable),
                    'tha.attribute_id = eav.attribute_id',
                    null
                )
                ->join(array('a' => $this->_attributesTable), 'a.id = eav.attribute_id', array('a.name', 'a.label'))
                ->where('tha.tag_id IN (?)', $tags)
                ->where('a.name IN (?)', Filtering_Tools::$_rangeFilters)
                ->where(
                    'eav.product_id IN (?)',
                    $dbAdapter->fetchCol(
                        $dbAdapter->select()
                            ->from(array('p' => 'shopping_product'), array('DISTINCT(p.id)'))
                            ->join(array('pht' => 'shopping_product_has_tag'), 'pht.product_id = p.id', null)
                            ->where('pht.tag_ID IN (?)', $tags)
                    )
                )
                ->group(array('eav.attribute_id'));

            return $dbAdapter->fetchAssoc($select);
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
            $select->orWhere($nameWhere . ' AND ' . $valueWhere);
        }

        $select->group('eav.product_id');

        if ($strictMatch) {
            $select->having('COUNT(eav.product_id) = ?', sizeof($attributes));
        }

        return $dbAdapter->fetchCol($select);
    }

    public function getPriceRange($productTags)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();

        $select = $dbAdapter->select()
            ->from(
                array(
                    'p' => 'shopping_product'
                ),
                array(
                    'min' => 'FLOOR(MIN(p.price))',
                    'max' => 'CEIL(MAX(p.price))'
                )
            )
            ->from(array('t' => 'shopping_product_has_tag'), null)
            ->where('p.id = t.product_id')
            ->where('t.tag_id IN (?)', $productTags);

        $result = $dbAdapter->fetchRow($select);

        return $result;
    }

    public function getBrands($productTags)
    {
        $select = $this->_dbAdapter->select()
            ->from(
                array('p' => 'shopping_product'),
                array('count' => 'COUNT(p.id)')
            )
            ->from(
                array('b' => 'shopping_brands'),
                array('b.id', 'b.name')
            )
            ->from(
                array('t' => 'shopping_product_has_tag'),
                null
            )
            ->where('t.product_id = p.id')
            ->where('b.id = p.brand_id')
            ->where('t.tag_id IN (?)', $productTags)
            ->group('b.id');

        $result = $this->_dbAdapter->fetchAll($select);

        return $result;
    }
}
