<?php
/**
 * @method Models_Mapper_CartSessionMapper getInstance() getInstance() Returns an instance of itself
 * @method Zend_Db_Table getDbTable() getDbTable()  Returns an instance of Zend_Db_Table
 */
class Models_Mapper_CartSessionMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable	= 'Models_DbTable_CartSession';

	protected $_model	= 'Models_Model_CartSession';

	/**
	 * Save cart to database
	 * @param $model Models_Model_CartSession
	 * @return mixed
	 * @throws Exceptions_SeotoasterPluginException
	 */
	public function save($model) {
		if(!$model instanceof Models_Model_CartSession) {
			throw new Exceptions_SeotoasterPluginException('Wrong model type given.');
		}
		$data = array(
			'ip_address'                 => $model->getIpAddress(),
			'referer'                    => $model->getReferer(),
			'user_id'                    => $model->getUserId(),
			'status'                     => $model->getStatus(),
			'gateway'                    => $model->getGateway(),
			'shipping_address_id'        => $model->getShippingAddressId(),
			'billing_address_id'         => $model->getBillingAddressId(),
			'shipping_price'             => $model->getShippingPrice(),
			'shipping_type'              => $model->getShippingType(),
			'shipping_service'           => $model->getShippingService(),
            'shipping_tracking_id'       => $model->getShippingTrackingId(),
            'shipping_tracking_code_id'  => $model->getShippingTrackingCodeId(),
			'sub_total'                  => $model->getSubTotal(),
			'total_tax'                  => $model->getTotalTax(),
			'total'                      => $model->getTotal(),
            'notes'                      => $model->getNotes(),
			'discount'                   => $model->getDiscount(),
            'shipping_tax'               => $model->getShippingTax(),
            'discount_tax'               => $model->getDiscountTax(),
            'sub_total_tax'              => $model->getSubTotalTax(),
            'discount_tax_rate'          => $model->getDiscountTaxRate(),
            'free_cart'                  => $model->getFreeCart(),
            'refund_amount'              => $model->getRefundAmount(),
            'refund_notes'               => $model->getRefundNotes(),
            'shipping_service_id'        => $model->getShippingServiceId(),
            'shipping_availability_days' => $model->getShippingAvailabilityDays(),
            'shipping_service_info'      => $model->getShippingServiceInfo(),
            'shipping_label_link'        => $model->getShippingLabelLink(),
            'purchased_on'               => $model->getPurchasedOn(),
            'additional_info'            => $model->getAdditionalInfo(),
            'is_gift'                    => $model->getIsGift(),
            'gift_email'                 => $model->getGiftEmail(),
            'order_subtype'              => $model->getOrderSubtype(),
            'partial_percentage'         => $model->getPartialPercentage(),
            'is_partial'                 => $model->getIsPartial(),
            'partial_paid_amount'        => $model->getPartialPaidAmount(),
            'partial_purchased_on'        => $model->getPartialPurchasedOn()
		);

		if(!$model->getId() || null === ($exists = $this->find($model->getId()))) {
			$data['created_at'] = date(DATE_ATOM);
			$newId = $this->getDbTable()->insert($data);
			if ($newId){
				$model->setId($newId);
			}
		}
		else {
			$data['updated_at'] = date(DATE_ATOM);
			$this->getDbTable()->update($data, array('id = ?' => $exists->getId()));
		}

		try {
			$this->_processCartContent($model);
		} catch (Exception $e) {
			error_log($e->getMessage());
			error_log($e->getTraceAsString());
			return false;
		}
		$model->notifyObservers();

		return $model;
	}

	private function _processCartContent(Models_Model_CartSession $cartSession){
		$cartSessionContentDbTable = new Models_DbTable_CartSessionContent();
		$content = $cartSession->getCartContent();

        $cartSessionContentDbTable->getAdapter()->beginTransaction();
		$cartSessionId = $cartSession->getId();

		$cartSessionContentDbTable->delete(array('cart_id = ?' => $cartSessionId));
        if (!empty($content)) {
            $cartItemKeys = array();
            foreach ($content as $item) {
	            $productId = isset($item['product_id']) ? $item['product_id'] : $item['id'];

	            $data = array(
		            'cart_id' => $cartSessionId,
		            'product_id' => $productId,
		            'price' => $item['price'],
		            'qty' => $item['qty'],
		            'tax' => $item['tax'],
		            'tax_price' => isset($item['taxPrice']) ? $item['taxPrice'] : $item['tax_price'],
                    'freebies'  => is_null($item['freebies']) ? 0 : $item['freebies'],
                    'is_digital' => isset($item['isDigital']) ? $item['isDigital'] : $item['is_digital']
	            );
                $optionsList = array();
	            if (isset($item['options']) && !empty($item['options'])) {
                    $productId = isset($item['product_id']) ? $item['product_id'] : $item['id'];
		            $options = array();
                    $archiveOptions = array();
		            foreach ($item['options'] as $optName => $opt) {
			            $options[$opt['option_id']] = isset($opt['id']) ? $opt['id'] : $opt['title'];
                        $archiveOptions[$opt['option_id']] = $opt;
		            }
		            $data['options'] = http_build_query($options);
                    $optionsList = http_build_query($options);
		            unset($options);
	            }

                $cartItemKeys[] = Tools_ShoppingCart::generateCartItemKey($cartSessionId, $productId,
                    $optionsList);

	            $cartSessionContentId = $cartSessionContentDbTable->insert($data);
                try {
                    if (!empty($archiveOptions)) {
                        $this->_processHistoricalOptions($cartSession, $cartSessionContentId, $productId,
                            $archiveOptions, $optionsList);
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    error_log($e->getTraceAsString());
                    return false;
                }
            }


            if (!empty($cartItemKeys)) {
                $cartSessionOptionMapper = Store_Mapper_CartSessionOptionMapper::getInstance();
                $cartSessionOptionMapper->deleteNotUsedProductOptions($cartItemKeys, $cartSessionId);
            }
        }
        $cartSessionContentDbTable->getAdapter()->commit();
	}

    /**
     * Process archive (historical) options
     *
     * @param Models_Model_CartSession $cartSession
     * @param int $cartSessionContentId cart session content id
     * @param int $productId product id
     * @param array $options product options
     * @param string $optionsList options plus option selection Ex: 19=791
     * @throws Exception
     * @throws Exceptions_SeotoasterException
     */
    private function _processHistoricalOptions(
        Models_Model_CartSession $cartSession,
        $cartSessionContentId,
        $productId,
        $options,
        $optionsList
    ) {
        $cartSessionId = $cartSession->getId();
        $cartSessionOptionMapper = Store_Mapper_CartSessionOptionMapper::getInstance();
        $cartOptionMapper = Models_Mapper_OptionMapper::getInstance();
        $allProductOptions = $cartOptionMapper->getOptions(array_keys($options));
        foreach ($options as $optionId => $optionData) {
            $optionSelectionId = $optionData['id'];
            $cartItemKey = Tools_ShoppingCart::generateCartItemKey($cartSessionId, $productId,
                $optionsList);
            $cartItemOptionKey = Tools_ShoppingCart::generateCartItemOptionKey($cartSessionId, $productId,
                $optionData['option_id'], $optionData['id']);

            if (isset($allProductOptions[$optionId])) {
                $cartSessionOptionModel = $cartSessionOptionMapper->getByUniqueKeys($cartItemKey, $cartItemOptionKey);
                if (!$cartSessionOptionModel instanceof Store_Model_CartSessionOption) {
                    $cartSessionOptionModel = new Store_Model_CartSessionOption();
                    $cartSessionOptionModel->setCartId($cartSessionId);
                    $cartSessionOptionModel->setProductId($productId);
                    $cartSessionOptionModel->setOptionId($optionId);
                    $cartSessionOptionModel->setOptionTitle($allProductOptions[$optionId]['title']);
                    $cartSessionOptionModel->setOptionType($allProductOptions[$optionId]['type']);
                    $cartSessionOptionModel->setTitle($options[$optionId]['title']);
                    $cartSessionOptionModel->setPriceSign($options[$optionId]['priceSign']);
                    $cartSessionOptionModel->setPriceValue($options[$optionId]['priceValue']);
                    $cartSessionOptionModel->setPriceType($options[$optionId]['priceType']);
                    $cartSessionOptionModel->setWeightSign($options[$optionId]['weightSign']);
                    $cartSessionOptionModel->setWeightValue($options[$optionId]['weightValue']);
                    $cartSessionOptionModel->setCartItemKey($cartItemKey);
                    $cartSessionOptionModel->setCartItemOptionKey($cartItemOptionKey);
                    $cartSessionOptionModel->setOptionSelectionId($optionSelectionId);
                }

                $cartSessionOptionModel->setCartContentId($cartSessionContentId);
                $cartSessionOptionMapper->save($cartSessionOptionModel);
            }
        }

    }

    /**
     * Search for cart session by id
     * @param $id
     * @param bool $withArchiveOptions get data with archive options
     * @return Models_Model_CartSession
     * @throws Exceptions_SeotoasterException
     * @throws Zend_Db_Table_Exception
     */
	public function find($id, $withArchiveOptions = false) {
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return null;
		}
		$row = $result->current();
		if ($row) {
			return $this->_toModel($row, $withArchiveOptions);
		}
		return null;
	}

	public function fetchAll($where = null, $order = array()) {
		$entries = array();
		$resultSet = $this->getDbTable()->fetchAll($where, $order);
		if(sizeof($resultSet)){
			foreach ($resultSet as $row) {
				$entries[] = $this->_toModel($row);
			}
		}
		return $entries;
	}

    /**
     * @param Zend_Db_Table_Row_Abstract $row
     * @param bool $withArchiveOptions get data with archive options
     * @return mixed
     * @throws Exceptions_SeotoasterException
     */
	private function _toModel(Zend_Db_Table_Row_Abstract $row, $withArchiveOptions = false) {
		$model = new $this->_model($row->toArray());
		$contentTable = new Models_DbTable_CartSessionContent();
		$select = $contentTable->select()
				->setIntegrityCheck(false)
				->from(array('c' => 'shopping_cart_session_content'))
				->join(array('prod' => 'shopping_product'), 'prod.id = c.product_id', array('name', 'sku', 'original_price'=>'price'))
				->where('cart_id = ?', $model->getId());
		$content = $contentTable->fetchAll($select)->toArray();
		if (!empty($content)){
			$cartId = $model->getId();
            if ($withArchiveOptions === true) {
                $archiveOptions = $this->_restoreArchiveOptionsForCartSession($cartId);
            }

			foreach ($content as &$item) {
				if (!empty($item['options'])) {
					parse_str($item['options'], $tmpOptions);
					$options = $this->_restoreOptionsForCartSession($tmpOptions);
					if (!empty($archiveOptions) && isset($archiveOptions[$item['id']])) {
                        $item['archiveOptions'] = $archiveOptions[$item['id']];
                    } else {
                        $item['archiveOptions'] = array();
                    }
					$item['options'] = empty($options) ? null : $options;
				}
			}

			$model->setCartContent($content);
		}

		return $model;
	}

	public function findByProductId($productId){
		if (!is_numeric($productId)){}
		$select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)->setIntegrityCheck(false)
				->from(array('cart' => 'shopping_cart_session'))
				->join(array('content' => 'shopping_cart_session_content'), 'content.cart_id = cart.id')
				->where('content.product_id = ?', $productId);

		APPLICATION_ENV === 'development' && error_log($select->__toString());
		return $this->getDbTable()->fetchAll($select)->toArray();
	}

    public function findByClientId($clientId, $token){
        $where = $this->getDbTable()->getAdapter()->quoteInto('shipping_address_id = ?', $token);
        $where .= ' OR '. $this->getDbTable()->getAdapter()->quoteInto('billing_address_id = ?', $token);
        $where .= ' AND '. $this->getDbTable()->getAdapter()->quoteInto('user_id = ?', $clientId);
        $select = $this->getDbTable()->getAdapter()->select()->from($this->getDbTable()->info('name'))->where($where);
        $row = $this->getDbTable()->getAdapter()->fetchRow($select);
        if(null == $row) {
            return null;
        }
        return new $this->_model($row);

    }

    /**
     * Fetch orders  by user id including recurring payments data
     *
     * @param int $userId user Id
     * @param bool $withoutRecurring without recurring orders
     * @return array
     *
     */
	public function fetchOrders($userId, $withoutRecurring = false){
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart.user_id = ?', $userId);
        if ($withoutRecurring) {
            $where .= ' AND '. $this->getDbTable()->getAdapter()->quoteInto('recurrent.cart_id IS NULL AND user_id = ?', $userId);
        }
        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)->setIntegrityCheck(false)
				->from(array('cart' => 'shopping_cart_session'))
				->joinLeft(array('recurrent' => 'shopping_recurring_payment'), 'recurrent.cart_id = cart.id', array('recurring_id' => 'recurrent.cart_id'))
                ->where($where);
        $entries = array();
        $resultSet = $this->getDbTable()->fetchAll($select);
        if(sizeof($resultSet)){
            foreach ($resultSet as $row) {
                $entries[] = $this->_toModel($row);
            }
        }
		return $entries;
	}

    /**
     * Restore archive product options
     *
     * @param int $cartId cart id
     * @return array
     */
    protected function _restoreArchiveOptionsForCartSession($cartId)
    {
        $cartSessionOptionMapper = Store_Mapper_CartSessionOptionMapper::getInstance();
        $archiveOptions = $cartSessionOptionMapper->getByCartId($cartId);
        $preparedOptions = array();
        if (empty($archiveOptions)) {
            return array();
        }

        foreach ($archiveOptions as $archiveOption) {
            $preparedOptions[$archiveOption['cart_content_id']][$archiveOption['option_title']] = $archiveOption;
        }

        return $preparedOptions;
    }

	protected function _restoreOptionsForCartSession($mapping){
		if (!is_array($mapping) || empty($mapping)) {
			throw new Exceptions_SeotoasterException('Wrong parameters passed');
		}

		$options = Models_Mapper_OptionMapper::getInstance()->find(array_keys($mapping));

		$result = array();
		foreach ($options as $option) {
			$value = $mapping[$option->getId()];
			switch ($option->getType()){
				case Models_Model_Option::TYPE_DATE:
				case Models_Model_Option::TYPE_TEXT:
					$result[$option->getTitle()] = array(
						'option_id'   => $option->getId(),
						'title'       => $value,
						'priceSign'   => null,
						'priceType'   => null,
						'priceValue'  => null,
						'weightSign'  => null,
						'weightValue' => null
					);
					break;
                case Models_Model_Option::TYPE_TEXTAREA:
                    $result[$option->getTitle()] = array(
                        'option_id'   => $option->getId(),
                        'title'       => $value,
                        'priceSign'   => null,
                        'priceType'   => null,
                        'priceValue'  => null,
                        'weightSign'  => null,
                        'weightValue' => null
                    );
                    break;
				default:
					$selections = $option->getSelection();
					if (empty($selections)){
						continue;
					}
					$result[$option->getTitle()] = current(array_filter($selections, function($sel) use ($value) {
						return $sel['id'] === $value;
					}));
					break;
			}
		}

		return $result;
	}

    public function updateAddress($oldTokenId, $type = 'shipping', $data = array()){
            $where = $this->getDbTable()->getAdapter()->quoteInto('shipping_address_id = ?', $oldTokenId);
            if($type == 'billing') {
                $where = $this->getDbTable()->getAdapter()->quoteInto('billing_address_id = ?', $oldTokenId);
            }
            return $this->getDbTable()->getAdapter()->update('shopping_cart_session', $data, $where);
    }
}
