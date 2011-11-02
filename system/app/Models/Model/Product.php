<?php

/**
 * Product
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Model_Product extends Application_Model_Models_Abstract {

	protected $_id = null;
	
	protected $_parentId;
	
	protected $_pageId;
	
	protected $_sku;
	
	protected $_name;
	
	protected $_photo;
	
	protected $_mpn;
	
	protected $_price;

	protected $_weight;
	
	protected $_brand;
	
	protected $_shortDescription;
	
	protected $_fullDescription;
	
	protected $_taxClass;
	
	protected $_categories;
	
	protected $_defaultOptions;
	
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

	public function getTaxClass() {
		return $this->_taxClass;
	}

	public function setTaxClass($_tax) {
		$this->_taxClass = $_tax;
	}

	public function getCategories() {
		return $this->_categories;
	}

	public function setCategories($_categories) {
		$this->_categories = $_categories;
	}
	
	public function getParentId() {
		return $this->_parentId;
	}

	public function setParentId($_parentId) {
		$this->_parentId = $_parentId;
	}
		
	public function getDefaultOptions() {
		return $this->_defaultOptions;
	}

	public function setDefaultOptions($_defaultOptions) {
		$this->_defaultOptions = $_defaultOptions;
	}
	
	public function toArray() {
		$vars = array();
		$methods = get_class_methods($this);
		$props   = get_class_vars(get_class($this));
        foreach ($props as $key => $value) {
			if ($this->$key instanceof Application_Model_Models_Abstract) {
				$vars[str_replace('_', '', $key)] = $this->$key->toArray();
			} else {
				$method = 'get' . ucfirst($this->_normalizeOptionsKey($key));
				if (in_array($method, $methods)) {
					$newKey = str_replace('_', '', $key);
					$newValue = $this->$method();
					
					if (is_array($newValue)){
						foreach ($newValue as &$val){
							if ($val instanceof Application_Model_Models_Abstract){
								$val = &$val->toArray();
							}
						}
					}
					$vars[$newKey] = $newValue;
				}
			}
        }
        return $vars;
	}

}