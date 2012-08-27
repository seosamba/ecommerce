<?php
/**
 * Shipping plugin interface
 */
interface Interfaces_Shipping {

	/**
	 * Main method to interact with shipper API server
	 * Returns a result - collection of rates in format:
	 * array(
	 *  'price' => 10.99,
	 *  'type'  => 'Name of service',
	 *  'descr' => 'Description of delivery method
	 * );
	 */
	public function calculateAction();

	/**
	 * Method that returns config form and process config saving.
	 * Process submited for via POST request
	 * @return text/html
	 */
	public function configAction();

}
