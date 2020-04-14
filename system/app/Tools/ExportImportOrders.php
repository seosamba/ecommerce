<?php
/**
 * Class Tools_ExportImportOrders
 */
class Tools_ExportImportOrders
{

    const DEFAULT_IMPORT_ORDER = 'default_import_order';

    const PRESTASHOP_IMPORT_ORDER = 'prestashop_import_order';

    const MAGENTO_IMPORT_ORDER = 'magento_import_order';

    /**
     * Orders statuses
     *
     * @var array
     */
    public static $statuses = array(
        Shopping::GATEWAY_QUOTE => array(
            Models_Model_CartSession::CART_STATUS_PROCESSING => 'Quote Sent',
            Models_Model_CartSession::CART_STATUS_PENDING    => 'New quote',
            Models_Model_CartSession::CART_STATUS_CANCELED   => 'Lost opportunity'
        ),
        Models_Model_CartSession::CART_STATUS_NEW        => 'Abandoned carts',
        Models_Model_CartSession::CART_STATUS_PENDING    => 'Merchant action required - Customer charged',
        Models_Model_CartSession::CART_STATUS_PROCESSING => 'Technical processing - Customer not charged',
        Models_Model_CartSession::CART_STATUS_COMPLETED  => 'Payment Received',
        Models_Model_CartSession::CART_STATUS_CANCELED   => 'Canceled',
        Models_Model_CartSession::CART_STATUS_SHIPPED    => 'Items Shipped',
        Models_Model_CartSession::CART_STATUS_DELIVERED  => 'Items Delivered',
        Models_Model_CartSession::CART_STATUS_REFUNDED   => 'Refunded purchase',
        Models_Model_CartSession::CART_STATUS_ERROR      => 'Error'
    );

    public static function prepareOrdersDataForExport($data, $ordersIds)
    {
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        unset($data['name']);
        unset($data['run']);
        unset($data['orderIds']);
        unset($data['controller']);
        unset($data['action']);
        $filters = $data['filters'];
        unset($data['filters']);
        $shoppingConfigMapper = Models_Mapper_ShoppingConfig::getInstance();
        $excludeFields = array();
        foreach ($data as $exportFieldName => $exportFieldValue) {
            if (!preg_match('~checked~', $exportFieldName)) {
                if ($exportFieldValue === '') {
                    $exportFieldValue = $exportFieldName;
                }
                if (!isset($data[$exportFieldName . '-checked'])) {
                    $excludeFields[$exportFieldName] = $exportFieldName;
                    $exportFields[$exportFieldName] = array('label' => $exportFieldValue, 'checked' => 0);
                } else {
                    $renamedFields[] = $exportFieldValue;
                    $exportFields[$exportFieldName] = array('label' => $exportFieldValue, 'checked' => 1);
                }
            }
        }
        $config = array(Shopping::ORDER_EXPORT_CONFIG => serialize($exportFields));
        $shoppingConfigMapper->save($config);
        $dataToExport = Models_Mapper_OrdersMapper::getInstance()->fetchOrdersForExport(
                $ordersIds,
                $excludeFields,
                $filters
            );

        if (!empty($dataToExport)) {
            $headers[] = $renamedFields;
            $fileName = 'orderlist.' . date("Y-m-d", time()) . '.csv';
            $filePath = $websiteHelper->getPath() . $websiteHelper->getTmp() . $fileName;
            $expFile = fopen($filePath, 'w');
            $dataToExport = array_merge($headers, $dataToExport);
            $statesNumeric = Tools_Geo::getState(null, true);
            $stateTable = new Zend_Db_Table('shopping_list_state');
            $select = $stateTable->select()->from($stateTable,array('id','state'));
            $statesAlpha = $stateTable->getAdapter()->fetchPairs($select);
            foreach ($dataToExport as $key => $data) {
                if (!empty($key)) {
                    if (!empty($data['billing_state'])) {
                        if (is_numeric($data['billing_state']) && array_key_exists($data['billing_state'], $statesNumeric)) {
                            $data['billing_state'] = $statesNumeric[$data['billing_state']];
                        } else {
                            $stateExists = array_search($data['billing_state'], $statesAlpha, true);
                            if ($stateExists !== false) {
                                $data['billing_state'] = $statesNumeric[$stateExists];
                            }
                        }
                    }
                    if (!empty($data['shipping_state'])) {
                        if (is_numeric($data['shipping_state']) && array_key_exists($data['shipping_state'], $statesNumeric)) {
                            $data['shipping_state'] = $statesNumeric[$data['shipping_state']];
                        }  else {
                            $stateExists = array_search($data['shipping_state'], $statesAlpha, true);
                            if ($stateExists !== false) {
                                $data['shipping_state'] = $statesNumeric[$stateExists];
                            }
                        }

                    }
                    if(!empty($data['status_label'])) {
                        $data['status_label'] = Tools_ExportImportOrders::exportImportCartStatuses($data['gateway'], $data['status_label']);
                    }
                }
                fputcsv($expFile, $data, ',', '"');
            }
            fclose($expFile);
            Tools_ExportImportOrders::downloadCsv($filePath, $fileName);
        }

    }

