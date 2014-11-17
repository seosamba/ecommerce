<?php
/**
 * PickupLocationConfig.php
 *
 * @method Store_Mapper_PickupLocationConfigMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_PickupLocationConfigMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_PickupLocationConfig';

    protected $_dbTable = 'Store_DbTable_PickupLocationConfig';

    /**
     * Save pickup locations config
     *
     * @param Store_Model_PickupLocationConfig $model
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }
        $data = array(
            'id' => $model->getId(),
            'amount_type_limit' => $model->getAmountTypeLimit(),
            'amount_limit' => $model->getAmountLimit()
        );

        $locationZones = $model->getLocationZones();
        $where = $this->getDbTable()->getAdapter()->quoteInto("id=?", $data['id']);
        $existRow = $this->fetchAll($where);
        if (!empty($existRow)) {
            $this->getDbTable()->update($data, $where);
        } else {
            $this->getDbTable()->insert($data);
        }
        $this->_saveLocationZones($data['id'], $locationZones);

    }

    /**
     * Process and save location zones
     *
     * @param int $configId Pickup location config id
     * @param array $zones
     */
    private function _saveLocationZones($configId, $zones)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto("config_id=?", $configId);
        $this->getDbTable()->getAdapter()->delete('shopping_pickup_location_zones', $where);
        foreach ($zones as $confZoneId => $value) {
            if (is_array($value)) {
                $data = array(
                    'config_id' => $configId,
                    'pickup_location_category_id' => $value['zoneId'],
                    'amount_location_category' => $value['zoneAmount'],
                    'config_zone_id' => $confZoneId
                );
                $this->getDbTable()->getAdapter()->insert('shopping_pickup_location_zones', $data);
            }
        }
    }

    /**
     * Get pickup location config
     *
     * @return array
     */
    public function getConfig()
    {
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('shopping_pickup_location_config', array('id', 'amount_type_limit', 'amount_limit')));
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Get pickup locations zones
     *
     * @return array
     */
    public function getLocationZones()
    {
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(
                'shopping_pickup_location_zones',
                array(
                    'conf_key' => new Zend_Db_Expr("CONCAT(config_id, '_', config_zone_id)"),
                    'pickup_location_category_id',
                    'amount_location_category',
                    'config_zone_id',
                    'config_id'
                )
            );
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }


    /**
     * Get pickup locations zones full info
     *
     * @param int $zonesQuantity An SQL LIMIT count
     * @return array
     */
    public function getLocationZonesInfo($zonesQuantity)
    {
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(
                'shopping_pickup_location_zones',
                array(
                    'pickup_location_category_id',
                )
            )->limit($zonesQuantity);
        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }

    /**
     * Get shipping pickup location data
     *
     * @param $comparator (find by price or by weight)
     * @param bool $locationId (find by pickup location id)
     * @param array $coordinates (location coordinates latitudeStart, latitudeEnd, longitudeStart, longitudeEnd)
     * @param bool $maxWeight  (limit by weight)
     * @param bool $groupByCities (group by cities)
     * @param array $searchBy (search by specific fields like: city, country, etc ...)
     * @return array|mixed
     */
    public function getLocations(
        $comparator,
        $locationId = false,
        $coordinates = array(),
        $maxWeight = false,
        $groupByCities = false,
        $searchBy = array()
    ) {
        $pickupLocationsZonesConfig = new Store_DbTable_PickupLocationZonesConfig();
        $where = $pickupLocationsZonesConfig->getAdapter()->quoteInto('shplz.pickup_location_category_id <> ?', 0);
        if ($locationId) {
            $where .= ' AND ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto('shpl.id = ?', $locationId);
        }
        if ($maxWeight) {
            $where .= ' AND (' . $pickupLocationsZonesConfig->getAdapter()->quoteInto('shpl.weight > ?', $maxWeight);
            $where .= ' OR shpl.weight IS NULL';
            $where .= ' OR ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto('shpl.weight = ?', 0);
            $where .= ' ) ';
        }
        if (!empty($searchBy)) {
            foreach ($searchBy as $field => $value) {
                $where .= ' AND ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto($field . ' = ?', $value);
            }
        }
        if (!empty($coordinates)) {
            $where .= ' AND ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
                'shpl.lat >= ?',
                $coordinates['latitudeStart']
            );
            $where .= ' AND ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
                'shpl.lat <= ?',
                $coordinates['latitudeEnd']
            );
            $where .= ' AND ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
                'shpl.lng >= ?',
                $coordinates['longitudeStart']
            );
            $where .= ' AND ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
                'shpl.lng <= ?',
                $coordinates['longitudeEnd']
            );
        }
        $where .= ' AND (( ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
            'splc.amount_type_limit  = ?',
            Shopping::AMOUNT_TYPE_UP_TO
        );
        $where .= ' AND  ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
            'splc.amount_limit  >= ?',
            $comparator
        ) . ' )';
        $where .= ' OR ( ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
            'splc.amount_type_limit  = ?',
            Shopping::AMOUNT_TYPE_OVER
        );
        $where .= ' AND  ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
            'splc.amount_limit  <= ?',
            $comparator
        ) . ' )';
        $where .= ' OR ( ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
            'splc.amount_type_limit  = ?',
            Shopping::AMOUNT_TYPE_EACH_OVER
        );
        $where .= ' AND  ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
            'splc.amount_limit  <= ?',
            $comparator
        ) . ' ))';

        $select = $pickupLocationsZonesConfig->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)
            ->setIntegrityCheck(false)
            ->from(array('shplz' => 'shopping_pickup_location_zones'), array('price' => 'amount_location_category'))
            ->joinLeft(
                array('shpl' => 'shopping_pickup_location'),
                'shplz.pickup_location_category_id=shpl.location_category_id'
            )
            ->joinLeft(
                array('shplcat' => 'shopping_pickup_location_category'),
                'shplz.pickup_location_category_id=shplcat.id',
                array('imgName' => 'img')
            )
            ->joinLeft(
                array('splc' => 'shopping_pickup_location_config'),
                'splc.id=shplz.config_id',
                array('limitType' => 'amount_type_limit', 'amount_limit')
            );
        if ($groupByCities) {
            $where .= ' AND  ' . $pickupLocationsZonesConfig->getAdapter()->quoteInto(
                'shpl.city  <> ?',
                ''
            );
            $select->group(array('shpl.city', 'shpl.country'));
        } else {
            $select->group('shpl.id');
        }

        $select->where($where);
        if ($locationId) {
            return $pickupLocationsZonesConfig->getAdapter()->fetchRow($select);
        }
        return $pickupLocationsZonesConfig->getAdapter()->fetchAll($select);


    }

    /**
     * Save cart pickup locations
     *
     * @param int $cartId Shopping cart id
     * @param array $address (address1, address2, zip, country, city, working_hours, phone, location_category_id, name, lat, lng)
     */
    public function saveCartPickupLocation($cartId, $address = array())
    {
        if (!empty($address)) {
            $pickupLocationsCart = new Store_DbTable_PickupLocationCart();
            $where = $pickupLocationsCart->getAdapter()->quoteInto('cart_id = ?', $cartId);
            $cartLocationExist = $pickupLocationsCart->getAdapter()->fetchAll(
                $pickupLocationsCart->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)->from(
                    'shopping_pickup_location_cart'
                )->where($where)
            );
            $data = array(
                'cart_id' => $cartId,
                'address1' => $address['address1'],
                'address2' => $address['address2'],
                'zip' => $address['zip'],
                'country' => $address['country'],
                'city' => $address['city'],
                'working_hours' => $address['working_hours'],
                'phone' => $address['phone'],
                'location_category_id' => $address['location_category_id'],
                'name' => $address['name'],
                'lat' => $address['lat'],
                'lng' => $address['lng']
            );
            if (empty($cartLocationExist)) {
                $pickupLocationsCart->insert($data, $where);
            } else {
                $pickupLocationsCart->update($data, $where);
            }
        }
    }

    /**
     * Get pickup location by cart id
     *
     * @param int $cartId
     * @return mixed
     */
    public function getCartPickupLocationByCartId($cartId)
    {
        $pickupLocationsCart = new Store_DbTable_PickupLocationCart();
        $where = $pickupLocationsCart->getAdapter()->quoteInto('shplc.cart_id = ?', $cartId);
        $select = $pickupLocationsCart->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)
            ->setIntegrityCheck(false)
            ->from(array('shplc' => 'shopping_pickup_location_cart'))
            ->where($where);
        return $pickupLocationsCart->getAdapter()->fetchRow($select);

    }

    /**
     * Get pickup location user address by user id
     *
     * @param int $userId
     * @return array
     */
    public function getUserAddressByUserId($userId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('scs.user_id = ?', $userId);
        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)
            ->setIntegrityCheck(false)
            ->from(array('splc' => 'shopping_pickup_location_cart'), array())
            ->joinLeft(array('scs' => 'shopping_cart_session'), 'splc.cart_id=scs.id', array('shipping_address_id'))
            ->joinLeft(array('sca' => 'shopping_customer_address'), 'sca.id=scs.shipping_address_id', array())
            ->where($where);
        return $this->getDbTable()->getAdapter()->fetchCol($select);
    }

    /**
     * Delete pickup location config by id
     *
     * @param int $configId
     */
    public function deleteConfig($configId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto("id = ?", $configId);
        $this->getDbTable()->delete($where);
    }
}
