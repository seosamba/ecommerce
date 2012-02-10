<?php
/**
 *
 *
 * @author Eugene I. Nezhuta <eugene@seotoaster.com>
 */
interface Interfaces_Shipping {

	/**
	 * Setting origination address from array
	 */
	public function setOrigination(array $address);

	/**
     * Setting destination address from array
     */
	public function setDestination(array $address);

	/**
     * Setting weight and units
     */
    public function setWeight($weight, $unit = '');

	/**
     * Main method to interact with shipper API server and return a result
     */
    public function run();

	public static function getConfigScreen();
}
