<?php

/**
 * Misc
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_Misc
{

    /**
     * Localized list of names for currencies
     */
    const KEY_CURRENCY_LIST = 'currency_list';

    const SECTION_STORE_MANAGEZONES = 'zones';

    const SECTION_STORE_TAXES = 'taxes';

    const SECTION_STORE_CONFIG = 'storeconfig';

    const SECTION_STORE_SHIPPINGCONFIG = 'shippingconfig';

    const SECTION_STORE_ADDEDITPRODUCT = 'addproduct';

    const SECTION_STORE_BRANDLOGOS = 'brandlogos';

    const SECTION_STORE_MERCHANDISING = 'merchandising';

    const SECTION_STORE_IMPORTORDERS = 'ordersimportconfig';

    const SECTION_STORE_MANAGELOCATION = 'pickupLocation';

    const CS_ALIAS_PENDING = 'new_quote';

    const CS_ALIAS_PROCESSING = 'quote_sent';

    const CS_ALIAS_LOST_OPPORTUNITY = 'lost_opportunity';

    const CS_ALIAS_QUOTE_SIGNED = 'quote_signed';

    const EXCHANGE_PATH = 'https://query.yahooapis.com/v1/public/yql?q=';

    const EXCHANGE_ADDITIONAL_PARAMS = '&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=';

    /**
     * Option for the client page
     */
    const OPTION_STORE_CLIENT_LOGIN = 'option_storeclientlogin';
    /**
     * Option for the page options system
     */
    const OPTION_THANKYOU = 'option_storethankyou';

    const THROTTLE_TRANSACTIONS_DEFAULT_MESSAGE = 'Due to unprecedented orders volume, and in order to maintain quality of service, our online shop is open for a limited amount of time every day. We are no longer accepting orders today, please try to come back earlier tomorrow to place your order. We apologize for the inconvenience.';

    /**
     * System shipping services without label generation
     */
    public static $systemShippingServices = array(
        'freeshipping',
        'pickup',
        'markup',
        'flatrateshipping',
        'flatrateship'
    );

    /*
     * Changes for name inc. Tax 
     * Put in array country abbr and name for change 'AU'=>'GST'
     */
    public static $_taxName = array('AU' => 'GST', 'FR' => 'TVA');

    public static $_weightUnits = array(
        'kg' => 'Kilogram (kg)',
        'lbs' => 'Pound (lbs)'
    );

    public static $_lengthUnits = array(
        'cm' => 'Centimetre (cm)',
        'inch' => 'Inch (inch)'
    );

    public static $_hoursUnits = array(
        '1' => '12:00 AM',
        '2' => '1:00 AM',
        '3' => '2:00 AM',
        '4' => '3:00 AM',
        '5' => '4:00 AM',
        '6' => '5:00 AM',
        '7' => '6:00 AM',
        '8' => '7:00 AM',
        '9' => '8:00 AM',
        '10' => '9:00 AM',
        '11' => '10:00 AM',
        '12' => '11:00 AM',
        '13' => '12:00 PM',
        '14' => '1:00 PM',
        '15' => '2:00 PM',
        '16' => '3:00 PM',
        '17' => '4:00 PM',
        '18' => '5:00 PM',
        '19' => '6:00 PM',
        '20' => '7:00 PM',
        '21' => '8:00 PM',
        '22' => '9:00 PM',
        '23' => '10:00 PM',
        '24' => '11:00 PM'
    );

    public static $_productConfigTabs = array(
        0 => array('tabId' => 'main-tab', 'tabName' => 'Main', 'type' => 'internal'),
        1 => array('tabId' => 'digital-product-tab', 'tabName' => 'Digital products', 'type' => 'internal'),
        2 => array('tabId' => 'tag-tab', 'tabName' => 'Product tags', 'type' => 'internal'),
        3 => array('tabId' => 'options-tab', 'tabName' => 'Product options', 'type' => 'internal'),
        4 => array('tabId' => 'related-tab', 'tabName' => 'Cross selling', 'type' => 'internal'),
        5 => array('tabId' => 'coupon-tab', 'tabName' => 'Coupons', 'type' => 'internal'),
        6 => array('tabId' => 'group-pricing-tab', 'tabName' => 'Groups', 'type' => 'internal')
    );

    public static $_merchandisingConfigTabs = array(
        0 => array('tabId' => 'coupons', 'tabName' => 'Coupons', 'type' => 'internal'),
        1 => array('tabId' => 'recurring-payments', 'tabName' => 'Recurring payments', 'type' => 'internal'),
        //2 => array('tabId' => 'group-pricing', 'tabName' => 'Customers/Leads groups', 'type' => 'internal'),
        2 => array('tabId' => 'group-pricing-tab', 'tabName' => 'Customers/Leads groups', 'type' => 'dynamic', 'settingActionPath' => 'configCustomerGroups'),
        3 => array('tabId' => 'user-attributes-assignment-rules', 'tabName' => 'Automated group assignment', 'type' => 'internal'),
        4 => array('tabId' => 'throttle-transactions', 'tabName' => 'Throttle transactions', 'type' => 'internal'),
    );


    public static $_helpHashMap = array(
        self::SECTION_STORE_MANAGEZONES => 'shopping-cart-shipping-tax-zones.html',
        self::SECTION_STORE_TAXES => 'shopping-cart-tax-calculation.html',
        self::SECTION_STORE_CONFIG => 'setup-online-shopping-cart.html',
        self::SECTION_STORE_SHIPPINGCONFIG => 'shopping-cart-shipping-calculator.html',
        self::SECTION_STORE_ADDEDITPRODUCT => 'ecommerce-cms.html',
        self::SECTION_STORE_BRANDLOGOS => 'e-commerce-product-brands.html',
        self::SECTION_STORE_MERCHANDISING => 'ecommerce-marketing.html',
        self::SECTION_STORE_IMPORTORDERS => 'import-orders.html',
        self::SECTION_STORE_MANAGELOCATION => 'multi-locations-ecommerce.html'
    );

    /**
     * @var array Supported currencies
     */
    public static $_currenciesFilter = array(
        "AED" => "United Arab Emirates Dirham",
        "AFN" => "Afghan Afghani",
        "ALL" => "Albanian Lek",
        "AMD" => "Armenian Dram",
        "ANG" => "Netherlands Antillean Guilder",
        "AOA" => "Angolan Kwanza",
        "ARS" => "Argentine Peso",
        "AUD" => "Australian Dollar",
        "AWG" => "Aruban Florin",
        "AZN" => "Azerbaijani Manat",
        "BAM" => "Bosnia-Herzegovina Convertible Mark",
        "BBD" => "Barbadian Dollar",
        "BDT" => "Bangladeshi Taka",
        "BGN" => "Bulgarian Lev",
        "BHD" => "Bahraini Dinar",
        "BIF" => "Burundian Franc",
        "BMD" => "Bermudan Dollar",
        "BND" => "Brunei Dollar",
        "BOB" => "Bolivian Boliviano",
        "BRL" => "Brazilian Real",
        "BSD" => "Bahamian Dollar",
        "BTN" => "Bhutanese Ngultrum",
        "BWP" => "Botswanan Pula",
        "BYR" => "Belarusian Ruble",
        "BZD" => "Belize Dollar",
        "CAD" => "Canadian Dollar",
        "CDF" => "Congolese Franc",
        "CHF" => "Swiss Franc",
        "CLF" => "Chilean Unit of Account (UF)",
        "CLP" => "Chilean Peso",
        "CNY" => "Chinese Yuan",
        "COP" => "Colombian Peso",
        "CRC" => "Costa Rican Colón",
        "CUP" => "Cuban Peso",
        "CVE" => "Cape Verdean Escudo",
        "CZK" => "Czech Republic Koruna",
        "DJF" => "Djiboutian Franc",
        "DKK" => "Danish Krone",
        "DOP" => "Dominican Peso",
        "DZD" => "Algerian Dinar",
        "EGP" => "Egyptian Pound",
        "ETB" => "Ethiopian Birr",
        "EUR" => "Euro",
        "FJD" => "Fijian Dollar",
        "FKP" => "Falkland Islands Pound",
        "GBP" => "British Pound Sterling",
        "GEL" => "Georgian Lari",
        "GHS" => "Ghanaian Cedi",
        "GIP" => "Gibraltar Pound",
        "GMD" => "Gambian Dalasi",
        "GNF" => "Guinean Franc",
        "GTQ" => "Guatemalan Quetzal",
        "GYD" => "Guyanaese Dollar",
        "HKD" => "Hong Kong Dollar",
        "HNL" => "Honduran Lempira",
        "HRK" => "Croatian Kuna",
        "HTG" => "Haitian Gourde",
        "HUF" => "Hungarian Forint",
        "IDR" => "Indonesian Rupiah",
        "IEP" => "Irish Pound",
        "ILS" => "Israeli New Sheqel",
        "INR" => "Indian Rupee",
        "IQD" => "Iraqi Dinar",
        "IRR" => "Iranian Rial",
        "ISK" => "Icelandic Króna",
        "JMD" => "Jamaican Dollar",
        "JOD" => "Jordanian Dinar",
        "JPY" => "Japanese Yen",
        "KES" => "Kenyan Shilling",
        "KGS" => "Kyrgystani Som",
        "KHR" => "Cambodian Riel",
        "KMF" => "Comorian Franc",
        "KPW" => "North Korean Won",
        "KRW" => "South Korean Won",
        "KWD" => "Kuwaiti Dinar",
        "KZT" => "Kazakhstani Tenge",
        "LAK" => "Laotian Kip",
        "LBP" => "Lebanese Pound",
        "LKR" => "Sri Lankan Rupee",
        "LRD" => "Liberian Dollar",
        "LSL" => "Lesotho Loti",
        "LTL" => "Lithuanian Litas",
        "LVL" => "Latvian Lats",
        "LYD" => "Libyan Dinar",
        "MAD" => "Moroccan Dirham",
        "MDL" => "Moldovan Leu",
        "MGA" => "Malagasy Ariary",
        "MKD" => "Macedonian Denar",
        "MMK" => "Myanma Kyat",
        "MNT" => "Mongolian Tugrik",
        "MOP" => "Macanese Pataca",
        "MRO" => "Mauritanian Ouguiya",
        "MUR" => "Mauritian Rupee",
        "MVR" => "Maldivian Rufiyaa",
        "MWK" => "Malawian Kwacha",
        "MXN" => "Mexican Peso",
        "MYR" => "Malaysian Ringgit",
        "MZN" => "Mozambican Metical",
        "NAD" => "Namibian Dollar",
        "NGN" => "Nigerian Naira",
        "NIO" => "Nicaraguan Córdoba",
        "NOK" => "Norwegian Krone",
        "NPR" => "Nepalese Rupee",
        "NZD" => "New Zealand Dollar",
        "OMR" => "Omani Rial",
        "PAB" => "Panamanian Balboa",
        "PEN" => "Peruvian Nuevo Sol",
        "PGK" => "Papua New Guinean Kina",
        "PHP" => "Philippine Peso",
        "PKR" => "Pakistani Rupee",
        "PLN" => "Polish Zloty",
        "PYG" => "Paraguayan Guarani",
        "QAR" => "Qatari Rial",
        "RON" => "Romanian Leu",
        "RSD" => "Serbian Dinar",
        "RUB" => "Russian Ruble",
        "RWF" => "Rwandan Franc",
        "SAR" => "Saudi Riyal",
        "SBD" => "Solomon Islands Dollar",
        "SCR" => "Seychellois Rupee",
        "SDG" => "Sudanese Pound",
        "SEK" => "Swedish Krona",
        "SGD" => "Singapore Dollar",
        "SHP" => "Saint Helena Pound",
        "SLL" => "Sierra Leonean Leone",
        "SOS" => "Somali Shilling",
        "SRD" => "Surinamese Dollar",
        "STD" => "São Tomé and Príncipe Dobra",
        "SVC" => "Salvadoran Colón",
        "SYP" => "Syrian Pound",
        "SZL" => "Swazi Lilangeni",
        "THB" => "Thai Baht",
        "TJS" => "Tajikistani Somoni",
        "TMT" => "Turkmenistani Manat",
        "TND" => "Tunisian Dinar",
        "TOP" => "Tongan Paʻanga",
        "TRY" => "Turkish Lira",
        "TTD" => "Trinidad and Tobago Dollar",
        "TWD" => "New Taiwan Dollar",
        "TZS" => "Tanzanian Shilling",
        "UAH" => "Ukrainian Hryvnia",
        "UGX" => "Ugandan Shilling",
        "USD" => "United States Dollar",
        "UYU" => "Uruguayan Peso",
        "UZS" => "Uzbekistan Som",
        "VEF" => "Venezuelan Bolívar",
        "VND" => "Vietnamese Dong",
        "VUV" => "Vanuatu Vatu",
        "WST" => "Samoan Tala",
        "XAF" => "CFA Franc BEAC",
        "XCD" => "East Caribbean Dollar",
        "XDR" => "Special Drawing Rights",
        "XOF" => "CFA Franc BCEAO",
        "XPF" => "CFP Franc",
        "YER" => "Yemeni Rial",
        "ZAR" => "South African Rand",
        "ZMK" => "Zambian Kwacha",
        "ZWD" => "Zimbabwean Dollar (1980-2008)",
        "ZWL" => "Zimbabwean Dollar"
    );

    public static function getShippingPluginContent($shippingPlugin)
    {
        $className = ucfirst($shippingPlugin);
        $method = 'getConfigScreen';
        if (class_exists($className) && method_exists($className, $method)) {
            return preg_replace(
                '~name="([-_\w\s\d]+)([\[\]]{0,2})"~',
                'name="shippingExternal[$1]$2"',
                $className::$method()
            );
        }
    }

    public static function getCurrencyList()
    {
        $cacheHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
        if (($data = $cacheHelper->load(self::KEY_CURRENCY_LIST, 'store_')) === null) {
            $zendCurrenciesList = Zend_Locale::getTranslationList('NameToCurrency');
            $data = array();
            foreach (self::$_currenciesFilter as $currency => $name) {
                if (array_key_exists($currency, $zendCurrenciesList)) {
                    $currencySymbol = Zend_Locale::getTranslation($currency, 'CurrencySymbol');
                    $data[$currency] = ucwords(
                        $zendCurrenciesList[$currency]
                    ) . ($currencySymbol !== false ? ' (' . $currencySymbol . ')' : '');
                }
            }
            asort($data);
            $cacheHelper->save(
                self::KEY_CURRENCY_LIST,
                $data,
                'store_',
                array('locale'),
                Helpers_Action_Cache::CACHE_LONG
            );
        }
        return $data;
    }

    /**
     * Get current format for the currency such as decimal separator, thousand separator, symbol and format
     *
     * @return array
     */
    public static function getCurrencyFormat()
    {
        $currency = Zend_Registry::get('Zend_Currency');
        $format = strtr(
            $currency->toCurrency(0),
            array('0' => 'x', '.' => '', ',' => '', $currency->getSymbol() => '%s')
        );
        return array(
            'decimal' => preg_replace('/.*0([\.,])0.*/u', '$1', $currency->toCurrency(0)),
            'thousand' => preg_replace('/.*1(.?)000.*/u', '$1', $currency->toCurrency(1000)),
            'symbol' => $currency->getSymbol(),
            'format' => preg_replace('/x+/', '%v', $format),
            'precision' => 2
        );
    }

    public static function clenupAddress($address)
    {
        $_addressTmpl = array(
            'address_type' => '',
            'prefix' => '',
            'firstname' => '',
            'lastname' => '',
            'company' => '',
            'email' => '',
            'address1' => '',
            'address2' => '',
            'country' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'phone' => '',
            'mobile' => '',
            'mobilecountrycode' => '',
            'phonecountrycode' => '',
            'mobile_country_code_value' => '',
            'phone_country_code_value' => '',
            'customer_notes' => '',
            'position' => ''
        );

        $address = array_intersect_key($address, $_addressTmpl);
        ksort($address);
        return $address;
    }

    public static function getAddressUniqKey($address)
    {
        $address = self::clenupAddress($address);
        return md5(http_build_query($address));
    }

    public static function getDefaultProductOptions(Models_Model_Product $product)
    {
        $productOptions = $product->getDefaultOptions();
        if (!is_array($productOptions) || empty($productOptions)) {
            return array();
        }
        foreach ($productOptions as $option) {
            if (isset($option['selection']) && is_array($option['selection']) && !empty($option['selection'])) {
                $selections = $option['selection'];
                foreach ($selections as $selectionData) {
                    if (!$selectionData['isDefault']) {
                        continue;
                    }
                    return array(
                        $selectionData['option_id'] => $selectionData['id']
                    );
                }
            } else {
                return array();
            }
        }
    }

    public static function getCheckoutPage()
    {
        return Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(Shopping::OPTION_CHECKOUT, true);
    }

    public static function getTaxName()
    {
        $country = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('country');
        if (isset($country) && array_key_exists($country, self::$_taxName)) {
            return self::$_taxName[$country];
        } else {
            return '';
        }
    }

    /**
     * Currency Conversion by Yahoo Finance Xchange Service
     *
     * @param $price
     * @param string $currency (USD, AUD, etc...)
     * @return float converted price
     */
    public static function getConvertedPriceByCurrency($price, $currency)
    {
        $amount = number_format($price, 2, ".", "");
        $translator = Zend_Registry::get('Zend_Translate');
        $shoppingCurrency = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('currency');
        $cacheHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
        $currRate = $cacheHelper->load('currency_' . $currency . '_to_' . $shoppingCurrency, 'store_');
        if (is_null($currRate)) {
            $yqlQuery = 'SELECT * FROM yahoo.finance.xchange WHERE pair IN ("' . $currency . $shoppingCurrency . '")';
            $requestUrl = self::EXCHANGE_PATH . urlencode($yqlQuery) . self::EXCHANGE_ADDITIONAL_PARAMS;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $requestUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $response = curl_exec($ch);
            curl_close($ch);
            $resultDecode = json_decode($response);
            if ($response === false) {
                throw new Exceptions_SeotoasterPluginException($translator->translate(
                        'Can not automatically convert:'
                    ) . ' ' . $shoppingCurrency . ' ' . $translator->translate('to') . ' ' . $currency);
            } else {
                $currRate = $resultDecode->query->results->rate->Rate;
                $cacheHelper->save('currency_' . $currency . '_to_' . $shoppingCurrency, $currRate, 'store_',
                        array(), Helpers_Action_Cache::CACHE_LONG);
            }
        }
        return number_format($amount / $currRate, 2, ".", "");
    }


    public static function prepareProductImage($photoSrc, $newSize = 'product')
    {
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $websiteUrl = (Zend_Controller_Action_HelperBroker::getStaticHelper('config')->getConfig(
            'mediaServers'
        ) ? Tools_Content_Tools::applyMediaServers($websiteHelper->getUrl()) : $websiteHelper->getUrl());
        if (preg_match('~^https?://.*~', $photoSrc)) {
            $tmp = parse_url($photoSrc);
            $path = explode('/', trim($tmp['path'], '/'));
            if (is_array($path)) {
                $imgName = array_pop($path);
                $guessSize = array_pop($path);
                if (in_array($guessSize, array('small', 'medium', 'large', 'original')) && $guessSize !== $newSize) {
                    $guessSize = $newSize;
                }
                return $tmp['scheme'] . '://' . implode(
                    '/',
                    array(
                        $tmp['host'],
                        implode('/', $path),
                        $guessSize,
                        $imgName
                    )
                );
            }
            return $photoSrc;
        } else {
            $photoSrc = str_replace('/', '/' . $newSize . '/', $photoSrc);
            return $websiteUrl . $websiteHelper->getMedia() . $photoSrc;
        }
    }


    public static function getJsTranslationLanguage()
    {
        $miscConfig = Zend_Registry::get('misc');
        $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $translator = Zend_Registry::get('Zend_Translate');
        $locale = $translator->getLocale();
        $translationFilePath = $websiteHelper->getPath(
        ) . $miscConfig['pluginsPath'] . 'shopping' . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'nls' . DIRECTORY_SEPARATOR . $locale . '_ln.js';
        if (!file_exists($translationFilePath)) {
            return 'en_US';
        }
        return $locale;

    }

    public static function getDefaultCheckoutErrorMessage()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $checkoutErrorMessage = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam(
            Shopping::SHIPPING_ERROR_MESSAGE
        );
        if ($checkoutErrorMessage === null) {
            $checkoutErrorMessage = $translator->translate(
                'There is an issue with the shipping information provided, please contact us for support.'
            );
        }
        return $checkoutErrorMessage;
    }

    /*
     * Replaces widgets on values from a dictionary.
     *
     * If noZeroPrice in config set to 1 - do not show zero prices and "Add to cart" becomes "Go to product".
     *
     * @return string
     */
    public static function preparingProductListing($templateContent, $product, $dictionary = array(), $noZeroPrice = 0)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $entityParser = new Tools_Content_EntityParser();

        //setting up the entity parser
        $entityParser->addToDictionary($dictionary);

        // fetching $product:price and $product:freeshipping widgets and rendering them via native widget
        if (preg_match_all(
            '~{\$product:((?:price|freeshipping|photourl|wishlistqty|tags):?[^}]*)}~',
            $templateContent,
            $productPriceWidgets
        )
        ) {
            $replacements = array();
            foreach ($productPriceWidgets[1] as $key => $widgetData) {
                if (!$product->getPage() instanceof Application_Model_Models_Page) {
                    continue;
                }
                $args = array_filter(explode(':', $widgetData));
                $widget = Tools_Factory_WidgetFactory::createWidget(
                    'product',
                    $args,
                    array('id' => $product->getPage()->getId())
                );
                $key = trim($productPriceWidgets[0][$key], '{}');
                $replacements[$key] = $widget->render();

                if ($widgetData === 'price' || $widgetData === 'price:original') {
                    if ((int)$noZeroPrice === 1 && floatval($product->getPrice()) == 0) {
                        $replacements[$key] = '';
                        $replacements['$store:addtocart'] = '<a class="tcart-add go-to-product" href="' . ($product->getPage(
                        ) ? $product->getPage()->getUrl() : 'javascript:;') . '">' . $translator->translate(
                            'Go to product'
                        ) . '</a>';
                        $replacements['$store:addtocart:' . $product->getId()] = $replacements['$store:addtocart'];
                        $replacements['$store:addtocart:checkbox'] = $replacements['$store:addtocart'];
                    }
                }
            }
            if (!empty($replacements)) {
                $entityParser->addToDictionary($replacements);
                unset($replacements, $productPriceWidgets);
            }
        }

        return $entityParser->parse($templateContent);
    }

    /**
     *  Return links for 'thank you'  and 'client area' pages
     *
     * @return array
     */
    public static function getPostPurchaseAndLandingPageLinks()
    {
        $pageOptionsDbRable = new Application_Model_DbTable_PageOption();
        $select = $pageOptionsDbRable->getAdapter()->select()->from(
            array('po' => 'page_option'),
            array('pho.option_id', 'p.url')
        )
            ->joinLeft(array('pho' => 'page_has_option'), 'po.id = pho.option_id', array())
            ->joinLeft(array('p' => 'page'), 'p.id = pho.page_id', array())
            ->where('pho.option_id IN (?)', array(Tools_Misc::OPTION_THANKYOU, Tools_Misc::OPTION_STORE_CLIENT_LOGIN));
        return $pageOptionsDbRable->getAdapter()->fetchAssoc($select);
    }

    /**
     * apply inventory actions (plugin based)
     *
     * @param int $productId product id
     * @param array $options product options
     * @param int $quantity quantity of products
     * @param string $methodName public function name from inventory plugin
     * @return array
     */
    public static function applyInventory($productId, $options, $quantity, $methodName)
    {
        $inventoryPluginsStatus = self::getInventoryPlugins($methodName);
        if (!empty($inventoryPluginsStatus)) {
            $pageData = array('websiteUrl' => Zend_Controller_Action_HelperBroker::getStaticHelper('website')->getUrl());
            foreach ($inventoryPluginsStatus as $pluginName => $pluginStatus) {
                if($pluginStatus === true) {
                    try {
                        $plugin = Tools_Factory_PluginFactory::createPlugin($pluginName, array(),
                            $pageData);
                        $result = $plugin->$methodName($productId, $options, $quantity);
                        if ($result['error'] === true) {
                            return $result;
                        }
                    } catch (Exception $e) {
                        return array('error' => true, 'message' => $e->getMessage());
                    }
                }
            }
        }
        return array('error' => false);
    }

    /**
     * Get all plugins with tag inventory with specified method name
     *
     * @param string $methodName plugin public method name
     *
     * @return array
     */
    public static function getInventoryPlugins($methodName)
    {
        $inventoryPlugins = Tools_Plugins_Tools::getPluginsByTags(array('inventory'));
        $inventoryPluginsStatus = array();
        if (!empty($inventoryPlugins)) {
            foreach ($inventoryPlugins as $inventoryPlugin) {
                if ($inventoryPlugin->getStatus() === Application_Model_Models_Plugin::ENABLED) {
                    $invPluginName = ucfirst($inventoryPlugin->getName());
                    if (class_exists($invPluginName) && method_exists($invPluginName,
                            $methodName)
                    ) {
                        $reflection = new ReflectionMethod($invPluginName, $methodName);
                        if ($reflection->isPublic()) {
                            $inventoryPluginsStatus[$invPluginName] = true;
                        }
                    } else {
                        $inventoryPluginsStatus[$invPluginName] = false;
                    }
                }
            }
        }
        return $inventoryPluginsStatus;
    }

    /**
     * Reorder config screen tabs
     *
     * @param array $pluginsToReorder Ex:array('tabId' => 'coupons', 'tabName' => 'Coupons', 'type' => 'internal')
     * @param array $configTabs Ex: array('tabId' => 'pluginid', 'tabName' => 'title', 'type' => 'external');
     * @return array
     */
    public static function reorderPluginTabs($pluginsToReorder, $configTabs)
    {
        foreach ($pluginsToReorder as $pluginOrder) {
            $elementsAfterPosition = array_slice($configTabs, $pluginOrder['tabOrderId'] -1);
            $elementsBeforePosition = array_slice($configTabs, 0, $pluginOrder['tabOrderId'] -1);
            $elementsBeforePosition[] = array('tabId' => $pluginOrder['tabId'], 'tabName' => $pluginOrder['tabName'], 'type' => 'external');
            $configTabs = array_merge($elementsBeforePosition, $elementsAfterPosition);
        }

        return $configTabs;
    }

    /**
     * Remove last zero number
     * Weight format conversion by Country location
     * ex. fr_FR = 15,35
     * ex. en_US = 15.35
     *
     * @param $weight
     * @return string
     */
    public static function processingWeightFormat($weight) {
        $weight = round($weight, 3);
        $locale = Zend_Locale::getLocaleToTerritory(Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('country'));
        $weightFormat = Zend_Locale_Format::toNumber($weight, array('locale' => $locale));

        return $weightFormat;
    }

    public static function prepareInvoice($data)
    {
        if (Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf')->getStatus() === 'enabled') {
            $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Session');
            $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
            $websiteConfig = Zend_Controller_Action_HelperBroker::getStaticHelper('config')->getConfig();
            $pdfPath = $websiteConfig['path'] . 'plugins' . DIRECTORY_SEPARATOR . 'invoicetopdf' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR;
            if (empty($data['cartId'])) {
                return false;
            }
            $cartId = $data['cartId'];
            $cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
            if (empty($cartSession)) {
                return false;
            }
            $invoicetopdfSettings = Invoicetopdf_Models_Mapper_InvoicetopdfSettingsMapper::getInstance()->getConfigParams();
            if (isset($data['cartId']) && isset($invoicetopdfSettings['invoiceTemplate']) && isset($data['dwn'])) {
                $cartId = $data['cartId'];
                $templateTable = new Application_Model_DbTable_Template;
                if (isset($data['packing'])) {
                    $where = $templateTable->getAdapter()->quoteInto(
                        'name = ?',
                        $invoicetopdfSettings['packingTemplate']
                    );
                } else {
                    $where = $templateTable->getAdapter()->quoteInto(
                        'name = ?',
                        $invoicetopdfSettings['invoiceTemplate']
                    );
                }
                $invoiceTemplate = Application_Model_Mappers_TemplateMapper::getInstance()->fetchAll($where);
                $templateContent = $invoiceTemplate[0]->getContent();

                $sessionHelper->storeCartSessionKey = $cartId;
                $sessionHelper->storeCartSessionConversionKey = $cartId;
                $customerDbTable = new Quote_Models_DbTable_ShoppingCustomerAddress();
                $whereBilling = $customerDbTable->getAdapter()->quoteInto(
                    'id = ?',
                    $cartSession->getBillingAddressId()
                );
                $whereShipping = $customerDbTable->getAdapter()->quoteInto(
                    'id = ?',
                    $cartSession->getShippingAddressId()
                );
                $customerShipping = $customerDbTable->getAdapter()->fetchAll(
                    $customerDbTable->select()->from('shopping_customer_address')->where($whereShipping)
                );
                $customerBilling = $customerDbTable->getAdapter()->fetchAll(
                    $customerDbTable->select()->from('shopping_customer_address')->where($whereBilling)
                );
                if (!empty($customerShipping)) {
                    $customerShipping[0]['country'] = self::prepareCountry($customerShipping[0]['country']);
                    $sessionHelper->customerShippingInvoice = $customerShipping;
                }
                if (!empty($customerBilling)) {
                    $customerBilling[0]['country'] = self::prepareCountry($customerBilling[0]['country']);
                    $sessionHelper->customerBillingInvoice = $customerBilling;
                }

                $themeData = Zend_Registry::get('theme');
                $pageMapper = Application_Model_Mappers_PageMapper::getInstance();
                $parserOptions = array(
                    'websiteUrl' => $websiteHelper->getUrl(),
                    'websitePath' => $websiteHelper->getPath(),
                    'currentTheme' => $websiteHelper->getConfig('currentTheme'),
                    'themePath' => $themeData['path'],
                );
                $page = $pageMapper->findByUrl('index.html');
                $page = $page->toArray();
                $parser = new Tools_Content_Parser($templateContent, $page, $parserOptions);
                $content = $parser->parse();

//                if (!defined('_MPDF_TEMP_PATH')) {
//                    define('_MPDF_TEMP_PATH', $pdfPath);
//                }
//                require_once($websiteConfig['path'] . 'plugins' . DIRECTORY_SEPARATOR . 'invoicetopdf' . DIRECTORY_SEPARATOR . 'system/library/mpdf/mpdf.php');

//                $pdfFile = new mPDF('utf-8', 'A4');
                $pdfTmpPath = $websiteConfig['path'] . 'plugins' . DIRECTORY_SEPARATOR . 'invoicetopdf' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR;
                require_once($websiteConfig['path'] . 'plugins' . DIRECTORY_SEPARATOR . 'invoicetopdf' . DIRECTORY_SEPARATOR.'system/library/mpdflatest/vendor/autoload.php');

                $pdfFile = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'tempDir' => $pdfTmpPath
                ]);
                $pdfFile->WriteHTML($content);

                if (isset($data['packing'])) {
                    $pdfFileName = 'packing_slip_' . md5($cartId . microtime()) . '.pdf';
                    if (!empty($invoicetopdfSettings['invoiceOrigName'])) {
                        $pdfFileName = 'packing_slip_' . $cartId . '.pdf';
                    }
                } else {
                    $invoicePrefixLabel = 'Invoice';
                    if (!empty($invoicetopdfSettings['invoicePrefix'])) {
                        $invoicePrefixLabel = $invoicetopdfSettings['invoicePrefix'];
                    }

                    $pdfFileName = $invoicePrefixLabel.'_' . md5($cartId . microtime()) . '.pdf';
                    if (!empty($invoicetopdfSettings['invoiceOrigName'])) {
                        $pdfFileName = $invoicePrefixLabel.'_' . $cartId . '.pdf';
                    }

                }
                $pdfFile->Output($pdfPath . $pdfFileName, 'F');

                return array('fileName' => $pdfFileName, 'folder' => $pdfPath, 'cartSession' => $cartSession);

            }
        }
        return false;
    }

    /**
     * Return country full name
     *
     * @param string $country country code
     * @return mixed
     */
    public static function prepareCountry($country)
    {
        if (!empty($country)) {
            $countries = Tools_Geo::getCountries(true);
            $country = $countries[$country];
        }
        return $country;
    }

    /**
     * Prepare checked filter in config to view
     *
     * @param $widgetSettings
     * @param $filters
     * @return mixed
     */
    public static function processProductFilters($widgetSettings, $filters)
    {
        foreach ($filters as $key => $filter) {
            $filterValues = array();

            if(!empty($widgetSettings[$filter['name']]) && is_array($widgetSettings[$filter['name']])) {
                foreach ($filter['values'] as $filterName => $value) {
                    if(!empty($filterName) && !empty($widgetSettings[$filter['name']][$filterName])) {
                        $filterValues[$filterName] = $value;
                    }
                }

                $filters[$key]['values'] = $filterValues;
            }
        }

        return $filters;
    }

    public static function addThrottleTransaction()
    {
        $throttleConfigParams = [];
        $shoppingConfigMapper = Models_Mapper_ShoppingConfig::getInstance();
        $throttleTransactionsCounter = $shoppingConfigMapper->getConfigParam('throttleTransactionsCounter');
        $throttleTransactionsCounterDate = $shoppingConfigMapper->getConfigParam('throttleTransactionsCounterDate');
        $throttleTransactionsCounter = !empty(intval($throttleTransactionsCounter)) ? intval($throttleTransactionsCounter) : 0;
        $throttleTransactionsCounterDate = !empty($throttleTransactionsCounterDate) ? date('Y-m-d', strtotime($throttleTransactionsCounterDate)) : date('Y-m-d');
        $timeDiff = abs(strtotime($throttleTransactionsCounterDate) - strtotime('now')) / 3600;
        if ($timeDiff > 24) {
            $throttleTransactionsCounter = 0;
            $throttleTransactionsCounterDate = date('Y-m-d');
        }
        $throttleConfigParams['throttleTransactionsCounter'] = $throttleTransactionsCounter + 1;
        $throttleConfigParams['throttleTransactionsCounterDate'] = $throttleTransactionsCounterDate;
        $shoppingConfigMapper->save($throttleConfigParams);
    }

    public static function checkThrottleTransactionsLimit()
    {
        $shoppingConfigMapper = Models_Mapper_ShoppingConfig::getInstance();
        $throttleTransactionsLimit = intval($shoppingConfigMapper->getConfigParam('throttleTransactionsLimit'));
        $throttleTransactionsCounter = $shoppingConfigMapper->getConfigParam('throttleTransactionsCounter');
        $throttleTransactionsCounterDate = $shoppingConfigMapper->getConfigParam('throttleTransactionsCounterDate');
        $throttleTransactionsCounter = !empty(intval($throttleTransactionsCounter)) ? intval($throttleTransactionsCounter) : 0;
        $throttleTransactionsCounterDate = !empty($throttleTransactionsCounterDate) ? date('Y-m-d', strtotime($throttleTransactionsCounterDate)) : date('Y-m-d');

        $timeDiff = abs(strtotime($throttleTransactionsCounterDate) - strtotime('now')) / 3600;

        return ($timeDiff > 24 || (($timeDiff < 24) && $throttleTransactionsCounter < $throttleTransactionsLimit));

    }


    public static function isStoreClosed()
    {
        $configMapper = Models_Mapper_ShoppingConfig::getInstance();
        $config = $configMapper->getConfigParams();
        if (!empty($config['useOperationalHoursForOrders'])) {
            $operationalHoursStoreConfig = $config['operationalHours'];
            if (empty($operationalHoursStoreConfig)) {
                return false;
            }

            $operationalHoursStoreConfig = unserialize($operationalHoursStoreConfig);

            $storeTimezone = $config['timezone'];
            $serverTimezone = date_default_timezone_get();
            if (empty($serverTimezone)) {
                $serverTimezone = 'UTC';
            }

            $preparedDate = Tools_System_Tools::convertDateFromTimezone('now', $serverTimezone, 'UTC');
            $preparedDate = date(Tools_System_Tools::DATE_MYSQL, strtotime($preparedDate .'+'.self::getTimezoneShift('UTC', $storeTimezone).'hours'));

            $currentDayOfTheWeek = strtolower(date('l', strtotime($preparedDate)));
            $currentHourOfTheWeek = intval(strtolower(date('H', strtotime($preparedDate))));

            if (isset($operationalHoursStoreConfig[$currentDayOfTheWeek]) && !empty($operationalHoursStoreConfig[$currentDayOfTheWeek])) {
                $operationHoursInfo = $operationalHoursStoreConfig[$currentDayOfTheWeek];
                $operationHoursInfoTo = $operationHoursInfo['to'] - 1;
                if (empty($operationHoursInfoTo)) {
                    $operationHoursInfoTo = 24;
                }

                if (($operationHoursInfo['from']-1) <= $currentHourOfTheWeek && $operationHoursInfoTo > $currentHourOfTheWeek) {
                    return false;
                } else {
                    return true;
                }
            }
        }

        return false;
    }


    public static function getStoreIsClosedMessage()
    {
        $configMapper = Models_Mapper_ShoppingConfig::getInstance();
        $config = $configMapper->getConfigParams();
        if (!empty($config['useOperationalHoursForOrdersMessage'])) {
            return $config['useOperationalHoursForOrdersMessage'];
        }

        $translator = Zend_Registry::get('Zend_Translate');

        return $translator->translate('Online ordering unavailable');
    }

    public static function getStoreIsDisabledMessage()
    {
        $configMapper = Models_Mapper_ShoppingConfig::getInstance();
        $config = $configMapper->getConfigParams();
        if (!empty($config['disabledStoreMessage'])) {
            return $config['disabledStoreMessage'];
        }

        $translator = Zend_Registry::get('Zend_Translate');

        return $translator->translate('Online ordering unavailable');
    }

    public static function isStoreDisabled()
    {
        $configMapper = Models_Mapper_ShoppingConfig::getInstance();
        $config = $configMapper->getConfigParams();
        if (!empty($config['disabledStore'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $timezoneFrom
     * @param $timeZoneToShift
     * @return float|int
     */
    public static function getTimezoneShift($timezoneFrom, $timeZoneToShift)
    {
        $dateTimeZoneServer = new DateTimeZone($timezoneFrom);
        $dateTimeZoneStore = new DateTimeZone($timeZoneToShift);
        $dateTimeServer = new DateTime("now", $dateTimeZoneServer);

        $timeOffsetInHours = $dateTimeZoneStore->getOffset($dateTimeServer) / 3600;

        return $timeOffsetInHours;
    }

}
