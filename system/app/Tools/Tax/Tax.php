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

	/**
	 * Calculates product tax price according to configured rules
	 * @param Models_Model_Product $product             Product model
	 * @param null                 $destinationAddress  If not specified uses default tax rule for calculation
	 * @param bool                 $taxRateOnly         If true returns only appropriate tax rate
	 * @return float|int
	 */
	public static function calculateProductTax(Models_Model_Product $product, $destinationAddress = null, $taxRateOnly = false) {
		if(($taxClass = $product->getTaxClass()) != 0) {
			$rateMethodName = 'getRate' . $taxClass;

			if (null !== $destinationAddress){
				$zoneId = self::getZone($destinationAddress);
				if ($zoneId) {
					$tax = Models_Mapper_Tax::getInstance()->findByZoneId($zoneId);
				}
			} else {
				$tax = Models_Mapper_Tax::getInstance()->getDefaultRule();
			}

			if (isset($tax) && $tax !== null) {
				$productPrice = is_null($product->getCurrentPrice()) ? $product->getPrice() : $product->getCurrentPrice();
                return $taxRateOnly ? $tax->$rateMethodName() : ($productPrice / 100) * $tax->$rateMethodName();
			}
		}
		return 0;
	}

    /**
     * Calculates shipping tax according to configured rules
     * @param float                $shippingPrice       Shipping price
     * @param null                 $destinationAddress  If not specified uses default tax rule for calculation
     * @return float|int
    */

    public static function calculateShippingTax($shippingPrice, $destinationAddress = null) {
        $shippingTaxClass = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('shippingTaxRate');
        if($shippingTaxClass !=  '0' && $shippingTaxClass != null) {
            $getRate = 'getRate'.$shippingTaxClass;
            if (null !== $destinationAddress){
                $zoneId = self::getZone($destinationAddress);
                if ($zoneId) {
                    $tax = Models_Mapper_Tax::getInstance()->findByZoneId($zoneId);
                }
            } else {
                $tax = Models_Mapper_Tax::getInstance()->getDefaultRule();
            }

            if (isset($tax) && $tax !== null) {
                return ($shippingPrice / 100) * $tax->$getRate();
            }
        }
        return 0;
    }

    /**
     * Calculates discount tax according to configured rules
     * @param float                $discountPrice       Discount price
     * @param null                 $destinationAddress  If not specified uses default tax rule for calculation
     * @return float|int
     */

    public static function calculateDiscountTax($discountPrice, $discountTaxRate, $destinationAddress = null) {
        if($discountTaxRate != '0') {
            $getRate = 'getRate'.$discountTaxRate;
            if (null !== $destinationAddress){
                $zoneId = self::getZone($destinationAddress);
                if ($zoneId) {
                    $tax = Models_Mapper_Tax::getInstance()->findByZoneId($zoneId);
                }
            } else {
                $tax = Models_Mapper_Tax::getInstance()->getDefaultRule();
            }

            if (isset($tax) && $tax !== null) {
                return ($discountPrice / 100) * $tax->$getRate();
            }
        }
        return 0;
    }

	/**
	 * Tries to find zone id using all zone types (zip, state, country)
	 *
	 * @return int
	 */
	public static function getZone($address = null, $withTaxable = true) {
		if (is_null($address) || empty($address)){
			return 0;
		} else {
			$address = Tools_Misc::clenupAddress($address);
		}
        $zones = array();
        $zoneMapper = Models_Mapper_Zone::getInstance();
        if($withTaxable){
            $taxableZones = Models_Mapper_Tax::getInstance()->fetchAll();
            if(is_array($taxableZones) && !empty($taxableZones)) {
                foreach($taxableZones as $taxZone){
                    $zoneIds[] =  $taxZone->getZoneId();
                }
                $zones =  $zoneMapper->fetchAll($zoneMapper->getDbTable()->getAdapter()->quoteInto('id IN(?)', $zoneIds));
            }
        }else{
            $zones = $zoneMapper->fetchAll();
        }

		if(is_array($zones) && !empty($zones)) {
			$zoneMatch = 0;
			$maxRate = 0;
			foreach($zones as $zone) {
				$matchRate = 0;

				if (empty($address['country']) && empty($address['state']) && empty($address['zip'])){
					continue;
				}

				$countries = $zone->getCountries(true);
                if ($zone->getZip() && !empty($address['zip'])) {

                    //wildcard zip analyze
                    $zipMatched = false;
                    $wildcardZones = preg_grep('~\*~',  $zone->getZip());
                    if (!empty($wildcardZones)) {
                        foreach ($wildcardZones as $wildcardZone) {
                            $wildcardPosition = strpos($wildcardZone, '*');
                            $currentZip = substr_replace($address['zip'], '', $wildcardPosition);
                            $matchZip = substr_replace($wildcardZone, '', $wildcardPosition);
                            if ($currentZip === $matchZip && in_array($address['country'], $countries)) {
                                $matchRate += 5;
                                $zipMatched = true;
                            }
                        }
                    }

                    if (in_array($address['zip'], $zone->getZip()) && in_array($address['country'], $countries)
                        && !$zipMatched) {
                        $matchRate += 5;
                    } elseif(!$zipMatched) {
                        continue;
                    }
                }
				if (!empty($address['state'])){
					if ($zone->getStates()) {
						$states = array_map(function($state){ return $state['id'];}, $zone->getStates());
						if (in_array($address['state'], $states)) {
							$matchRate += 3;
						}
					}
//@todo Review this scoring algoryhtm. It looks like we don't need this
//                    else {
//                        $matchRate++;
//                    }
				}
				if (!empty($countries)) {
					if (in_array($address['country'], $countries)){
						$matchRate += 1;
					}
				}

				if ($matchRate && $matchRate > $maxRate){
					$maxRate = $matchRate;
					$zoneMatch = $zone->getId();
				}

				unset($countries, $states);
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
