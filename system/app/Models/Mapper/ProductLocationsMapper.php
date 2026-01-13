<?php

/**
 * Class Models_Mapper_ProductLocationsMapper
 *
 * @method Models_Mapper_ProductLocationsMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Models_Mapper_ProductLocationsMapper extends Application_Model_Mappers_Abstract
{

    protected $_dbTable = 'Models_DbTable_ProductLocationsDbtable';

    protected $_model = 'Models_Model_ProductLocationsModel';

    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            throw new Exceptions_SeotoasterException('Given parameter should be ' . $this->_model . ' instance');
        }
        $data = array(
            'product_id'          => $model->getProductId(),
            'location_id'         => $model->getLocationId(),
            'inventory'           => $model->getInventory(),
            'is_default_location' => $model->getIsDefaultLocation(),
            'is_quick_product'    => $model->getIsQuickProduct(),
        );

        $id = $model->getId();
        if ($id) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
            $this->getDbTable()->update($data, $where);
        } else {
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);
        }
        return $model;
    }

    /**
     * @param $productId
     * @return array
     */
    public function findLocationsByProductId($productId)
    {
        $data = array();

        if(!empty($productId)) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('pspl.product_id = ?', intval($productId));
            $select = $this->getDbTable()->getAdapter()->select()->from(array('pspl' => 'shopping_product_locations'), array(
                'pspl.id',
                'pspl.location_id',
                'spl.name',
                'spl.address1',
                'spl.address2',
                'pspl.inventory',
                'pspl.is_default_location',
                'pspl.is_quick_product',
            ))
                ->join(array('spl' => 'shopping_pickup_location'), 'pspl.location_id = spl.id', array())
                ->where($where);

            $data = $this->getDbTable()->getAdapter()->fetchAll($select);
        }

        return $data;
    }

    /**
     * @param $productId
     * @param $locationId
     * @return mixed|null
     */
    public function findLocationByProductIdAndLocationId($productId, $locationId)
    {
        if(!empty($productId) && !empty($locationId)) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
            $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('location_id = ?', $locationId);
            $select = $this->getDbTable()->getAdapter()->select()->from(array('pspl' => 'shopping_product_locations'), array(
                'pspl.id',
                'pspl.product_id',
                'pspl.location_id',
                'pspl.inventory',
                'pspl.is_default_location',
                'pspl.is_quick_product',
            ))
                ->where($where);

            return $this->getDbTable()->getAdapter()->fetchRow($select);
        }

        return null;
    }

    /**
     * @param $id
     * @return int|null
     */
    public function deleteLocation($id)
    {
        if(!empty($id)) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
            return $this->getDbTable()->getAdapter()->delete('shopping_product_locations', $where);
        }

        return null;
    }

    /**
     * @param $productId
     * @return mixed|null
     */
    public function getDefaultLocationByProductId($productId)
    {
        if(!empty($productId)) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
            $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('is_default_location = ?', '1');
            $select = $this->getDbTable()->getAdapter()->select()->from(array('pspl' => 'shopping_product_locations'), array(
                'pspl.id',
                'pspl.product_id',
                'pspl.location_id',
                'pspl.inventory',
                'pspl.is_default_location',
                'pspl.is_quick_product',
            ))
                ->where($where);

            return $this->getDbTable()->getAdapter()->fetchRow($select);
        }

        return null;
    }

    /**
     * Get product list
     *
     * @param $where
     * @param $order
     * @param $limit
     * @param $offset
     * @param $withoutCount
     * @param $singleRecord
     * @param $having
     * @param $searchByLocationId
     * @param $searchByTags
     * @return array|mixed
     */
    public function fetchAllData(
        $where = null,
        $order = null,
        $limit = null,
        $offset = null,
        $withoutCount = false,
        $singleRecord = false,
        $having = '',
        $searchByLocationId = false,
        $searchByTags = false
    ) {

        $productDbTable = new Models_DbTable_Product();

        $select = $productDbTable->getAdapter()->select()
            ->from(array('sp' => 'shopping_product'),
                array(
                    'sp.*'
                )
            )->joinLeft(array('p' => 'page'), 'p.id = sp.page_id', array('p.url'))
            ->joinLeft(array('sb' => 'shopping_brands'), 'sb.id = sp.brand_id', array('brandName' => 'sb.name'));

        if($searchByLocationId) {
            $select->joinLeft(array('pspl' => 'shopping_product_locations'), 'sp.id = pspl.product_id', array());
            $select->joinLeft(array('spl' => 'shopping_pickup_location'), 'pspl.location_id = spl.id', array(
                'locationId' => 'spl.id',
                'locationName' => 'spl.name',
                'locationAddress1' => 'spl.address1',
                'locationAddress2' => 'spl.address2',
                'locationZip' => 'spl.zip',
                'locationCity' => 'spl.city'
            ));
        }

        if($searchByTags) {
            $select->joinLeft(array('pt' => 'shopping_product_has_tag'), 'pt.product_id = sp.id', array());
            $select->joinLeft(array('t' => 'shopping_tags'), 'pt.tag_id = t.id', array());
        }

        if (!empty($having)) {
            $select->having($having);
        }

        $select->group('sp.id');
        if (!empty($order)) {
            $select->order($order);
        }

        if (!empty($where)) {
            $select->where($where);
        }

        $select->limit($limit, $offset);

        if ($singleRecord) {
            $data = $productDbTable->getAdapter()->fetchRow($select);
        } else {
            $data = $productDbTable->getAdapter()->fetchAll($select);
        }

        if ($withoutCount === false) {
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::FROM);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);
            $select->reset(Zend_Db_Select::LIMIT_COUNT);

            $count = array('count' => new Zend_Db_Expr('COUNT(DISTINCT(sp.id))'));

            $select->from(array('sp' => 'shopping_product'), $count);
            if($searchByLocationId) {
                $select->joinLeft(array('pspl' => 'shopping_product_locations'), 'sp.id = pspl.product_id', array());
                $select->joinLeft(array('spl' => 'shopping_pickup_location'), 'pspl.location_id = spl.id', array());
            }

            if($searchByTags) {
                $select->joinLeft(array('pt' => 'shopping_product_has_tag'), 'pt.product_id = sp.id', array());
                $select->joinLeft(array('t' => 'shopping_tags'), 'pt.tag_id = t.id', array());
            }

            $select = $productDbTable->getAdapter()->select()
                ->from(
                    array('subres' => $select),
                    array('count' => 'SUM(count)')
                );

            $count = $productDbTable->getAdapter()->fetchRow($select);

            return array(
                'totalRecords' => $count['count'],
                'data' => $data,
                'offset' => $offset,
                'limit' => $limit
            );
        } else {
            return $data;
        }
    }

    /**
     * Find products by locationId
     *
     * @param $locationId
     * @return array
     */
    public function findProductsByLocationId($locationId)
    {
        $data = array();

        if(!empty($locationId)) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('pspl.location_id = ?', intval($locationId));
            $select = $this->getDbTable()->getAdapter()->select()->from(array('pspl' => 'shopping_product_locations'), array(
                'pspl.product_id'
            ))->where($where);

            $data = $this->getDbTable()->getAdapter()->fetchCol($select);
        }

        return $data;
    }

    /**
     * Get product tags id by product ids
     *
     * @param $productIds
     * @return array|false
     */
    public function findTagsByProductId($productIds)
    {
        if(!empty($productIds)) {
            $tagsDbTable = new Models_DbTable_Tag();

            $where = $tagsDbTable->getAdapter()->quoteInto('sp.id IN (?)', $productIds);
            $where .= ' AND '. new Zend_Db_Expr('st.id IS NOT NULL');

            $select = $tagsDbTable->select()->from(array('sp' => 'shopping_product'), array('st.id', 'st.name'))
                ->joinLeft(array('spht' => 'shopping_product_has_tag'), 'spht.product_id = sp.id', array())
                ->joinLeft(array('st' => 'shopping_tags'), 'spht.tag_id = st.id', array())
                ->group('st.id')
                ->where($where);
            $data = $tagsDbTable->getAdapter()->fetchAll($select);

            return $data;
        }
        return false;
    }


}