    /**
     * @param $action
     * @param $gateway
     * @param $status
     * @return false|int|mixed|string
     */
    public static function exportImportCartStatuses($gateway, $status)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $processingStatus = $status;
        $status = Tools_ExportImportOrders::$statuses[$status];

        if($gateway == Shopping::GATEWAY_QUOTE) {
            $status = Tools_ExportImportOrders::$statuses[Shopping::GATEWAY_QUOTE][$status];

            if(empty($status)) {
                $status = Tools_ExportImportOrders::$statuses[$processingStatus];
            }
        }

        if(empty($status)) {
            $status = $processingStatus;
        }

        return $translator->translate($status);
    }

    public static function prepareImportOrdersReport($importErrors)
    {
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        if (empty($importErrors)) {
            exit;
        }
        $headers = array(
            'orders ids',
            'already imported orders',
            'empty user_email',
            'sku, prices, taxes quantity mismatched',
            'empty sku or mpn',
            'product doesn\'t exist',
            'wrong country'
        );
        $fileName = 'ordersReport.' . date("Y-m-d", time()) . '.csv';
        $filePath = $websiteHelper->getPath() . $websiteHelper->getTmp() . $fileName;
        $expFile = fopen($filePath, 'w');
        fputcsv($expFile, $headers, ',', '"');
        foreach ($importErrors as $errorData) {
            fputcsv($expFile, $errorData, ',', '"');
        }
        fclose($expFile);
        Tools_ExportImportOrders::downloadCsv($filePath, $fileName);
    }

    public static function addOrderAddress($customerId, $address, $type = null)
    {
        $addressTable = new Models_DbTable_CustomerAddress();
        if (!empty($address)) {
            if ($type !== null) {
                $address['address_type'] = $type;
            }
            $address = Tools_Misc::clenupAddress($address);
            $address['id'] = Tools_Misc::getAddressUniqKey($address);
            $address['user_id'] = $customerId;
            if (null === ($row = $addressTable->find($address['id'])->current())) {
                $row = $addressTable->createRow();
            }
            $row->setFromArray($address);

            return $row->save();
        }
        return null;
    }

    public static function getOrderImportFieldsNames()
    {
        $orderImportFieldNames = array(
            'order_id',
            'updated_at',
            'status',
            'sku',
            'product_price',
            'product_tax',
            'product_qty',
            'shipping_type',
            'shipping_service',
            'gateway',
            'shipping_price',
            'discount_tax_rate',
            'sub_total',
            'shipping_tax',
            'discount_tax',
            'sub_total_tax',
            'total_tax',
            'discount',
            'total',
            'notes',
            'shipping_tracking_code',
            'user_prefix',
            'user_name',
            'user_email',
            'shipping_prefix',
            'shipping_firstname',
            'shipping_lastname',
            'shipping_company',
            'shipping_email',
            'shipping_phonecountrycode',
            'shipping_phone_country_code_value',
            'shipping_phone',
            'shipping_mobilecountrycode',
            'shipping_mobile_country_code_value',
            'shipping_mobile',
            'shipping_country',
            'shipping_city',
            'shipping_state',
            'shipping_zip',
            'shipping_address1',
            'shipping_address2',
            'billing_prefix',
            'billing_firstname',
            'billing_lastname',
            'billing_company',
            'billing_email',
            'billing_phonecountrycode',
            'billing_phone_country_code_value',
            'billing_phone',
            'billing_mobilecountrycode',
            'billing_mobile_country_code_value',
            'billing_mobile',
            'billing_country',
            'billing_city',
            'billing_state',
            'billing_zip',
            'billing_address1',
            'billing_address2',
            'is_gift',
            'gift_email'
        );
        return $orderImportFieldNames;
    }

    public static function createOrdersCsv($ordersCsv, $importOrdersConfigFields, $currentTemplateName, $defaultOrderStatus)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $ordersCsvFile = fopen($ordersCsv['file']['tmp_name'], 'r');
        $minimumRequiredFields = array(
            'order_id',
            'sku',
            'user_name',
            'user_email'
        );

        $shoppingConfigMapper = Models_Mapper_ShoppingConfig::getInstance();
        $exportConfig = $shoppingConfigMapper->getConfigParam(Shopping::ORDER_IMPORT_CONFIG);

        foreach ($importOrdersConfigFields as $exportFieldName => $exportFieldValue) {
            $exportFields[$exportFieldName] = array('label' => $exportFieldValue, 'field' => $exportFieldName);
        }
        if ($exportConfig !== null) {
            $exportConfig = unserialize($exportConfig);
            $exportConfig[$currentTemplateName] = $exportFields;
            $config = array(Shopping::ORDER_IMPORT_CONFIG =>serialize($exportConfig));
        }else{
            $config = array(Shopping::ORDER_IMPORT_CONFIG => serialize(array($currentTemplateName =>$exportFields)));
        }
        $shoppingConfigMapper->save($config);
        $assignHeaders = false;
        if ($ordersCsv !== false) {
            while (($orderData = fgetcsv($ordersCsvFile, ',')) !== false) {
                if (!$assignHeaders) {
                    $ordersHeaders = array_flip(array_map('strtolower', $orderData));
                    $changedMinReqFields = array_intersect_key(
                        $importOrdersConfigFields,
                        array_flip($minimumRequiredFields)
                    );
                    $importOrdersConfigFields = array_map('strtolower', $importOrdersConfigFields);
                    $changedMinReqFields = array_map('strtolower', $changedMinReqFields);
                    $requiredFields = array_diff_key(array_flip($changedMinReqFields), $ordersHeaders);
                    $assignHeaders = true;
                    $notUsedFields = array_diff_key($ordersHeaders, array_flip($importOrdersConfigFields));
                    if (!empty($requiredFields)) {
                        $errorMessage = '';
                        foreach ($requiredFields as $fieldMissed => $key) {
                            $errorMessage .= $fieldMissed . '<br />';
                        }
                        fclose($ordersCsvFile);
                        return array(
                            'error' => true,
                            'errorMessage' => $translator->translate(
                                'Required fields missed'
                            ) . '<br />' . $errorMessage
                        );
                    }
                    $userMapper = Application_Model_Mappers_UserMapper::getInstance();
                    $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();
                    $productMapper = Models_Mapper_ProductMapper::getInstance();
                    $cartSessionContentDbTable = new Models_DbTable_CartSessionContent();
                    $userModel = new Application_Model_Models_User();
                    $countries = Tools_Geo::getCountries(true);
                    $states = Tools_Geo::getState(null, true);
                    $importOrderDbTable = new Store_DbTable_ImportOrder();
                    $emailValidate = new Tools_System_CustomEmailValidator();
                    $importOrdersErrors = array();
                    $importedOrdersIds = array();
                    $importedContentData = array();
                    $importedOrdersData = array();
                    $importHasError = false;
                    $productBySku = 'sku';
                    $existingProducts = $productMapper->getDbTable()->getAdapter()->fetchAssoc(
                        $productMapper->getDbTable()->getAdapter()->select()->from(
                            'shopping_product',
                            array($productBySku, 'id', 'price')
                        )
                    );
                    $existingUsers = $userMapper->getDbTable()->getAdapter()->fetchAssoc(
                        $userMapper->getDbTable()->getAdapter()->select()->from('user', array('email', 'id'))
                    );
                    $importedOrders = $importOrderDbTable->getAdapter()->fetchAssoc(
                        $importOrderDbTable->getAdapter()->select()->from(
                            'shopping_import_orders',
                            array('import_order_id', 'real_order_id')
                        )
                    );
                    continue;
                }
                $userEmail = $orderData[$ordersHeaders[$importOrdersConfigFields['user_email']]];
                $orderImportId = $orderData[$ordersHeaders[$importOrdersConfigFields['order_id']]];
                if (array_key_exists($orderImportId, $importedOrders)) {
                    $importOrdersErrors[] = array($orderImportId, '+', '', '', '', '', '');
                    $importHasError = true;
                    continue;
                }
                if (!$emailValidate->isValid($userEmail)) {
                    $importOrdersErrors[] = array($orderImportId, '', '+', '', '', '', '');
                    $importHasError = true;
                    continue;
                }
                if (!array_key_exists($userEmail, $existingUsers)) {
                    $userModel->setId(null);
                    $userModel->setEmail($orderData[$ordersHeaders[$importOrdersConfigFields['user_email']]]);
                    $userModel->setPrefix($orderData[$ordersHeaders[$importOrdersConfigFields['user_prefix']]]);
                    $userModel->setFullName($orderData[$ordersHeaders[$importOrdersConfigFields['user_name']]]);
                    $userModel->setPassword(microtime());
                    $userModel->setRoleId(Shopping::ROLE_CUSTOMER);
                    $userId = $userMapper->save($userModel);
                    $existingUsers[$userModel->getEmail()]['id'] = $userId;
                    $existingUsers[$userModel->getEmail()]['email'] = $userModel->getEmail();
                } else {
                    $userId = $existingUsers[$userEmail]['id'];
                }

                $cartContent = array();

                $orderProductSku = explode(
                    ',',
                    $orderData[$ordersHeaders[$importOrdersConfigFields[$productBySku]]]
                );
                $skuQuantity = count($orderProductSku);
                if (!isset($ordersHeaders[$importOrdersConfigFields['product_price']])) {
                    $orderProductPrice = false;
                } else {
                    $orderProductPrice = explode(
                        ',',
                        $orderData[$ordersHeaders[$importOrdersConfigFields['product_price']]]
                    );
                }
                if (!isset($ordersHeaders[$importOrdersConfigFields['product_qty']])) {
                    $orderProductQty = array_fill(0, $skuQuantity, 1);
                } else {
                    $orderProductQty = explode(
                        ',',
                        $orderData[$ordersHeaders[$importOrdersConfigFields['product_qty']]]
                    );
                }
                if (!isset($ordersHeaders[$importOrdersConfigFields['product_tax']])) {
                    $orderProductTax = false;
                } else {
                    $orderProductTax = explode(
                        ',',
                        $orderData[$ordersHeaders[$importOrdersConfigFields['product_tax']]]
                    );
                }
                if ($skuQuantity !== count($orderProductQty)) {
                    $importOrdersErrors[] = array($orderImportId, '', '', '+', '', '', '');
                    $importHasError = true;
                    continue;
                }

                $subTotal = 0;
                $subTotalTax = 0;
                foreach ($orderProductSku as $key => $sku) {
                    if (trim($sku) === '') {
                        $importOrdersErrors[] = array($orderImportId, '', '', '', '+', '', '');
                        $importHasError = true;
                        break;
                    }
                    if (!array_key_exists($sku, $existingProducts)) {
                        $importOrdersErrors[] = array($orderImportId, '', '', '', '', '+', '');
                        $importHasError = true;
                        break;
                    }
                    $cartContent[$key]['product_id'] = $existingProducts[$sku]['id'];
                    if (isset($orderProductPrice[$key]) && is_numeric($orderProductPrice[$key])) {
                        $cartContent[$key]['price'] = $orderProductPrice[$key];
                    } else {
                        $cartContent[$key]['price'] = $existingProducts[$sku]['price'];
                    }
                    $cartContent[$key]['qty'] = intval($orderProductQty[$key]);
                    if (isset($orderProductTax[$key]) && is_numeric($orderProductTax[$key])) {
                        $cartContent[$key]['tax'] = $orderProductTax[$key];
                    } else {
                        $cartContent[$key]['tax'] = 0;
                    }
                    $cartContent[$key]['tax_price'] = $cartContent[$key]['price'] + $cartContent[$key]['tax'];
                    $subTotal += $cartContent[$key]['price']*$cartContent[$key]['qty'];
                    if (!isset($ordersHeaders[$importOrdersConfigFields['sub_total_tax']])) {
                        $subTotalTax += $cartContent[$key]['tax']*$cartContent[$key]['qty'];
                    }
                }

                if (!empty($cartContent)) {
                    if (isset($ordersHeaders[$importOrdersConfigFields['updated_at']])) {
                        $date = $orderData[$ordersHeaders[$importOrdersConfigFields['updated_at']]];
                    } else {
                        $date = date(DATE_ATOM);
                    }
                    $notes = '';
                    if (isset($ordersHeaders[$importOrdersConfigFields['notes']])) {
                        $notes = $orderData[$ordersHeaders[$importOrdersConfigFields['notes']]];
                    } else {
                        if (!empty($notUsedFields)) {
                            foreach ($notUsedFields as $key) {
                                $notes .= $orderData[$key] . ' ';
                            }
                        }
                    }
                    $gateway = isset($ordersHeaders[$importOrdersConfigFields['gateway']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['gateway']]] : '';
                    $shippingPrice = isset($ordersHeaders[$importOrdersConfigFields['shipping_price']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_price']]] : 0;
                    $discountTax = isset($ordersHeaders[$importOrdersConfigFields['discount_tax']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['discount_tax']]] : 0;
                    $subTotalTax = isset($ordersHeaders[$importOrdersConfigFields['sub_total_tax']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['sub_total_tax']]] : 0;
                    $discount = isset($ordersHeaders[$importOrdersConfigFields['discount']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['discount']]] : 0;
                    $shippingType = isset($ordersHeaders[$importOrdersConfigFields['shipping_type']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_type']]] : '';
                    $shippingService = isset($ordersHeaders[$importOrdersConfigFields['shipping_service']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_service']]] : '';
                    $shippingTrackingCode = isset($ordersHeaders[$importOrdersConfigFields['shipping_tracking_code']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_tracking_code']]] : '';
                    $shippingTax = isset($ordersHeaders[$importOrdersConfigFields['shipping_tax']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_tax']]] : 0;
                    $totalTax = isset($ordersHeaders[$importOrdersConfigFields['total_tax']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['total_tax']]] : 0;

                    if (isset($ordersHeaders[$importOrdersConfigFields['shipping_firstname']])) {
                        $shippingFirstName = $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_firstname']]];
                    } else {
                        $shippingFirstName = $orderData[$ordersHeaders[$importOrdersConfigFields['user_email']]];
                    }

                    $shippingAddressId = null;
                    if ($shippingFirstName !== '') {
                        $shippingAddress = array();
                        //TODO states and countries identification
                        $shippingCountry = isset($ordersHeaders[$importOrdersConfigFields['shipping_country']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_country']]] : null;
                        if (!array_key_exists(
                            $shippingCountry,
                            $countries
                        ) && $shippingCountry !== '' && $shippingCountry !== null
                        ) {
                            $importOrdersErrors[] = array($orderImportId, '', '', '', '', '', '+');
                            $importHasError = true;
                            continue;
                        } elseif (trim($shippingCountry) === '') {
                            $shippingCountry = null;
                        }
                        $shippingState = isset($ordersHeaders[$importOrdersConfigFields['shipping_state']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_state']]] : '';
                        $shippingState = array_search($shippingState, $states);
                        if (!$shippingState) {
                            $shippingState = null;
                        }

                        $shippingAddress['prefix'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_prefix']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_prefix']]] : '';
                        $shippingAddress['firstname'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_firstname']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_firstname']]] : '';
                        $shippingAddress['lastname'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_lastname']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_lastname']]] : '';
                        $shippingAddress['company'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_company']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_company']]] : '';
                        $shippingAddress['email'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_email']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_email']]] : '';
                        $shippingAddress['phonecountrycode'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_phonecountrycode']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_phonecountrycode']]] : '';
                        $shippingAddress['phone_country_code_value'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_phone_country_code_value']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_phone_country_code_value']]] : '';
                        $shippingAddress['phone'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_phone']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_phone']]] : '';
                        $shippingAddress['mobilecountrycode'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_mobilecountrycode']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_mobilecountrycode']]] : '';
                        $shippingAddress['mobile_country_code_value'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_mobile_country_code_value']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_mobile_country_code_value']]] : '';
                        $shippingAddress['mobile'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_mobile']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_mobile']]] : '';
                        $shippingAddress['country'] = $shippingCountry;
                        $shippingAddress['city'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_city']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_city']]] : '';
                        $shippingAddress['state'] = $shippingState;
                        $shippingAddress['zip'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_zip']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_zip']]] : '';
                        $shippingAddress['address1'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_address1']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_address1']]] : '';
                        $shippingAddress['address2'] = isset($ordersHeaders[$importOrdersConfigFields['shipping_address2']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['shipping_address2']]] : '';
                        $shippingAddressId = Tools_ExportImportOrders::addOrderAddress(
                            $userId,
                            $shippingAddress,
                            Models_Model_Customer::ADDRESS_TYPE_SHIPPING
                        );
                    }

                    $billingFirstName = isset($ordersHeaders[$importOrdersConfigFields['billing_firstname']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_firstname']]] : '';
                    $billingAddressId = null;
                    if ($billingFirstName !== '') {
                        $billingAddress = array();
                        //TODO states and countries identification
                        $billingCountry = isset($ordersHeaders[$importOrdersConfigFields['billing_country']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_country']]] : null;
                        if (!array_key_exists($billingCountry, $countries) && $billingCountry !== null && trim(
                            $billingCountry
                        ) !== ''
                        ) {
                            $importOrdersErrors[] = array($orderImportId, '', '', '', '', '', '+');
                            $importHasError = true;
                            continue;
                        } elseif (trim($billingCountry) === '') {
                            $billingCountry = null;
                        }

                        $billingState = isset($ordersHeaders[$importOrdersConfigFields['billing_state']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_state']]] : '';
                        $billingState = array_search($billingState, $states);
                        if (!$billingState) {
                            $billingState = null;
                        }

                        $billingAddress['prefix'] = isset($ordersHeaders[$importOrdersConfigFields['billing_prefix']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_prefix']]] : '';
                        $billingAddress['firstname'] = isset($ordersHeaders[$importOrdersConfigFields['billing_firstname']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_firstname']]] : '';
                        $billingAddress['lastname'] = isset($ordersHeaders[$importOrdersConfigFields['billing_lastname']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_lastname']]] : '';
                        $billingAddress['company'] = isset($ordersHeaders[$importOrdersConfigFields['billing_company']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_company']]] : '';
                        $billingAddress['email'] = isset($ordersHeaders[$importOrdersConfigFields['billing_email']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_email']]] : '';
                        $billingAddress['phonecountrycode'] = isset($ordersHeaders[$importOrdersConfigFields['billing_phonecountrycode']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_phonecountrycode']]] : '';
                        $billingAddress['phone_country_code_value'] = isset($ordersHeaders[$importOrdersConfigFields['billing_phone_country_code_value']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_phone_country_code_value']]] : '';
                        $billingAddress['phone'] = isset($ordersHeaders[$importOrdersConfigFields['billing_phone']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_phone']]] : '';
                        $billingAddress['mobilecountrycode'] = isset($ordersHeaders[$importOrdersConfigFields['billing_mobilecountrycode']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_mobilecountrycode']]] : '';
                        $billingAddress['mobile_country_code_value'] = isset($ordersHeaders[$importOrdersConfigFields['billing_mobile_country_code_value']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_mobile_country_code_value']]] : '';
                        $billingAddress['mobile'] = isset($ordersHeaders[$importOrdersConfigFields['billing_mobile']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_mobile']]] : '';
                        $billingAddress['country'] = $billingCountry;
                        $billingAddress['city'] = isset($ordersHeaders[$importOrdersConfigFields['billing_city']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_city']]] : '';
                        $billingAddress['state'] = $billingState;
                        $billingAddress['zip'] = isset($ordersHeaders[$importOrdersConfigFields['billing_zip']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_zip']]] : '';
                        $billingAddress['address1'] = isset($ordersHeaders[$importOrdersConfigFields['billing_address1']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_address1']]] : '';
                        $billingAddress['address2'] = isset($ordersHeaders[$importOrdersConfigFields['billing_address2']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['billing_address2']]] : '';
                        $billingAddressId = Tools_ExportImportOrders::addOrderAddress(
                            $userId,
                            $billingAddress,
                            Models_Model_Customer::ADDRESS_TYPE_BILLING
                        );
                    }

                    $status = isset($ordersHeaders[$importOrdersConfigFields['status']]) ? $orderData[$ordersHeaders[$importOrdersConfigFields['status']]] : $defaultOrderStatus;
                    //new version of processing cart session content
                    if (isset($ordersHeaders[$importOrdersConfigFields['sub_total']]) && is_numeric(
                        $orderData[$ordersHeaders[$importOrdersConfigFields['sub_total']]]
                    )
                    ) {
                        $subTotal = $orderData[$ordersHeaders[$importOrdersConfigFields['sub_total']]];
                    }
                    if (isset($ordersHeaders[$importOrdersConfigFields['total']]) && is_numeric(
                        $orderData[$ordersHeaders[$importOrdersConfigFields['total']]]
                    )
                    ) {
                        $total = $orderData[$ordersHeaders[$importOrdersConfigFields['total']]];
                    } else {
                        $total = $subTotal + $shippingPrice + $discountTax + $subTotalTax;
                    }

                    $data = array(
                        'ip_address' => '',
                        'referer' => '',
                        'user_id' => $userId,
                        'status' => $status,
                        'gateway' => $gateway,
                        'shipping_address_id' => $shippingAddressId,
                        'billing_address_id' => $billingAddressId,
                        'shipping_price' => $shippingPrice,
                        'shipping_type' => $shippingType,
                        'shipping_service' => $shippingService,
                        'shipping_tracking_code' => $shippingTrackingCode,
                        'sub_total' => $subTotal,
                        'total_tax' => is_numeric($totalTax) ? $totalTax : 0,
                        'total' => $total,
                        'notes' => $notes,
                        'discount' => is_numeric($discount) ? $discount : 0,
                        'shipping_tax' => is_numeric($shippingTax) ? $shippingTax : 0,
                        'discount_tax' => is_numeric($discountTax) ? $discountTax : 0,
                        'sub_total_tax' => is_numeric($subTotalTax) ? $subTotalTax : 0,
                        'discount_tax_rate' => 0,
                        'created_at' => $date,
                        'updated_at' => $date
                    );

                    $newId = $cartSessionMapper->getDbTable()->insert($data);

                    foreach ($cartContent as $content) {
                        $taxPrice = isset($content['tax_price']) ? $content['tax_price'] : $content['price'];
                        $freebies = 0;
                        array_push(
                            $importedContentData,
                            $newId,
                            $content['product_id'],
                            null,
                            $content['price'],
                            $content['qty'],
                            $content['tax'],
                            $taxPrice,
                            $freebies
                        );
                    }
                    $importOrdersErrors[] = array($orderImportId, '', '', '', '', '', '');
                    array_push($importedOrdersIds, $orderImportId);
                    array_push($importedOrdersData, $newId, $orderImportId, date(DATE_ATOM));
                }
            }

            if (!empty($importedOrdersData)) {
                $values = implode(',', array_fill(0, count($importedOrdersData) / 3, '(?, ?, ?)'));
                $importOrdersStmt = $importOrderDbTable->getAdapter()
                    ->prepare(
                        'INSERT INTO shopping_import_orders (real_order_id, import_order_id, created_at) VALUES ' . $values . ''
                    );
                $importOrdersStmt->execute($importedOrdersData);
            }

            if ($importedContentData) {
                $contentValues = implode(
                    ',',
                    array_fill(0, count($importedContentData) / 8, '(?, ?, ?, ?, ?, ?, ?, ?)')
                );
                $importContentStmt = $cartSessionContentDbTable->getAdapter()
                    ->prepare(
                        'INSERT INTO shopping_cart_session_content (cart_id, product_id, options, price, qty, tax, tax_price, freebies) VALUES ' . $contentValues . ''
                    );
                $importContentStmt->execute($importedContentData);
            }
            fclose($ordersCsvFile);
            return array(
                'error' => $importHasError,
                'importErrorsIds' => $importOrdersErrors,
                'importedOrdersIds' => $importedOrdersIds
            );

        }
        fclose($ordersCsvFile);
        return array('error' => true, 'errorMessage' => 'Format error');
    }


    public static function getDefaultOrderExportConfig()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        return array(
            'order_id' => array(
                'label' => 'order_id',
                'checked' => 1,
                'label_name' => $translator->translate('Order Id')
            ),
            'updated_at' => array(
                'label' => 'updated_at',
                'checked' => 1,
                'label_name' => $translator->translate('Updated At (Last order date)')
            ),
            'status' => array(
                'label' => 'status',
                'checked' => 1,
                'label_name' => $translator->translate('Status')
            ),
            'status_label' => array(
                'label' => 'status_label',
                'checked' => 1,
                'label_name' => $translator->translate('Status Label')
            ),
            'total_products' => array(
                'label' => 'total_products',
                'checked' => 1,
                'label_name' => $translator->translate('Total Products')
            ),
            'sku' => array('label' => 'sku', 'checked' => 1, 'label_name' => $translator->translate('Sku')),
            'mpn' => array('label' => 'mpn', 'checked' => 1, 'label_name' => $translator->translate('Mpn')),
            'product_name' => array(
                'label' => 'product_name',
                'checked' => 1,
                'label_name' => $translator->translate('Product name')
            ),
            'product_price' => array(
                'label' => 'product_price',
                'checked' => 1,
                'label_name' => $translator->translate('Product price')
            ),
            'product_tax' => array(
                'label' => 'product_tax',
                'checked' => 1,
                'label_name' => $translator->translate('Product tax')
            ),
            'product_tax_price' => array(
                'label' => 'product_tax_price',
                'checked' => 1,
                'label_name' => $translator->translate('Price inclusive taxes')
            ),
            'product_qty' => array(
                'label' => 'product_qty',
                'checked' => 1,
                'label_name' => $translator->translate('Product quantity')
            ),
            'shipping_type' => array(
                'label' => 'shipping_type',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping type')
            ),
            'shipping_service' => array(
                'label' => 'shipping_service',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping service')
            ),
            'gateway' => array(
                'label' => 'gateway',
                'checked' => 1,
                'label_name' => $translator->translate('Gateway')
            ),
            'shipping_price' => array(
                'label' => 'shipping_price',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping price')
            ),
            'discount_tax_rate' => array(
                'label' => 'discount_tax_rate',
                'checked' => 1,
                'label_name' => $translator->translate('Discount tax rate')
            ),
            'sub_total' => array(
                'label' => 'sub_total',
                'checked' => 1,
                'label_name' => $translator->translate('Sub total')
            ),
            'shipping_tax' => array(
                'label' => 'shipping_tax',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping tax')
            ),
            'discount_tax' => array(
                'label' => 'discount_tax',
                'checked' => 1,
                'label_name' => $translator->translate('Discount tax')
            ),
            'sub_total_tax' => array(
                'label' => 'sub_total_tax',
                'checked' => 1,
                'label_name' => $translator->translate('Sub total tax')
            ),
            'total_tax' => array(
                'label' => 'total_tax',
                'checked' => 1,
                'label_name' => $translator->translate('Total tax')
            ),
            'discount' => array(
                'label' => 'discount',
                'checked' => 1,
                'label_name' => $translator->translate('Discount')
            ),
            'total' => array(
                'label' => 'total',
                'checked' => 1,
                'label_name' => $translator->translate('Total')
            ),
            'notes' => array(
                'label' => 'notes',
                'checked' => 1,
                'label_name' => $translator->translate('Notes')
            ),
            'additional_info' => array(
                'label' => 'additional_info',
                'checked' => 1,
                'label_name' => $translator->translate('Additional info')
            ),
            'order_subtype' => array(
                'label' => 'order_subtype',
                'checked' => 1,
                'label_name' => $translator->translate('Order subtype')
            ),
            'shipping_tracking_code' => array(
                'label' => 'shipping_tracking_code',
                'checked' => 1,
                'label_name' => $translator->translate('Tracking code')
            ),
            'brand' => array(
                'label' => 'brand',
                'checked' => 1,
                'label_name' => $translator->translate('Brand')
            ),
            'user_prefix' => array(
                'label' => 'user_prefix',
                'checked' => 1,
                'label_name' => $translator->translate('Prefix')
            ),
            'user_name' => array(
                'label' => 'user_name',
                'checked' => 1,
                'label_name' => $translator->translate('User Name')
            ),
            'user_email' => array(
                'label' => 'user_email',
                'checked' => 1,
                'label_name' => $translator->translate('User Email')
            ),
            'shipping_prefix' => array(
                'label' => 'shipping_prefix',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping user prefix')
            ),
            'shipping_firstname' => array(
                'label' => 'shipping_firstname',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping first name')
            ),
            'shipping_lastname' => array(
                'label' => 'shipping_lastname',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping last name')
            ),
            'shipping_company' => array(
                'label' => 'shipping_company',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping company')
            ),
            'shipping_email' => array(
                'label' => 'shipping_email',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping email')
            ),
            'shipping_phonecountrycode' => array(
                'label' => 'shipping_phonecountrycode',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping phone country code')
            ),
            'shipping_phone_country_code_value' => array(
                'label' => 'shipping_phone_country_code_value',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping phone country code value')
            ),
            'shipping_phone' => array(
                'label' => 'shipping_phone',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping phone')
            ),
            'shipping_phone_full' => array(
                'label' => 'shipping_phone_full',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping phone full')
            ),
            'shipping_mobilecountrycode' => array(
                'label' => 'shipping_mobilecountrycode',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping mobile country code')
            ),
            'shipping_mobile_country_code_value' => array(
                'label' => 'shipping_mobile_country_code_value',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping mobile country code value')
            ),
            'shipping_mobile' => array(
                'label' => 'shipping_mobile',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping mobile')
            ),
            'shipping_mobile_full' => array(
                'label' => 'shipping_mobile_full',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping mobile full')
            ),
            'shipping_country' => array(
                'label' => 'shipping_country',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping country')
            ),
            'shipping_city' => array(
                'label' => 'shipping_city',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping city')
            ),
            'shipping_state' => array(
                'label' => 'shipping_state',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping state')
            ),
            'shipping_zip' => array(
                'label' => 'shipping_zip',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping zip')
            ),
            'shipping_address1' => array(
                'label' => 'shipping_address1',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping address 1')
            ),
            'shipping_address2' => array(
                'label' => 'shipping_address2',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping address 2')
            ),
            'billing_prefix' => array(
                'label' => 'billing_prefix',
                'checked' => 1,
                'label_name' => $translator->translate('Billing user prefix')
            ),
            'billing_firstname' => array(
                'label' => 'billing_firstname',
                'checked' => 1,
                'label_name' => $translator->translate('Billing first name')
            ),
            'billing_lastname' => array(
                'label' => 'billing_lastname',
                'checked' => 1,
                'label_name' => $translator->translate('Billing last name')
            ),
            'billing_company' => array(
                'label' => 'billing_company',
                'checked' => 1,
                'label_name' => $translator->translate('Billing company')
            ),
            'billing_email' => array(
                'label' => 'billing_email',
                'checked' => 1,
                'label_name' => $translator->translate('Billing email')
            ),
            'billing_phonecountrycode' => array(
                'label' => 'billing_phonecountrycode',
                'checked' => 1,
                'label_name' => $translator->translate('Billing phone country code')
            ),
            'billing_phone_country_code_value' => array(
                'label' => 'billing_phone_country_code_value',
                'checked' => 1,
                'label_name' => $translator->translate('Billing phone country code value')
            ),
            'billing_phone' => array(
                'label' => 'billing_phone',
                'checked' => 1,
                'label_name' => $translator->translate('Billing phone')
            ),
            'billing_phone_full' => array(
                'label' => 'billing_phone_full',
                'checked' => 1,
                'label_name' => $translator->translate('Billing phone full')
            ),
            'billing_mobilecountrycode' => array(
                'label' => 'billing_mobilecountrycode',
                'checked' => 1,
                'label_name' => $translator->translate('Billing mobile country code')
            ),
            'billing_mobile_country_code_value' => array(
                'label' => 'billing_mobile_country_code_value',
                'checked' => 1,
                'label_name' => $translator->translate('Billing mobile country code value')
            ),
            'billing_mobile' => array(
                'label' => 'billing_mobile',
                'checked' => 1,
                'label_name' => $translator->translate('Billing mobile')
            ),
            'billing_mobile_full' => array(
                'label' => 'billing_mobile_full',
                'checked' => 1,
                'label_name' => $translator->translate('Billing mobile full')
            ),
            'billing_country' => array(
                'label' => 'billing_country',
                'checked' => 1,
                'label_name' => $translator->translate('Billing country')
            ),
            'billing_city' => array(
                'label' => 'billing_city',
                'checked' => 1,
                'label_name' => $translator->translate('Billing city')
            ),
            'billing_state' => array(
                'label' => 'billing_state',
                'checked' => 1,
                'label_name' => $translator->translate('Billing state')
            ),
            'billing_zip' => array(
                'label' => 'billing_zip',
                'checked' => 1,
                'label_name' => $translator->translate('Billing zip')
            ),
            'billing_address1' => array(
                'label' => 'billing_address1',
                'checked' => 1,
                'label_name' => $translator->translate('Billing address 1')
            ),
            'billing_address2' => array(
                'label' => 'billing_address2',
                'checked' => 1,
                'label_name' => $translator->translate('Billing address 2')
            ),
            'coupon_code' => array(
                'label' => 'coupon_code',
                'checked' => 1,
                'label_name' => $translator->translate('Coupon code')
            ),
            'groupName' => array(
                'label' => 'groupName',
                'checked' => 1,
                'label_name' => $translator->translate('Client group')
            ),
            'is_gift' => array(
                'label' => 'is_gift',
                'checked' => 1,
                'label_name' => $translator->translate('Is gift')
            ),
            'gift_email' => array(
                'label' => 'gift_email',
                'checked' => 1,
                'label_name' => $translator->translate('Gift email')
            )
        );
    }

    public static function getSampleOrdersData()
    {
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $headers = Tools_ExportImportOrders::getOrderImportFieldsNames();
        $ordersSampleData[] = array(
            '245',
            '2013-10-29 13:43:23',
            'completed',
            'DIAG01ASF,DIAG40BCX',
            '15.00,112.47',
            '5.00,0.00',
            '2,1',
            'Colissimo Suivi 48h',
            'flatrateshipping',
            'paypal',
            '0',
            '0',
            '142.47',
            '0',
            '0',
            '0',
            '10.00',
            '0',
            '152.47',
            'some info from customer',
            'https://tools.usps.com/go/TrackConfirmAction_input?origTrackNum=12333',
            'Mr',
            'Jon Doe',
            'jondoe@gmail.com',
            'Mr',
            'Jon',
            'Doe',
            'Joe company',
            'jondoe@gmail.com',
            'US',
            '+1',
            '18002221222',
            'US',
            '+1',
            '18002221222',
            'US',
            'CALIFORNIA CITY',
            'CA',
            '93505',
            '1156 High Street',
            '',
            'Mr',
            'Jon',
            'Doe',
            'Joe company',
            'jondoe@gmail.com',
            'US',
            '+1',
            '18002221222',
            'US',
            '+1',
            '18002221222',
            'US',
            'CALIFORNIA CITY',
            'CA',
            '93505',
            '1156 High Street',
            ''
        );
        $fileName = 'ordersSample.' . date("Y-m-d", time()) . '.csv';
        $filePath = $websiteHelper->getPath() . $websiteHelper->getTmp() . $fileName;
        $expFile = fopen($filePath, 'w');
        fputcsv($expFile, $headers, ',', '"');
        foreach ($ordersSampleData as $data) {
            fputcsv($expFile, $data, ',', '"');
        }
        fclose($expFile);
        Tools_ExportImportOrders::downloadCsv($filePath, $fileName);
    }

    public static function downloadCsv($filePath, $fileName)
    {
        $response = Zend_Controller_Front::getInstance()->getResponse();
        $ordersArchive = Tools_System_Tools::zip($filePath, $fileName);
        $response->setHeader(
            'Content-Disposition',
            'attachment; filename=' . Tools_Filesystem_Tools::basename($ordersArchive)
        )
            ->setHeader('Content-type', 'application/force-download');
        readfile($ordersArchive);
        $response->sendResponse();
        exit;
    }

}
