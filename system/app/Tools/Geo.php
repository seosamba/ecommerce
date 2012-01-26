<?php

/**
 * Geo - usefull tools for working with geographical data
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_Geo {

	public static $_states = array(
		'US' => array(
			'AL'=>"Alabama",
			'AK'=>"Alaska",
			'AZ'=>"Arizona",
			'AR'=>"Arkansas",
			'CA'=>"California",
			'CO'=>"Colorado",
			'CT'=>"Connecticut",
			'DE'=>"Delaware",
			'DC'=>"District Of Columbia",
			'FL'=>"Florida",
			'GA'=>"Georgia",
			'HI'=>"Hawaii",
			'ID'=>"Idaho",
			'IL'=>"Illinois",
			'IN'=>"Indiana",
			'IA'=>"Iowa",
			'KS'=>"Kansas",
			'KY'=>"Kentucky",
			'LA'=>"Louisiana",
			'ME'=>"Maine",
			'MD'=>"Maryland",
			'MA'=>"Massachusetts",
			'MI'=>"Michigan",
			'MN'=>"Minnesota",
			'MS'=>"Mississippi",
			'MO'=>"Missouri",
			'MT'=>"Montana",
			'NE'=>"Nebraska",
			'NV'=>"Nevada",
			'NH'=>"New Hampshire",
			'NJ'=>"New Jersey",
			'NM'=>"New Mexico",
			'NY'=>"New York",
			'NC'=>"North Carolina",
			'ND'=>"North Dakota",
			'OH'=>"Ohio",
			'OK'=>"Oklahoma",
			'OR'=>"Oregon",
			'PA'=>"Pennsylvania",
			'RI'=>"Rhode Island",
			'SC'=>"South Carolina",
			'SD'=>"South Dakota",
			'TN'=>"Tennessee",
			'TX'=>"Texas",
			'UT'=>"Utah",
			'VT'=>"Vermont",
			'VA'=>"Virginia",
			'WA'=>"Washington",
			'WV'=>"West Virginia",
			'WI'=>"Wisconsin",
			'WY'=>"Wyoming"
		),
		'CA' => array(
			'AB'=>'Alberta',
			'BC'=>'British Columbia',
			'MB'=>'Manitoba',
			'NB'=>'New Brunswick',
			'NF'=>'Newfoundland and Labrador',
			'NT'=>'Northwest Territories',
			'NS'=>'Nova Scotia',
			'NU'=>'Nunavut',
			'ON'=>'Ontario',
			'PE'	=>'Prince Edward Island',
			'QC'=>'Quebec',
			'SK'=>'Saskatchewan',
			'YT'=>'Yukon Territory'
		),
		'UA' => array(
			'KH'=>'Харьковская область'
		)
	);
	
	public static $_defaultZones = array(
		'US' => array(
			'countries' => array(
				'US', 'UM', 'VI'
			),
			'states' => array('US' => array(
				'AL'=>"Alabama", 'AK'=>"Alaska", 'AZ'=>"Arizona", 'AR'=>"Arkansas",
				'CA'=>"California", 'CO'=>"Colorado", 'CT'=>"Connecticut", 'DE'=>"Delaware",
				'DC'=>"District Of Columbia", 'FL'=>"Florida", 'GA'=>"Georgia", 'HI'=>"Hawaii",
				'ID'=>"Idaho", 'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",
				'KS'=>"Kansas", 'KY'=>"Kentucky", 'LA'=>"Louisiana", 'ME'=>"Maine",
				'MD'=>"Maryland", 'MA'=>"Massachusetts", 'MI'=>"Michigan", 'MN'=>"Minnesota",
				'MS'=>"Mississippi", 'MO'=>"Missouri", 'MT'=>"Montana", 'NE'=>"Nebraska",
				'NV'=>"Nevada", 'NH'=>"New Hampshire", 'NJ'=>"New Jersey", 'NM'=>"New Mexico",
				'NY'=>"New York", 'NC'=>"North Carolina", 'ND'=>"North Dakota", 'OH'=>"Ohio",
				'OK'=>"Oklahoma", 'OR'=>"Oregon", 'PA'=>"Pennsylvania", 'RI'=>"Rhode Island",
				'SC'=>"South Carolina", 'SD'=>"South Dakota",  'TN'=>"Tennessee", 'TX'=>"Texas",
				'UT'=>"Utah", 'VT'=>"Vermont", 'VA'=>"Virginia", 'WA'=>"Washington", 
				'WV'=>"West Virginia",  'WI'=>"Wisconsin", 'WY'=>"Wyoming"
			))
		),
		'CA' => array(
			'countries' => array('CA'),
			'states' => array('CA' => array(
				'AB'=>'Alberta', 'BC'=>'British Columbia', 'MB'=>'Manitoba', 
				'NB'=>'New Brunswick', 'NF'=>'Newfoundland and Labrador', 
				'NT'=>'Northwest Territories', 'NS'=>'Nova Scotia',
				'NU'=>'Nunavut', 'ON'=>'Ontario', 'PE'	=>'Prince Edward Island',
				'QC'=>'Quebec', 'SK'=>'Saskatchewan', 'YT'=>'Yukon Territory'
			))
		),
		'QU' => array(
			'countries' => array(
				'AT','BE','BG','CY','CZ','DK','EE','FI','FR','DE',
				'GR','HU','IE','IT','LV','LT','LU','MT','NL','PL',
				'PT','RO','SK','SI','ES','SE','GB'
			),
			'states' => array()
		),
		'001' => array(
			'countries' => array(
				'AL','DZ','AD','AO','AI','AG','AR','AM','AW','AU',
				'AZ','BS','BH','BB','BZ','BJ','BM','BT','BO','BA',
				'BW','BR','VG','BN','BF','BI','KH','CV','KY','TD',
				'CL','CN','CO','KM','CK','CR','HR','CD','DJ','DM',
				'DO','EC','SV','ER','ET','FK','FO','FM','FJ','GF',
				'PF','GA','GM','GI','GL','GD','GP','GT','GN','GW',
				'GY','HN','HK','IS','IN','ID','IL','JM','JP','JO',
				'KZ','KE','KI','KW','KG','LA','LS','LI','MG','MW',
				'MY','MV','ML','MH','MQ','MR','MU','YT','MX','MN',
				'MS','MA','MZ','NA','NR','NP','AN','NC','NZ','NI',
				'NE','NU','NF','NO','OM','PW','PA','PG','PE','PH',
				'PN','QA','CG','RE','RU','RW','VC','WS','SM','ST',
				'SA','SN','SC','SL','SG','SB','SO','ZA','KR','LK',
				'SH','KN','LC','PM','SR','SJ','SZ','CH','TW','TJ',
				'TZ','TH','TG','TO','TT','TN','TR','TM','TC','TV',
				'UG','UA','AE','UY','VU','VA','VE','VN','WF','YE',
				'ZM'
				),
			'states' => array()
		)
	);


	/** 
	 * Method builds a list of world countries with ISO codes as key, 
	 * translated country name as value
	 * @return array list of world countries
	 * @todo redo for db 
	 */
	public static function getCountries() {
		$countryTable = new Models_DbTable_Country();
		$countries = Zend_Locale::getTranslationList('territory', null, 2);
		array_pop($countries);
		if (isset($countries['SU'])) {
			unset ($countries['SU']);	
		}
        asort($countries);
		return $countries;
	}
	
	/**
	 * Method returns list of State/Province/Region for given countries
	 * @param string $country official ISO code of country
	 * @return array|null array with list of states or null if given country doesn't have
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
			$data = $stateTable->getAdapter()->fetchAssoc($select);
		}
		return $data;
	}
	
	public static function getStateById($stateId) {
		$stateTable = new Zend_Db_Table('shopping_list_state');
		return $stateTable->find($stateId)->current()->toArray();
	}
	
	public static function getZone($zoneCode = null) {
		if ($zoneCode !== null) {
			if (array_key_exists($zoneCode, self::$_defaultZones)){
				$zone = self::$_defaultZones[$zoneCode];
				foreach ($zone['countries'] as $key => $country){
					$zone['countries'][$country] = Zend_Locale::getTranslation($country, 'Country');
					unset($zone['countries'][$key]);
				}
				return $zone;
			}
		}
		return self::$_defaultZones;
	}

}