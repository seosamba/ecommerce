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
	 * @return array list of world countries
	 * @todo add caching
	 */
	public static function getCountries($pairs = false) {
        $data = array();
        $countriesNames = Zend_Locale::getTranslationList('territory', null, 2);

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
				$marker['address2'],
				$marker['city'],
				$state['state'],
				$marker['zip'],
			)));
			$params['markers'][] = $addressLine;
		}

		$params['markers'] = implode('|', $params['markers']);

		return 'https://maps.googleapis.com/maps/api/staticmap?'.http_build_query($params);
	}

    /**
     * Get coordinates latitude and longitude
     */
    public static function getMapCoordinates($address)
    {
        // replace all the white space with "+" sign to match with google search pattern
        $url = 'http://maps.google.com/maps/api/geocode/json?sensor=false&address=' . str_replace(' ', '+', $address);
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

}
