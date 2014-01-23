<?php

/**
 * Product
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Model_Product extends Application_Model_Models_Abstract {

	protected $_parentId;
	
	protected $_page;

	protected $_pageTemplate;
	
	protected $_enabled;
	
	protected $_sku;
	
	protected $_name;
	
	protected $_photo;
	
	protected $_mpn;
	
	protected $_price;

    protected $_currentPrice = null;

	protected $_weight;
	
	protected $_brand;
	
	protected $_shortDescription;
	
	protected $_fullDescription;
	
	protected $_taxClass;
	
	protected $_tags;
	
	protected $_defaultOptions = array();
	
	protected $_related;

    protected $_parts = null;

	protected $_createdAt;

	protected $_updatedAt;

	protected $_extraProperties = array();

	protected $_inventory;

    protected $_freeShipping;

    protected $_freebies;

    protected $_groupPriceEnabled = 0;

    protected $_originalPrice = 0;

    public function  __construct(array $options = null) {
        parent::__construct($options);
        $this->notifyObservers();
    }

    public function getPage() {
		return $this->_page;
	}

	public function setPage($_page) {
		$this->_page = $_page;
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

    public function setCurrentPrice($price) {
        $this->_currentPrice = $price;
    }

    public function getCurrentPrice() {
        return $this->_currentPrice;
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

	public function getTags() {
		return $this->_tags;
	}

	public function setTags($_tags) {
		$this->_tags = $_tags;
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
	
	public function getRelated() {
		return $this->_related;
	}

	public function setRelated($_related) {
		$this->_related = $_related;
	}
	
	public function getEnabled() {
		return $this->_enabled;
	}

	public function setEnabled($_enabled) {
		$this->_enabled = $_enabled;
	}

    public function getFreeShipping() {
        return $this->_freeShipping;
    }

    public function setFreeShipping($_freeShipping) {
        $this->_freeShipping = $_freeShipping;
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
						foreach ($newValue as $val){
							if ($val instanceof Application_Model_Models_Abstract){
								$val = $val->toArray();
							}
						}
					}
					$vars[$newKey] = $newValue;
				}
			}
        }
        return $vars;
	}

	public function setCreatedAt($createdAt) {
		$this->_createdAt = $createdAt;
	}

	public function getCreatedAt() {
		return $this->_createdAt;
	}

	public function setUpdatedAt($updatedAt) {
		$this->_updatedAt = $updatedAt;
	}

	public function getUpdatedAt() {
		return $this->_updatedAt;
	}

	public function setExtraProperties($extraProperties) {
		$this->_extraProperties = $extraProperties;
		return $this;
	}

	public function getExtraProperties() {
		return $this->_extraProperties;
	}

	public function addExtraProperty($property){
		$this->_extraProperties[] = $property;
		return $this;
	}

	public function setPageTemplate($templateId) {
		$this->_pageTemplate = $templateId;
		return $this;
	}

	public function getPageTemplate() {
		return $this->_pageTemplate;
	}

	public function setInventory($inventory) {
		$this->_inventory = $inventory;
		return $this;
	}

	public function getInventory() {
		return $this->_inventory;
	}

    public function setParts($parts) {
        $this->_parts = $parts;
        return $this;
    }

    public function getParts() {
        return $this->_parts;
    }

    public function getFreebies() {
        return $this->_freebies;
    }

    public function setFreebies($freebies) {
        $this->_freebies = $freebies;
    }

    public function getGroupPriceEnabled() {
        return $this->_groupPriceEnabled;
    }

    public function setGroupPriceEnabled($groupPriceEnabled) {
        $this->_groupPriceEnabled = $groupPriceEnabled;
    }

    public function getOriginalPrice() {
        return $this->_originalPrice;
    }

    public function setOriginalPrice($originalPrice) {
        $this->_originalPrice = $originalPrice;
    }
}