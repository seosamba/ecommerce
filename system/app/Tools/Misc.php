<?php

/**
 * Misc
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_Misc {

    /**
     * Localized list of names for currencies
     */
    const KEY_CURRENCY_LIST = 'currency_list';

    const SECTION_STORE_MANAGEZONES     = 'zones';
    
    const SECTION_STORE_TAXES           = 'taxes';
    
    const SECTION_STORE_CONFIG          = 'storeconfig';
    
    const SECTION_STORE_SHIPPINGCONFIG  = 'shippingconfig';
    
    const SECTION_STORE_ADDEDITPRODUCT  = 'addproduct';
    
    const SECTION_STORE_BRANDLOGOS      = 'brandlogos';
    
    /*
     * Changes for name inc. Tax 
     * Put in array country abbr and name for change 'AU'=>'GST'
     */
    public static $_taxName     = array('AU'=>'GST', 'FR'=>'TVA');
    
	public static $_weightUnits = array(
		'kg' => 'Kilogram (kg)',
		'lbs' => 'Pound (lbs)'
	);

    
    public static $_helpHashMap  = array(
        self::SECTION_STORE_MANAGEZONES     => 'shopping-cart-shipping-tax-zones.html',
        self::SECTION_STORE_TAXES           => 'shopping-cart-tax-calculation.html',
        self::SECTION_STORE_CONFIG          => 'setup-online-shopping-cart.html',
        self::SECTION_STORE_SHIPPINGCONFIG  => 'shopping-cart-shipping-calculator.html',
        self::SECTION_STORE_ADDEDITPRODUCT  => 'how-to-add-or-edit-a-product-to-your-store.html',
        self::SECTION_STORE_BRANDLOGOS      => 'e-commerce-product-brands.html'
       
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

	public static function getShippingPluginContent($shippingPlugin) {
		$className = ucfirst($shippingPlugin);
		$method    = 'getConfigScreen';
		if(class_exists($className) && method_exists($className, $method)) {
			return preg_replace('~name="([-_\w\s\d]+)([\[\]]{0,2})"~','name="shippingExternal[$1]$2"', $className::$method());
		}
	}

    public static function getCurrencyList(){
        $cacheHelper   = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
        if(($data = $cacheHelper->load(self::KEY_CURRENCY_LIST, 'store_')) === null) {
            $zendCurrenciesList = Zend_Locale::getTranslationList('NameToCurrency');
            $data = array();
            foreach (self::$_currenciesFilter as $currency => $name){
                if (array_key_exists($currency, $zendCurrenciesList)){
                    $currencySymbol = Zend_Locale::getTranslation($currency, 'CurrencySymbol');
                    $data[$currency] = ucwords($zendCurrenciesList[$currency]) . ($currencySymbol !== false ? ' ('.$currencySymbol.')' : '' ) ;
                }
            }
            asort($data);
            $cacheHelper->save(self::KEY_CURRENCY_LIST, $data, 'store_', array('locale'), Helpers_Action_Cache::CACHE_LONG);
        }
        return $data;
    }

	public static function clenupAddress($address) {
		$_addressTmpl   = array(
			'address_type'  => '',
			'firstname'     => '',
			'lastname'      => '',
			'company'       => '',
			'email'         => '',
			'address1'      => '',
			'address2'      => '',
			'country'       => '',
			'city'          => '',
			'state'         => '',
			'zip'           => '',
			'phone'         => '',
			'mobile'        => ''
		);

		$address = array_intersect_key($address, $_addressTmpl);
		ksort($address);
		return $address;
	}

	public static function getAddressUniqKey($address) {
		$address = self::clenupAddress($address);
		return md5(http_build_query($address));
	}

	public static function getDefaultProductOptions(Models_Model_Product $product) {
		$productOptions = $product->getDefaultOptions();
		if(!is_array($productOptions) || empty($productOptions)) {
			return array();
		}
		foreach($productOptions as $option) {
			if(isset($option['selection']) && is_array($option['selection']) && !empty($option['selection'])) {
				$selections = $option['selection'];
				foreach($selections as $selectionData) {
					if(!$selectionData['isDefault']) {
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

    public static function getCheckoutPage() {
        return Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(Shopping::OPTION_CHECKOUT, true);
    }
    
    public static function getTaxName() {
        $country = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('country');
        if(isset($country) && array_key_exists($country, self::$_taxName)){
            return self::$_taxName[$country];
        }else{
            return '';
        }
    }
}