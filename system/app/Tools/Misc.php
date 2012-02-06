<?php

/**
 * Misc
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_Misc {

	public static $_weightUnits = array(
		'kg' => 'Kilogram (kg)',
		'lbs' => 'Pound (lbs)'
	);
	
	public static $_currencies = array(
		'USD' => 'U.S. Dollar ($)',
		'EUR' => 'Euro (€)',
		'GBP' => 'British Pound (£)',
		'AUD' => 'Australian Dollar (A$)',
		'CAD' => 'Canadian Dollar (C$)',
		'JPY' => 'Japanese Yen (¥)',
		'NZD' => 'New Zealand Dollar ($)',
		'CHF' => 'Swiss Franc',
		'HKD' => 'Hong Kong Dollar ($)',
		'SGD' => 'Singapore Dollar ($)',
		'SEK' => 'Swedish Krona',
		'DKK' => 'Danish Krone',
		'PLN' => 'Polish Zloty',
		'NOK' => 'Norwegian Krone',
		'HUF' => 'Hungarian Forint',
		'CZK' => 'Czech Koruna',
		'ILS' => 'Israeli New Shekel',
		'MXN' => 'Mexican Peso',
		'BRL' => 'Brazilian Real',
		'MYR' => 'Malaysian Ringgit',
		'PHP' => 'Philippine Peso',
		'TWD' => 'New Taiwan Dollar',
		'THB' => 'Thai Baht',
		'TRY' => 'Turkish Lira'
	);

	public static function getShippingPluginContent($shippingPlugin) {
		$className = ucfirst($shippingPlugin);
		$method    = 'getConfigScreen';
		if(method_exists($className, $method)) {
			return $className::$method();
		}
	}

}