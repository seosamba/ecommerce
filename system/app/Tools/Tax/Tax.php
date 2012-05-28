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
			$zoneId = self::getZoneId();
			if($zoneId) {
				$tax = Models_Mapper_Tax::getInstance()->findByZoneId($zoneId);
				if($tax !== null) {
					$rateMethodName = 'getRate' . $taxClass;
					return ($product->getPrice() / 100) * $tax->$rateMethodName();
				}
			}
		}
		return 0;
	}

	/**
	 * Tries to find zone id using all zone types (zip, state, country)
	 *
	 * @return int
	 */
	public static function getZoneId() {
		$zones = Models_Mapper_Zone::getInstance()->fetchAll();
		if(is_array($zones) && !empty($zones)) {
			foreach($zones as $zone) {
				$zoneIdByZip     = self::getZoneIdByType($zone, self::ZONE_TYPE_ZIP);
				if($zoneIdByZip) {
					return $zoneIdByZip;
				}
				$zoneIdByState   = self::getZoneIdByType($zone, self::ZONE_TYPE_STATE);
				if($zoneIdByState) {
					return $zoneIdByState;
				}
				$zoneIdByCountry = self::getZoneIdByType($zone, self::ZONE_TYPE_COUNTRY);
				if($zoneIdByCountry) {
					return $zoneIdByCountry;
				}
			}
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
	public static function getZoneIdByType(Models_Model_Zone $zone, $type = self::ZONE_TYPE_ZIP) {
		$shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
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
					if($zonePart['id'] == $shoppingConfig['state']) {
						return $zone->getId();
					}
				}
			}
			if(in_array($shoppingConfig[$type], $zoneParts)) {
				return $zone->getId();
			}
		}
		return 0;
	}

}
