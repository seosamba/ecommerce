<?php
/**
 * Class Tools_ExportImportOrders
 */
class Tools_ExportImportOrders
{

    public static function createOrders($ordersData, $switchSku = false)
    {
        $importOrdersErrors = array();
        $importedOrdersIds = array();
        $importedContentData = array();
        $importedOrdersData = array();
        $importHasError = false;
        $ordersImportData = $ordersData['data'];
        $ordersHeaders = $ordersData['headers'];
        $userMapper = Application_Model_Mappers_UserMapper::getInstance();
        $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();
        $productMapper = Models_Mapper_ProductMapper::getInstance();
        $cartSessionContentDbTable = new Models_DbTable_CartSessionContent();
        $userModel = new Application_Model_Models_User();
        $countries = Tools_Geo::getCountries(true);
        $states = Tools_Geo::getState(null, true);
        $importOrderDbTable = new Store_DbTable_ImportOrder();
        $emailValidate = new Zend_Validate_EmailAddress();
        $productBySkuOrMpn = 'sku';
        if ($switchSku) {
            $productBySkuOrMpn = 'mpn';
        }
        $existingProducts = $productMapper->getDbTable()->getAdapter()->fetchAssoc(
            $productMapper->getDbTable()->getAdapter()->select()->from(
                'shopping_product',
                array($productBySkuOrMpn, 'id')
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

        foreach ($ordersImportData as $orderData) {
            $userEmail = $orderData[$ordersHeaders['user_email']];
            $orderImportId = $orderData[$ordersHeaders['order_id']];
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
                $userModel->setEmail($orderData[$ordersHeaders['user_email']]);
                $userModel->setFullName($orderData[$ordersHeaders['user_name']]);
                $userModel->setPassword(microtime());
                $userModel->setRoleId(Shopping::ROLE_CUSTOMER);
                $userId = $userMapper->save($userModel);
            } else {
                $userId = $existingUsers[$userEmail]['id'];
            }

            $cartContent = array();

            $orderProductSku = explode(',', $orderData[$ordersHeaders[$productBySkuOrMpn]]);
            $orderProductPrice = explode(',', $orderData[$ordersHeaders['product_price']]);
            $orderProductQty = explode(',', $orderData[$ordersHeaders['product_qty']]);
            $orderProductTax = explode(',', $orderData[$ordersHeaders['product_tax']]);
            $skuQuantity = count($orderProductSku);
            if ($skuQuantity !== count($orderProductPrice) || $skuQuantity !== count($orderProductQty)
                || $skuQuantity !== count($orderProductTax)
            ) {
                $importOrdersErrors[] = array($orderImportId, '', '', '+', '', '', '');
                $importHasError = true;
                continue;
            }

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
                $cartContent[$key]['price'] = is_numeric($orderProductPrice[$key]) ? $orderProductPrice[$key] : 0;
                $cartContent[$key]['qty'] = intval($orderProductQty[$key]);
                $cartContent[$key]['tax'] = is_numeric($orderProductTax[$key]) ? $orderProductTax[$key]: 0;
                $cartContent[$key]['tax_price'] = $cartContent[$key]['price'] + $cartContent[$key]['tax'];
            }

            if (!empty($cartContent)) {
                $date = $orderData[$ordersHeaders['updated_at']];
                $notes = isset($ordersHeaders['notes']) ? $orderData[$ordersHeaders['notes']] : '';
                $gateway = isset($ordersHeaders['gateway']) ? $orderData[$ordersHeaders['gateway']] : '';
                $shippingPrice = isset($ordersHeaders['shipping_price']) ? $orderData[$ordersHeaders['shipping_price']] : 0;
                $discountTax = isset($ordersHeaders['discount_tax']) ? $orderData[$ordersHeaders['discount_tax']] : 0;
                $subTotalTax = isset($ordersHeaders['sub_total_tax']) ? $orderData[$ordersHeaders['sub_total_tax']] : 0;
                $discount = isset($ordersHeaders['discount']) ? $orderData[$ordersHeaders['discount']] : 0;
                $shippingType = isset($ordersHeaders['shipping_type']) ? $orderData[$ordersHeaders['shipping_type']] : '';
                $shippingService = isset($ordersHeaders['shipping_service']) ? $orderData[$ordersHeaders['shipping_service']] : '';
                $shippingTrackingId = isset($ordersHeaders['shipping_tracking_id']) ? $orderData[$ordersHeaders['shipping_tracking_id']] : '';
                $shippingTax = isset($ordersHeaders['shipping_tax']) ? $orderData[$ordersHeaders['shipping_tax']] : 0;
                $totalTax = isset($ordersHeaders['total_tax']) ? $orderData[$ordersHeaders['total_tax']] : 0;

                $shippingFirstName = isset($ordersHeaders['shipping_firstname']) ? $orderData[$ordersHeaders['shipping_firstname']] : '';
                $shippingAddressId = null;
                if ($shippingFirstName !== '') {
                    $shippingAddress = array();
                    //TODO states and countries identification
                    $shippingCountry = isset($ordersHeaders['shipping_country']) ? $orderData[$ordersHeaders['shipping_country']] : null;
                    if (!array_key_exists(
                        $shippingCountry,
                        $countries
                    ) && $shippingCountry !== '' && $shippingCountry !== null
                    ) {
                        $importOrdersErrors[] = array($orderImportId, '', '', '', '', '', '+');
                        $importHasError = true;
                        break;
                    } elseif (trim($shippingCountry) === '') {
                        $shippingCountry = null;
                    }
                    $shippingState = isset($ordersHeaders['shipping_state']) ? $orderData[$ordersHeaders['shipping_state']] : '';
                    $shippingState = array_search($shippingState, $states);
                    if (!$shippingState) {
                        $shippingState = null;
                    }

                    $shippingAddress['firstname'] = isset($ordersHeaders['shipping_firstname']) ? $orderData[$ordersHeaders['shipping_firstname']] : '';
                    $shippingAddress['lastname'] = isset($ordersHeaders['shipping_lastname']) ? $orderData[$ordersHeaders['shipping_lastname']] : '';
                    $shippingAddress['company'] = isset($ordersHeaders['shipping_company']) ? $orderData[$ordersHeaders['shipping_company']] : '';
                    $shippingAddress['email'] = isset($ordersHeaders['shipping_email']) ? $orderData[$ordersHeaders['shipping_email']] : '';
                    $shippingAddress['phone'] = isset($ordersHeaders['shipping_phone']) ? $orderData[$ordersHeaders['shipping_phone']] : '';
                    $shippingAddress['mobile'] = isset($ordersHeaders['shipping_mobile']) ? $orderData[$ordersHeaders['shipping_mobile']] : '';
                    $shippingAddress['country'] = $shippingCountry;
                    $shippingAddress['city'] = isset($ordersHeaders['shipping_city']) ? $orderData[$ordersHeaders['shipping_city']] : '';
                    $shippingAddress['state'] = $shippingState;
                    $shippingAddress['zip'] = isset($ordersHeaders['shipping_zip']) ? $orderData[$ordersHeaders['shipping_zip']] : '';
                    $shippingAddress['address1'] = isset($ordersHeaders['shipping_address1']) ? $orderData[$ordersHeaders['shipping_address1']] : '';
                    $shippingAddress['address2'] = isset($ordersHeaders['shipping_address2']) ? $orderData[$ordersHeaders['shipping_address2']] : '';
                    $shippingAddressId = Tools_ExportImportOrders::addOrderAddress(
                        $userId,
                        $shippingAddress,
                        Models_Model_Customer::ADDRESS_TYPE_SHIPPING
                    );
                }

                $billingFirstName = isset($ordersHeaders['billing_firstname']) ? $orderData[$ordersHeaders['billing_firstname']] : '';
                $billingAddressId = null;
                if ($billingFirstName !== '') {
                    $billingAddress = array();
                    //TODO states and countries identification
                    $billingCountry = isset($ordersHeaders['billing_country']) ? $orderData[$ordersHeaders['billing_country']] : null;
                    if (!array_key_exists($billingCountry, $countries) && $billingCountry !== null && trim(
                        $billingCountry
                    ) !== ''
                    ) {
                        $importOrdersErrors[] = array($orderImportId, '', '', '', '', '', '+');
                        $importHasError = true;
                        break;
                    } elseif (trim($billingCountry) === '') {
                        $billingCountry = null;
                    }

                    $billingState = isset($ordersHeaders['billing_state']) ? $orderData[$ordersHeaders['billing_state']] : '';
                    $billingState = array_search($billingState, $states);
                    if (!$billingState) {
                        $billingState = null;
                    }

                    $billingAddress['firstname'] = isset($ordersHeaders['billing_firstname']) ? $orderData[$ordersHeaders['billing_firstname']] : '';
                    $billingAddress['lastname'] = isset($ordersHeaders['billing_lastname']) ? $orderData[$ordersHeaders['billing_lastname']] : '';
                    $billingAddress['company'] = isset($ordersHeaders['billing_company']) ? $orderData[$ordersHeaders['billing_company']] : '';
                    $billingAddress['email'] = isset($ordersHeaders['billing_email']) ? $orderData[$ordersHeaders['billing_email']] : '';
                    $billingAddress['phone'] = isset($ordersHeaders['billing_phone']) ? $orderData[$ordersHeaders['billing_phone']] : '';
                    $billingAddress['mobile'] = isset($ordersHeaders['billing_mobile']) ? $orderData[$ordersHeaders['billing_mobile']] : '';
                    $billingAddress['country'] = $billingCountry;
                    $billingAddress['city'] = isset($ordersHeaders['billing_city']) ? $orderData[$ordersHeaders['billing_city']] : '';
                    $billingAddress['state'] = $billingState;
                    $billingAddress['zip'] = isset($ordersHeaders['billing_zip']) ? $orderData[$ordersHeaders['billing_zip']] : '';
                    $billingAddress['address1'] = isset($ordersHeaders['billing_address1']) ? $orderData[$ordersHeaders['billing_address1']] : '';
                    $billingAddress['address2'] = isset($ordersHeaders['billing_address2']) ? $orderData[$ordersHeaders['billing_address2']] : '';
                    $billingAddressId = Tools_ExportImportOrders::addOrderAddress(
                        $userId,
                        $billingAddress,
                        Models_Model_Customer::ADDRESS_TYPE_BILLING
                    );
                }

                //new version of processing cart session content
                $data = array(
                    'ip_address' => '',
                    'referer' => '',
                    'user_id' => $userId,
                    'status' => $orderData[$ordersHeaders['status']],
                    'gateway' => $gateway,
                    'shipping_address_id' => $shippingAddressId,
                    'billing_address_id' => $billingAddressId,
                    'shipping_price' => $shippingPrice,
                    'shipping_type' => $shippingType,
                    'shipping_service' => $shippingService,
                    'shipping_tracking_id' => $shippingTrackingId,
                    'sub_total' => is_numeric($orderData[$ordersHeaders['sub_total']]) ? $orderData[$ordersHeaders['sub_total']] : 0,
                    'total_tax' => is_numeric($totalTax) ? $totalTax : 0,
                    'total' => is_numeric($orderData[$ordersHeaders['total']]) ? $orderData[$ordersHeaders['total']] : 0,
                    'notes' => $notes,
                    'discount' => is_numeric($discount) ? $discount : 0,
                    'shipping_tax' => is_numeric($shippingTax) ? $shippingTax : 0,
                    'discount_tax' => is_numeric($discountTax) ? $discountTax : 0,
                    'sub_total_tax' => is_numeric($subTotalTax) ? $subTotalTax: 0,
                    'discount_tax_rate' => 0,
                    'created_at' => $date,
                    'updated_at' => $date
                );

                $newId = $cartSessionMapper->getDbTable()->insert($data);

                foreach ($cartContent as $content) {
                    $taxPrice = isset($content['tax_price']) ? $content['tax_price'] : $content['price'];
                    $freebies = is_null($content['freebies']) ? 0 : $content['freebies'];
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
            $contentValues = implode(',', array_fill(0, count($importedContentData) / 8, '(?, ?, ?, ?, ?, ?, ?, ?)'));
            $importContentStmt = $cartSessionContentDbTable->getAdapter()
                ->prepare(
                    'INSERT INTO shopping_cart_session_content (cart_id, product_id, options, price, qty, tax, tax_price, freebies) VALUES ' . $contentValues . ''
                );
            $importContentStmt->execute($importedContentData);
        }
        return array('error' => $importHasError, 'importErrorsIds' => $importOrdersErrors, 'importedOrdersIds' => $importedOrdersIds);
    }

    public static function prepareOrdersDataForExport($data, $exportAllOrders, $ordersIds)
    {
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $response = Zend_Controller_Front::getInstance()->getResponse();
        unset($data['name']);
        unset($data['run']);
        unset($data['orderIds']);
        unset($data['controller']);
        unset($data['action']);
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
        if (intval($exportAllOrders) === 1) {
            $dataToExport = Models_Mapper_OrdersMapper::getInstance()->fetchOrdersForExport(
                array(),
                $excludeFields
            );
        } else {
            $dataToExport = Models_Mapper_OrdersMapper::getInstance()->fetchOrdersForExport(
                $ordersIds,
                $excludeFields
            );
        }

        if (!empty($dataToExport)) {
            $headers[] = $renamedFields;
            $fileName = 'orderlist.' . date("Y-m-d", time()) . '.csv';
            $filePath = $websiteHelper->getPath() . $websiteHelper->getTmp() . $fileName;
            $expFile = fopen($filePath, 'w');
            $dataToExport = array_merge($headers, $dataToExport);
            foreach ($dataToExport as $data) {
                fputcsv($expFile, $data, ',', '"');
            }
            fclose($expFile);
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

    public static function prepareImportOrdersReport($importErrors)
    {
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $response = Zend_Controller_Front::getInstance()->getResponse();
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

    public static function createOrdersCsv($ordersCsv)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $ordersCsvFile = fopen($ordersCsv['file']['tmp_name'], 'r');
        $minimumRequiredFields = array(
            'order_id',
            'updated_at',
            'status',
            'product_qty',
            'sku',
            'mpn',
            'product_price',
            'product_tax',
            'sub_total',
            'total',
            'user_name',
            'user_email',
            'shipping_firstname'
        );
        if ($ordersCsv !== false) {
            while (($data = fgetcsv($ordersCsvFile, ',')) !== false) {
                $parsedCsv[] = $data;
            }
            fclose($ordersCsvFile);
            if (!empty ($parsedCsv)) {
                $headers = array_shift($parsedCsv);
                $headers = array_flip(array_map('strtolower', $headers));
                $requiredFields = array_diff_key(array_flip($minimumRequiredFields), $headers);
                if (!empty($requiredFields)) {
                    $errorMessage = '';
                    foreach ($requiredFields as $fieldMissed => $key) {
                        $errorMessage .= $fieldMissed . '<br />';
                    }
                    return array(
                        'error' => 1,
                        'errorMessage' => $translator->translate('Required fields missed') . '<br />' . $errorMessage
                    );
                }
                return array('error' => '0', 'data' => $parsedCsv, 'headers' => $headers);

            } else {
                return array('error' => 1, 'errorMessage' => $translator->translate('Csv is empty'));
            }
        } else {
            return array('error' => 1, 'errorMessage' => $translator->translate('Error during reading csv'));
        }
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
                'label_name' => $translator->translate('Updated At')
            ),
            'status' => array(
                'label' => 'status',
                'checked' => 1,
                'label_name' => $translator->translate('Status')
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
                'label_name' => $translator->translate('Product tax price')
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
            'shipping_tracking_id' => array(
                'label' => 'shipping_tracking_id',
                'checked' => 1,
                'label_name' => $translator->translate('Tracking id')
            ),
            'brand' => array(
                'label' => 'brand',
                'checked' => 1,
                'label_name' => $translator->translate('Brand')
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
            'shipping_phone' => array(
                'label' => 'shipping_phone',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping phone')
            ),
            'shipping_mobile' => array(
                'label' => 'shipping_mobile',
                'checked' => 1,
                'label_name' => $translator->translate('Shipping mobile')
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
            'billing_phone' => array(
                'label' => 'billing_phone',
                'checked' => 1,
                'label_name' => $translator->translate('Billing phone')
            ),
            'billing_mobile' => array(
                'label' => 'billing_mobile',
                'checked' => 1,
                'label_name' => $translator->translate('Billing mobile')
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
            )
        );
    }

    public static function getSampleOrdersData()
    {
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $response = Zend_Controller_Front::getInstance()->getResponse();
        $headers = array(
            'order_id',
            'updated_at',
            'status',
            'sku',
            'mpn',
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
            'shipping_tracking_id',
            'user_name',
            'user_email',
            'shipping_firstname',
            'shipping_lastname',
            'shipping_company',
            'shipping_email',
            'shipping_phone',
            'shipping_mobile',
            'shipping_country',
            'shipping_city',
            'shipping_state',
            'shipping_zip',
            'shipping_address1',
            'shipping_address2',
            'billing_firstname',
            'billing_lastname',
            'billing_company',
            'billing_email',
            'billing_phone',
            'billing_mobile',
            'billing_country',
            'billing_city',
            'billing_state',
            'billing_zip',
            'billing_address1',
            'billing_address2'
        );
        $ordersSampleData[] = array(
            '245',
            '2013-10-29 13:43:23',
            'completed',
            'DIAG01ASF,DIAG40BCX',
            '3432863008003,3432863006894',
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
            'Jon Doe',
            'jondoe@gmail.com',
            'Jon',
            'Doe',
            'Joe company',
            'jondoe@gmail.com',
            '18002221222',
            '18002221222',
            'US',
            'CALIFORNIA CITY',
            'CA',
            '93505',
            '1156 High Street',
            '',
            'Jon',
            'Doe',
            'Joe company',
            'jondoe@gmail.com',
            '18002221222',
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
