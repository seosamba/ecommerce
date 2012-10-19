<?php
/**
 * Handy tools for work with taxes
 *
 * @author Eugene I. Nezhuta <eugene@seotoaster.com>
 */

class Tools_Tax_Tax {

	const ZONE_TYPE_ZIP          = 'zip';

	const ZONE_TYPE_STATE        = 'state';

	const ZONE_TYPE_COUNTRY      = 'country';

	public static function calculateProductTax(Models_Model_Product $product) {
		if(($taxClass = $product->getTaxClass()) != 0) {
			$rateMethodName = 'getRate' . $taxClass;

			if (null !== ($addrId = Tools_ShoppingCart::getInstance()->getAddressKey(Models_Model_Customer::ADDRESS_TYPE_SHIPPING))){
				$destinationAddress = Tools_ShoppingCart::getInstance()->getAddressById($addrId);
				$zoneId = self::getZone($destinationAddress);
				if ($zoneId) {
					$tax = Models_Mapper_Tax::getInstance()->findByZoneId($zoneId);
				}
			} else {
				self::getZone();
				$tax = Models_Mapper_Tax::getInstance()->getDefaultRule();
			}

			if (isset($tax) && $tax !== null) {
				return ($product->getPrice() / 100) * $tax->$rateMethodName();
			}
		}
		return 0;
	}

	/**
	 * Tries to find zone id using all zone types (zip, state, country)
	 *
	 * @return int
	 */
	public static function getZone($address = null) {
		if (is_null($address)){
			$address = Tools_Misc::clenupAddress(Models_Mapper_ShoppingConfig::getInstance()->getConfigParams());
		}
		$zones = Models_Mapper_Zone::getInstance()->fetchAll();
		if(is_array($zones) && !empty($zones)) {
			$zoneMatch = 0;
			$maxRate = 0;
			foreach($zones as $zone) {
				$matchRate = 0;
//				var_dump($zone->toArray());
				if (!empty($address['zip']) && $zone->getZip() && in_array($address['zip'], $zone->getZip())){
					$matchRate++;
				}
				if (!empty($address['state']) && $zone->getStates() && array_key_exists($address['state'], $zone->getStates())){
					$matchRate++;
				}
				if (in_array($address['country'], $zone->getCountries(true))){
					$matchRate++;
				}
				if ($matchRate && $matchRate > $maxRate){
					$maxRate = $matchRate;
					$zoneMatch = $zone->getId();
				}
			}
			return $zoneMatch;
		}
		return 0;
	}

	/**
	 * Gives zone id by type such as: zip, state, country
	 *
	 * @param Models_Model_Zone $zone
	 * @param string $type
	 * @return int
	 */
	public static function getZoneIdByType(Models_Model_Zone $zone, $type = self::ZONE_TYPE_ZIP, $address = null) {
//		$address = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
		$zoneParts = array();
		switch($type) {
			case self::ZONE_TYPE_ZIP:
				$zoneParts = $zone->getZip();
			break;
			case self::ZONE_TYPE_STATE:
				$zoneParts = $zone->getStates();
			break;
			case self::ZONE_TYPE_COUNTRY:
				$zoneParts = $zone->getCountries(true);
			break;
		}
		if(is_array($zoneParts) && !empty($zoneParts)) {
			if($type == self::ZONE_TYPE_STATE) {
				foreach($zoneParts as $zonePart) {
					if($zonePart['id'] == $address['state']) {
						return $zone->getId();
					}
				}
			}
			if(in_array($address[$type], $zoneParts)) {
				return $zone->getId();
			}
		}
		return 0;
	}

}
