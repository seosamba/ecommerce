<?php

/**
 * Product
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Model_Product extends Application_Model_Models_Abstract {

	protected $_id = null;
	
	private $_pageId;
	
	private $_sku;
	
	private $_name;
	
	private $_mpn;
	
	private $_price;

	private $_photo;
	
	private $_weight;
	
	private $_brand;
	
	private $_shortDescription;
	
	private $_fullDescription;
	
	private $_tax;
	
	private $_categories;
	
	public function getId() {
		return $this->_id;
	}

	public function setId($_id) {
		$this->_id = $_id;
	}

	public function getPageId() {
		return $this->_pageId;
	}

	public function setPageId($_pageId) {
		$this->_pageId = $_pageId;
	}

	public function getSku() {
		return $this->_sku;
	}

	public function setSku($_sku) {
		$this->_sku = $_sku;
	}

	public function getName() {
		return $this->_name;
	}

	public function setName($_name) {
		$this->_name = $_name;
	}

	public function getMpn() {
		return $this->_mpn;
	}

	public function setMpn($_mpn) {
		$this->_mpn = $_mpn;
	}

	public function getPrice() {
		return $this->_price;
	}

	public function setPrice($_price) {
		$this->_price = $_price;
	}

	public function getPhoto() {
		return $this->_photo;
	}

	public function setPhoto($_photo) {
		$this->_photo = $_photo;
	}

	public function getWeight() {
		return $this->_weight;
	}

	public function setWeight($_weight) {
		$this->_weight = $_weight;
	}

	public function getBrand() {
		return $this->_brand;
	}

	public function setBrand($_brandId) {
		$this->_brand = $_brandId;
	}

	public function getShortDescription() {
		return $this->_shortDescription;
	}

	public function setShortDescription($_shortDescription) {
		$this->_shortDescription = $_shortDescription;
	}

	public function getFullDescription() {
		return $this->_fullDescription;
	}

	public function setFullDescription($_fullDescription) {
		$this->_fullDescription = $_fullDescription;
	}

	public function getTax() {
		return $this->_tax;
	}

	public function setTax($_tax) {
		$this->_tax = $_tax;
	}

	public function getCategories() {
		return $this->_categories;
	}

	public function setCategories($_categories) {
		$this->_categories = $_categories;
	}


}