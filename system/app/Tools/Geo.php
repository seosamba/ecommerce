<?php

/**
 * Geo - useful tools for working with geographical data
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_Geo {

	/**
	 * Method builds a list of world countries with ISO codes and country name translated to current locale language
	 * @static
	 * @param bool $pairs If true returns plain array of ISOcode => CountryName pairs
     * @param bool $withDefaultLocale return list of countries with en_GB locale
	 * @return array list of world countries
	 * @todo add caching
	 */
	public static function getCountries($pairs = false, $withDefaultLocale = false) {
        $data = array();
        if ($withDefaultLocale === true) {
            $countriesNames = Zend_Locale::getTranslationList('territory', 'en_GB', 2);
        } else {
            $countriesNames = Zend_Locale::getTranslationList('territory', null, 2);
        }

        $countryTable = new Models_DbTable_Country();
        $countryList = $countryTable->fetchAll()->toArray();
        foreach ($countryList as $country){
	        if(!isset($countriesNames[$country['country']])) {
		        continue;
	        }
	        $country['name'] = $countriesNames[$country['country']];
            if($pairs) {
	        	$data[$country['country']] = $country['name'];
            } else {
	            array_push($data, $country);
            }
        }
        asort($data);
		return $data;
	}
	
	/**
	 * Method returns list of State/Province/Region for given countries
	 * @static
	 * @param string $country official ISO code of country
	 * @param bool $pairs If true returns plain array of StateId => StateName
	 * @return array|null array with list of states or null if given country doesn't have any
	 */
	public static function getState($country = null, $pairs = false) {
		$stateTable = new Zend_Db_Table('shopping_list_state');
		
		$where = null;
		if ($country !== null){
			$where = $stateTable->getAdapter()->quoteInto('country = ?', $country);
		}
		if ($pairs) {
			$select = $stateTable->select()->from($stateTable,array('id','name'));
			if ($where){
				$select->where($where);
			}
			$data = $stateTable->getAdapter()->fetchPairs($select);
		} else {
			$select = $stateTable->select()->from($stateTable);
			if ($where){
				$select->where($where);
			}
			$data = $stateTable->getAdapter()->fetchAll($select);
		}
		return $data;
	}

	/**
	 * Get full state info by given id
	 * @static
	 * @param $stateId Id of state
	 * @return array State info
	 */
	public static function getStateById($stateId) {
		if (!is_numeric($stateId)) {
			return null;
		}
		$stateTable = new Zend_Db_Table('shopping_list_state');
		$state = $stateTable->find($stateId)->current();
		if ($state) {
			return $state->toArray();
		}
		return null;
	}

    public static function getStateByCode($code) {
        $code       = filter_var($code, FILTER_SANITIZE_STRING);
        $stateTable = new Zend_Db_Table('shopping_list_state');
        $state      = $stateTable->fetchAll($stateTable->getAdapter()->quoteInto('state=?', $code))->current();
        if($state) {
            return $state->toArray();
        }
        return null;
    }

	public static function generateStaticGmaps($markers, $width = 640, $height = 640){
		if (is_array($markers) && !is_array(current($markers))){
			$markers = array($markers);
		}
        $generalConfig = Application_Model_Mappers_ConfigMapper::getInstance()->getConfig();

		$params = array(
			'sensor'    => 'false',
			'size'      => intval($width).'x'.intval($height),
			'markers'   => array()
		);
		$countries = Tools_Geo::getCountries(true);

		foreach ($markers as $marker) {
			$marker = Tools_Misc::clenupAddress($marker);
			$state = Tools_Geo::getStateById($marker['state']);

			$addressLine = implode(', ', array_filter(array(
				$countries[$marker['country']],
				$marker['address1'],
				$marker['city'],
				$state['state'],
				$marker['zip'],
			)));
			$params['markers'][] = $addressLine;
		}

		$params['markers'] = implode('|', $params['markers']);

        if(!empty($generalConfig['googleApiKey'])) {
            $params['key'] = $generalConfig['googleApiKey'];
            $googleApiKey = Zend_Controller_Action_HelperBroker::getStaticHelper('config')->getConfig('googleApiKey');

            if (!empty($googleApiKey)) {
                $params['key'] = $googleApiKey;
            }
        }

		return 'https://maps.googleapis.com/maps/api/staticmap?'.http_build_query($params);
	}

    /**
     * Get coordinates latitude and longitude
     */
    public static function getMapCoordinates($address)
    {
        $googleApiKey = Zend_Controller_Action_HelperBroker::getStaticHelper('config')->getConfig('googleApiKey');

        $gApiKey = '';
        if(!empty($googleApiKey)) {
            $gApiKey = '&key='. $googleApiKey;
        }
        // replace all the white space with "+" sign to match with google search pattern
        $url = 'https://maps.google.com/maps/api/geocode/json?sensor=false'. $gApiKey .'&address=' . str_replace(' ', '+', $address);
        $response = file_get_contents($url);
        //generate array object from the response from the web
        $json = json_decode($response, true);
        if (empty($json['results'])) {
            return array('lat' => null, 'lng' => null);
        }

        return array(
            'lat' => $json['results'][0]['geometry']['location']['lat'],
            'lng' => $json['results'][0]['geometry']['location']['lng']
        );
    }

    /**
     * Get state
     *
     * @param string $stateParam state id or state abbr
     * @return string
     */
    public static function getStateByParam($stateParam)
    {
        if (!empty($stateParam) && is_numeric($stateParam)) {
            $state = Tools_Geo::getStateById($stateParam);
            return $state['state'];
        }  elseif ($stateParam) {
            return $stateParam;
        }

        return '';
    }

    /**
     * Get country list names
     *
     * @return array
     */
    public static function countryListNames()
    {
        return array (
            'AC' => 'Ascension Island',
            'AD' => 'Andorra',
            'AE' => 'United Arab Emirates',
            'AF' => 'Afghanistan',
            'AG' => 'Antigua & Barbuda',
            'AI' => 'Anguilla',
            'AL' => 'Albania',
            'AM' => 'Armenia',
            'AO' => 'Angola',
            'AQ' => 'Antarctica',
            'AR' => 'Argentina',
            'AS' => 'American Samoa',
            'AT' => 'Austria',
            'AU' => 'Australia',
            'AW' => 'Aruba',
            'AX' => 'Åland Islands',
            'AZ' => 'Azerbaijan',
            'BA' => 'Bosnia & Herzegovina',
            'BB' => 'Barbados',
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BH' => 'Bahrain',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BL' => 'St. Barthélemy',
            'BM' => 'Bermuda',
            'BN' => 'Brunei',
            'BO' => 'Bolivia',
            'BQ' => 'Caribbean Netherlands',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'BT' => 'Bhutan',
            'BW' => 'Botswana',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CC' => 'Cocos (Keeling) Islands',
            'CD' => 'Congo - Kinshasa',
            'CF' => 'Central African Republic',
            'CG' => 'Congo - Brazzaville',
            'CH' => 'Switzerland',
            'CI' => 'Côte d’Ivoire',
            'CK' => 'Cook Islands',
            'CL' => 'Chile',
            'CM' => 'Cameroon',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CV' => 'Cape Verde',
            'CW' => 'Curaçao',
            'CX' => 'Christmas Island',
            'CY' => 'Cyprus',
            'CZ' => 'Czechia',
            'DE' => 'Germany',
            'DG' => 'Diego Garcia',
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'DZ' => 'Algeria',
            'EA' => 'Ceuta & Melilla',
            'EC' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'EH' => 'Western Sahara',
            'ER' => 'Eritrea',
            'ES' => 'Spain',
            'ET' => 'Ethiopia',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Islands',
            'FM' => 'Micronesia',
            'FO' => 'Faroe Islands',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GB' => 'United Kingdom',
            'GD' => 'Grenada',
            'GE' => 'Georgia',
            'GF' => 'French Guiana',
            'GG' => 'Guernsey',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GL' => 'Greenland',
            'GM' => 'Gambia',
            'GN' => 'Guinea',
            'GP' => 'Guadeloupe',
            'GQ' => 'Equatorial Guinea',
            'GR' => 'Greece',
            'GS' => 'South Georgia & South Sandwich Islands',
            'GT' => 'Guatemala',
            'GU' => 'Guam',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HK' => 'Hong Kong SAR China',
            'HN' => 'Honduras',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'IC' => 'Canary Islands',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IM' => 'Isle of Man',
            'IN' => 'India',
            'IO' => 'British Indian Ocean Territory',
            'IQ' => 'Iraq',
            'IR' => 'Iran',
            'IS' => 'Iceland',
            'IT' => 'Italy',
            'JE' => 'Jersey',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'KE' => 'Kenya',
            'KG' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'St. Kitts & Nevis',
            'KP' => 'North Korea',
            'KR' => 'South Korea',
            'KW' => 'Kuwait',
            'KY' => 'Cayman Islands',
            'KZ' => 'Kazakhstan',
            'LA' => 'Laos',
            'LB' => 'Lebanon',
            'LC' => 'St. Lucia',
            'LI' => 'Liechtenstein',
            'LK' => 'Sri Lanka',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'LY' => 'Libya',
            'MA' => 'Morocco',
            'MC' => 'Monaco',
            'MD' => 'Moldova',
            'ME' => 'Montenegro',
            'MF' => 'St. Martin',
            'MG' => 'Madagascar',
            'MH' => 'Marshall Islands',
            'MK' => 'North Macedonia',
            'ML' => 'Mali',
            'MM' => 'Myanmar (Burma)',
            'MN' => 'Mongolia',
            'MO' => 'Macao SAR China',
            'MP' => 'Northern Mariana Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MS' => 'Montserrat',
            'MT' => 'Malta',
            'MU' => 'Mauritius',
            'MV' => 'Maldives',
            'MW' => 'Malawi',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NF' => 'Norfolk Island',
            'NG' => 'Nigeria',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NP' => 'Nepal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'NZ' => 'New Zealand',
            'OM' => 'Oman',
            'PA' => 'Panama',
            'PE' => 'Peru',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PM' => 'St. Pierre & Miquelon',
            'PN' => 'Pitcairn Islands',
            'PR' => 'Puerto Rico',
            'PS' => 'Palestinian Territories',
            'PT' => 'Portugal',
            'PW' => 'Palau',
            'PY' => 'Paraguay',
            'QA' => 'Qatar',
            'RE' => 'Réunion',
            'RO' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'SA' => 'Saudi Arabia',
            'SB' => 'Solomon Islands',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SE' => 'Sweden',
            'SG' => 'Singapore',
            'SH' => 'St. Helena',
            'SI' => 'Slovenia',
            'SJ' => 'Svalbard & Jan Mayen',
            'SK' => 'Slovakia',
            'SL' => 'Sierra Leone',
            'SM' => 'San Marino',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SR' => 'Suriname',
            'SS' => 'South Sudan',
            'ST' => 'São Tomé & Príncipe',
            'SV' => 'El Salvador',
            'SX' => 'Sint Maarten',
            'SY' => 'Syria',
            'SZ' => 'Eswatini',
            'TA' => 'Tristan da Cunha',
            'TC' => 'Turks & Caicos Islands',
            'TD' => 'Chad',
            'TF' => 'French Southern Territories',
            'TG' => 'Togo',
            'TH' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TK' => 'Tokelau',
            'TL' => 'Timor-Leste',
            'TM' => 'Turkmenistan',
            'TN' => 'Tunisia',
            'TO' => 'Tonga',
            'TR' => 'Turkey',
            'TT' => 'Trinidad & Tobago',
            'TV' => 'Tuvalu',
            'TW' => 'Taiwan',
            'TZ' => 'Tanzania',
            'UA' => 'Ukraine',
            'UG' => 'Uganda',
            'UM' => 'U.S. Outlying Islands',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VA' => 'Vatican City',
            'VC' => 'St. Vincent & Grenadines',
            'VE' => 'Venezuela',
            'VG' => 'British Virgin Islands',
            'VI' => 'U.S. Virgin Islands',
            'VN' => 'Vietnam',
            'VU' => 'Vanuatu',
            'WF' => 'Wallis & Futuna',
            'WS' => 'Samoa',
            'XK' => 'Kosovo',
            'YE' => 'Yemen',
            'YT' => 'Mayotte',
            'ZA' => 'South Africa',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );
    }

}
