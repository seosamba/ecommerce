<?php

/**
 * Filtering_Mappers_Eav
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Filtering_Mappers_Eav
{

    const DOUBLE_QUOTE_NEW_FORMAT = '”';
    const SINGLE_QUOTE_NEW_FORMAT = '’';

    /**
     * Unique product filter attribute value separator. Used for multiple values to attribute.
     */
    const ATTRIBUTE_VALUE_SEPARATOR = ' | ';

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
     * @param int $productId     Entity ID
     * @param int $attributeId   Attribute ID
     * @param string $attributeValue Attribute value
     * @return array Saved EAV container data
     * @throws Exceptions_SeotoasterException
     */
    public function saveEavContainer($productId, $attributeId, $attributeValue)
    {
        $attributeId = intval($attributeId);
        $productId = intval($productId);
        $attributeValue = (string)$attributeValue;

        $attributeValue = str_replace('"', self::DOUBLE_QUOTE_NEW_FORMAT, $attributeValue);
        $attributeValue = str_replace('\'', self::SINGLE_QUOTE_NEW_FORMAT, $attributeValue);
        $attributeValue = explode(self::ATTRIBUTE_VALUE_SEPARATOR, $attributeValue);
        $attributeValue = array_unique(array_filter($attributeValue));

        $dbTable = new Filtering_DbTables_Eav();

        $savedAttributes = $dbTable->find($productId, $attributeId);

        if($savedAttributes->count()) {
            $savedAttributes = $savedAttributes->toArray();

            if(!empty($savedAttributes)) {
                foreach ($savedAttributes as $attribute) {
                    if(!in_array($attribute['value'], $attributeValue)) {
                        $this->deleteById($attribute['id']);
                    }
                }
            }
        }

        $attributesData = array();
        foreach ($attributeValue as $value) {
            $row = $this->findFilteringValuesByParams($productId, $attributeId, $value);
            if (empty($row) ) {
                $row = $dbTable->createRow(array(
                    'product_id'   => $productId,
                    'attribute_id' => $attributeId,
                    'value'        => $value
                ));
                $row->save();

                $row = $this->findFilteringValuesByParams($productId, $attributeId, $value);
            }

            $attributesData[] = $row;
        }

        return $attributesData;
    }

    /**
     * Returns array of product filters relevant to specified tags
     * @param array $tags    List of tags
     * @return array
     */
    public function findListFiltersByTags($tags, array $prodIds = array())
    {
        if (!is_array($tags)) {
            $tags = (array)$tags;
        }
        $filters = array();

        if (!empty($tags)) {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $productIds = $dbAdapter->fetchCol(
                $dbAdapter->select()
                    ->from(array('p' => 'shopping_product'), array('p.id'))
                    ->join(array('pht' => 'shopping_product_has_tag'), 'pht.product_id = p.id', null)
                    ->where('pht.tag_id IN (?)', $tags)
                    ->where('p.enabled = ?', '1')
            );
            $data = array();
            if(!empty($productIds)){
                if(!empty($prodIds)) {
                    $productIds = array_intersect($productIds, $prodIds);
                }

                $select = $dbAdapter->select()->from(
                    array('eav' => $this->_valuesTable), //shopping_filtering_values
                    array('eav.attribute_id', 'eav.value', 'count' => 'COUNT(DISTINCT(eav.product_id))')
                )
                    ->join(
                        array('tha' => $this->_tagsRelationTable), //shopping_filtering_tags_has_attributes
                        'tha.attribute_id = eav.attribute_id',
                        null
                    )
                    ->join(array('a' => $this->_attributesTable), 'a.id = eav.attribute_id', array('a.name', 'a.label')) //shopping_filtering_attributes
                    ->where('tha.tag_id IN (?)', $tags)
                    ->where('a.name NOT IN (?)', Filtering_Tools::$_rangeFilters)
                    ->where('eav.product_id IN (?)', $productIds)
                    ->group(array('eav.attribute_id', 'eav.value'))
                    ->order('a.label ASC');
                $data = $dbAdapter->fetchAll($select);
            }
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
     * @param array $tags    List of tags
     * @return array
     */
    public function findRangeFiltersByTags($tags)
    {
        if (!is_array($tags)) {
            $tags = (array)$tags;
        }
        $filters = array();

        if (!empty($tags)) {
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $select = $dbAdapter->select()->from(
                array('eav' => $this->_valuesTable),
                array(
                    'eav.attribute_id',
                    'min' => new Zend_Db_Expr('MIN( CAST(eav.value AS DECIMAL(8)) )'),
                    'max' => new Zend_Db_Expr('MAX( CAST(eav.value AS DECIMAL(8)) )')
                )
            )
                ->join(array('a' => $this->_attributesTable), 'a.id = eav.attribute_id', array('a.name', 'a.label'))
                ->where('a.name IN (?)', Filtering_Tools::$_rangeFilters)
                ->where(
                    'eav.product_id IN (?)',
                    $dbAdapter->select()
                        ->from(array('pht' => 'shopping_product_has_tag'), 'DISTINCT(pht.product_id)')
                        ->where('pht.tag_ID IN (?)', $tags)
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
        $counterAttributes = sizeof($attributes);
        $counterAttrValues = 0;
        foreach ($attributes as $name => $value) {
            $counterAttrValues += sizeof($value);

            $nameWhere = $dbAdapter->quoteInto('attr.name = ?', $name);
            if (isset($value['from']) && isset($value['to'])) {
                $valueWhere = $dbAdapter->quoteInto('(eav.value BETWEEN ? ', $value['from']);
                $valueWhere .= $dbAdapter->quoteInto(' AND ?)', $value['to']);
            } else {
                if (is_array($value)) {
                    $otherIndex = array_search(Widgets_Filter_Filter::FILTER_OTHERS, $value);
                    if ($otherIndex !== false) {
                        unset($value[$otherIndex]);
                        $data = Zend_Controller_Action_HelperBroker::getExistingHelper('cache')
                            ->load(md5(Widgets_Filter_Filter::CACHE_KEY_OTHERS_ARRAY . $name));
                        if ($data) {
                            $value = array_merge($value, $data);
                        }
                    }
                }
                if (empty($value)) {
                    continue;
                }
                $valueWhere = $dbAdapter->quoteInto('eav.value IN (?)', $value);
            }
            $select->orWhere($nameWhere . ' AND ' . $valueWhere);
        }

        $select->group('eav.product_id');

        if ($strictMatch) {
            $select->having('COUNT(eav.product_id) IN (?)', range($counterAttributes, $counterAttrValues));
        }

        $productsData = $dbAdapter->fetchCol($select);

        if($counterAttributes > 1 && !empty($productsData)) {
            $eavMapper = Filtering_Mappers_Eav::getInstance();

            $attributesExists = array();
            foreach ($productsData as $key => $productId) {
                $productAttributes = $eavMapper->getAttributes($productId);

                if(!empty($productAttributes)) {
                    foreach ($productAttributes as $attribute) {
                        if(in_array($attribute['value'], $attributes[$attribute['name']])) {
                            $attributesExists[$productId][$attribute['name']] = 1;
                        }
                    }
                }
            }

            if(!empty($attributesExists)) {
                foreach ($attributesExists as $key => $value) {
                    if(sizeof($value) < $counterAttributes) {
                        $prodKey = array_search($key, $productsData);
                        unset($productsData[$prodKey]);
                    }
                }
            }
        }

        return $productsData;
    }

    public function getPriceRange($productTags, array $productsIds = array())
    {
        $where = $this->_dbAdapter->quoteInto('p.enabled = ?', '1');
        $where .= ' AND ' . $this->_dbAdapter->quoteInto('t.tag_id IN (?)', $productTags);

        if(!empty($productsIds)) {
            $where .= ' AND ' . $this->_dbAdapter->quoteInto('p.id IN (?)', $productsIds);
        }

        $select = $this->_dbAdapter->select()
            ->from(
                array(
                    'p' => 'shopping_product'
                ),
                array(
                    'min' => 'MIN(p.price)',
                    'max' => 'MAX(p.price)'
                )
            )
            ->from(array('t' => 'shopping_product_has_tag'), null)
            ->where('p.id = t.product_id')
            ->where($where);

        $result = $this->_dbAdapter->fetchRow($select);

        return $result;
    }

    /**
     * @param $productTags
     * @param $additionalAttributeName
     * @return array
     */
    public function getPriceRangeForProduct($productTags, $additionalAttributeName)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();

        $where = $dbAdapter->quoteInto('pt.tag_id IN (?)', $productTags);
        $where .= ' AND ' . $dbAdapter->quoteInto('sfa.name = ?', $additionalAttributeName);

        $select = $dbAdapter->select()->from(array('sfv' => 'shopping_filtering_values'), array('sfv.value'))
            ->join(array('p' => 'shopping_product'), 'p.id = sfv.product_id', null)
            ->join(array('pt' => 'shopping_product_has_tag'), 'pt.product_id = sfv.product_id', null)
            ->join(array('sfa' => 'shopping_filtering_attributes'), 'sfa.id = sfv.attribute_id', null)
            ->where($where);

        $result = $dbAdapter->fetchCol($select);

        return $result;
    }

    /**
     * Returns array of brand => count pairs for given tags
     * @param $productTags array Product tags to filer with
     * @param $filterByNames null|array List of allowed brand names
     * @return array
     */
    public function getBrands($productTags, array $productsIds = array())
    {
        $where = $this->_dbAdapter->quoteInto('pt.tag_id IN (?)', $productTags);
        $where .= ' AND ' . $this->_dbAdapter->quoteInto('p.enabled = ?', '1');

        if(!empty($productsIds)) {
            $where .= ' AND ' . $this->_dbAdapter->quoteInto('p.id IN (?)', $productsIds);
        }

        $select = $this->_dbAdapter->select()->from(array('p' => 'shopping_product'),
                array(
                    'p.id',
                    'b.name'
                ))
                ->joinInner(array('b' => 'shopping_brands'), 'b.id = p.brand_id', array())
                ->joinInner(array('t' => 'shopping_tags'), '', array())
                ->joinInner(array('pt' => 'shopping_product_has_tag'), 'pt.tag_id = t.id AND pt.product_id = p.id', array())
                ->where($where)
                ->group('p.id');

        $result = $this->_dbAdapter->fetchPairs($select);

        return $result;
    }


    /**
     * Return attribute data by attribute name
     * @param $attrName
     * @param $productId int
     */
    public function getByAttrName($attrName, $productId)
    {
        $where = $this->_dbAdapter->quoteInto('sfa.name = ?', $attrName);
        $where .= ' AND ' . $this->_dbAdapter->quoteInto('sfv.product_id = ?', $productId);
        $select = $this->_dbAdapter->select()->from(array('sfv' => 'shopping_filtering_values'))
            ->joinLeft(array('sfa' => 'shopping_filtering_attributes'), 'sfv.attribute_id=sfa.id')
            ->where($where);
        return $this->_dbAdapter->fetchRow($select);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function findAttributeIdByName($name)
    {
        $where = $this->_dbAdapter->quoteInto('sfa.name = ?', $name);
        $select = $this->_dbAdapter->select()->from(array('sfa' => 'shopping_filtering_attributes'), array('sfa.id'))->where($where);

        $currentData = $this->_dbAdapter->fetchRow($select);

        return $currentData;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function findAttributeDataByName($name)
    {
        $where = $this->_dbAdapter->quoteInto('sfa.name = ?', $name);
        $select = $this->_dbAdapter->select()->from(array('sfa' => 'shopping_filtering_attributes'), array(
            'sfa.id',
            'sfa.name',
            'sfa.label'
        ))->where($where);

        $currentData = $this->_dbAdapter->fetchRow($select);

        return $currentData;
    }

    /**
     * @param null $attributeId
     * @return int|null
     */
    public function deleteAttributeById($attributeId = null) {
        $where = $this->_dbAdapter->quoteInto('attribute_id = ?', $attributeId);
        return $this->_dbAdapter->delete('shopping_filtering_values', $where);
    }

    /**
     * @param $productId
     * @param $attributeId
     * @param $value
     * @return mixed
     */
    public function findFilteringValuesByParams($productId, $attributeId, $value)
    {
        $where = $this->_dbAdapter->quoteInto('product_id = ?', $productId);
        $where .= ' AND ' . $this->_dbAdapter->quoteInto('attribute_id = ?', $attributeId);
        $where .= ' AND ' . $this->_dbAdapter->quoteInto('value = ?', $value);

        $select = $this->_dbAdapter->select()->from(array('sfv' => 'shopping_filtering_values'), array(
            'sfv.id',
            'sfv.product_id',
            'sfv.attribute_id',
            'sfv.value',
        ))->where($where);

        $data = $this->_dbAdapter->fetchRow($select);

        return $data;
    }

    /**
     * @param $id
     * @return int
     */
    public function deleteById($id) {
        $where = $this->_dbAdapter->quoteInto('id = ?', $id);
        return $this->_dbAdapter->delete('shopping_filtering_values', $where);
    }
}
