<?php

/**
 * Geo - usefull tools for working with geographical data
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_Geo {

	protected static $_states = array(
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
		)
	);


	/** 
	 * Method builds a list of world countries with ISO codes as key, 
	 * translated country name as value
	 * @return array list of world countries
	 */
	public static function getCountries() {
		$countries = Zend_Locale::getTranslationList('territory', null, 2);
		array_pop($countries);
		if (isset($countries['SU'])) {
			unset ($countries['SU']);	
		}
		return $countries;
	}
	
	/**
	 * Method returns list of State/Province/Region for given countries
	 * @param string $country official ISO code of country
	 * @return array|null array with list of states or null if given country doesn't have
	 */
	public static function getState($country = null) {
		if ($country !== null){
			if (isset(self::$_states[$country])){
				return self::$_states[$country];
			} else {
				return null;
			}
		}
		return self::$_states;
	}

}