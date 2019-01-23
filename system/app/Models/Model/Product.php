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

    protected $_isDigital = 0;

    protected $_prodLength = 0;

    protected $_prodWidth = 0;

    protected $_prodDepth = 0;

    protected $_gtin = '';

    protected $_allowance = '';


    public function  __construct(array $options = null) {
        parent::__construct($options);
        $this->notifyObservers();
    }

    public function getPage() {
		return $this->_page;
	}

    /**
     * @param $_page
     * @return $this
     */
	public function setPage($_page) {
		$this->_page = $_page;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getSku() {
		return $this->_sku;
	}

    /**
     * @param $_sku
     * @return $this
     */
	public function setSku($_sku) {
		$this->_sku = $_sku;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getName() {
		return $this->_name;
	}

    /**
     * @param $_name
     * @return $this
     */
	public function setName($_name) {
		$this->_name = $_name;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getMpn() {
		return $this->_mpn;
	}

    /**
     * @param $_mpn
     * @return $this
     */
	public function setMpn($_mpn) {
		$this->_mpn = $_mpn;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getPrice() {
		return $this->_price;
	}

    /**
     * @param $_price
     * @return $this
     */
	public function setPrice($_price) {
		$this->_price = $_price;

        return $this;
	}

    /**
     * @param $price
     * @return $this
     */
    public function setCurrentPrice($price) {
        $this->_currentPrice = $price;

        return $this;
    }

    /**
     * @return |null
     */
    public function getCurrentPrice() {
        return $this->_currentPrice;
    }

    /**
     * @return mixed
     */
    public function getPhoto() {
		return $this->_photo;
	}

    /**
     * @param $_photo
     * @return $this
     */
	public function setPhoto($_photo) {
		$this->_photo = $_photo;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getWeight() {
		return $this->_weight;
	}

    /**
     * @param $_weight
     * @return $this
     */
	public function setWeight($_weight) {
		$this->_weight = $_weight;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getBrand() {
		return $this->_brand;
	}

    /**
     * @param $_brandId
     * @return $this
     */
	public function setBrand($_brandId) {
		$this->_brand = $_brandId;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getShortDescription() {
		return $this->_shortDescription;
	}

    /**
     * @param $_shortDescription
     * @return $this
     */
	public function setShortDescription($_shortDescription) {
		$this->_shortDescription = $_shortDescription;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getFullDescription() {
		return $this->_fullDescription;
	}

    /**
     * @param $_fullDescription
     * @return $this
     */
	public function setFullDescription($_fullDescription) {
		$this->_fullDescription = $_fullDescription;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getTaxClass() {
		return $this->_taxClass;
	}

    /**
     * @param $_tax
     * @return $this
     */
	public function setTaxClass($_tax) {
		$this->_taxClass = $_tax;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getTags() {
		return $this->_tags;
	}

    /**
     * @param $_tags
     * @return $this
     */
	public function setTags($_tags) {
		$this->_tags = $_tags;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getParentId() {
		return $this->_parentId;
	}

    /**
     * @param $_parentId
     * @return $this
     */
	public function setParentId($_parentId) {
		$this->_parentId = $_parentId;

        return $this;
	}

    /**
     * @return array
     */
	public function getDefaultOptions() {
		return $this->_defaultOptions;
	}

    /**
     * @param $_defaultOptions
     * @return $this
     */
	public function setDefaultOptions($_defaultOptions) {
		$this->_defaultOptions = $_defaultOptions;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getRelated() {
		return $this->_related;
	}

    /**
     * @param $_related
     * @return $this
     */
	public function setRelated($_related) {
		$this->_related = $_related;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getEnabled() {
		return $this->_enabled;
	}

    /**
     * @param $_enabled
     * @return $this
     */
	public function setEnabled($_enabled) {
		$this->_enabled = $_enabled;

        return $this;
	}

    /**
     * @return mixed
     */
    public function getFreeShipping() {
        return $this->_freeShipping;
    }

    /**
     * @param $_freeShipping
     * @return $this
     */
    public function setFreeShipping($_freeShipping) {
        $this->_freeShipping = $_freeShipping;

        return $this;
    }

    /**
     * @return array
     */
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

    /**
     * @param $createdAt
     * @return $this
     */
	public function setCreatedAt($createdAt) {
		$this->_createdAt = $createdAt;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getCreatedAt() {
		return $this->_createdAt;
	}

    /**
     * @param $updatedAt
     * @return $this
     */
	public function setUpdatedAt($updatedAt) {
		$this->_updatedAt = $updatedAt;

        return $this;
	}

    /**
     * @return mixed
     */
	public function getUpdatedAt() {
		return $this->_updatedAt;
	}

    /**
     * @param $extraProperties
     * @return $this
     */
	public function setExtraProperties($extraProperties) {
		$this->_extraProperties = $extraProperties;
		return $this;
	}

    /**
     * @return array
     */
	public function getExtraProperties() {
		return $this->_extraProperties;
	}

    /**
     * @param $property
     * @return $this
     */
	public function addExtraProperty($property){
		$this->_extraProperties[] = $property;
		return $this;
	}

    /**
     * @param $templateId
     * @return $this
     */
	public function setPageTemplate($templateId) {
		$this->_pageTemplate = $templateId;
		return $this;
	}

    /**
     * @return mixed
     */
	public function getPageTemplate() {
		return $this->_pageTemplate;
	}

    /**
     * @param $inventory
     * @return $this
     */
	public function setInventory($inventory) {
		$this->_inventory = $inventory;
		return $this;
	}

    /**
     * @return mixed
     */
	public function getInventory() {
		return $this->_inventory;
	}

    /**
     * @param $parts
     * @return $this
     */
    public function setParts($parts) {
        $this->_parts = $parts;
        return $this;
    }

    /**
     * @return |null
     */
    public function getParts() {
        return $this->_parts;
    }

    /**
     * @return mixed
     */
    public function getFreebies() {
        return $this->_freebies;
    }

    /**
     * @param $freebies
     * @return $this
     */
    public function setFreebies($freebies) {
        $this->_freebies = $freebies;

        return $this;
    }

    /**
     * @return int
     */
    public function getGroupPriceEnabled() {
        return $this->_groupPriceEnabled;
    }

    /**
     * @param $groupPriceEnabled
     * @return $this
     */
    public function setGroupPriceEnabled($groupPriceEnabled) {
        $this->_groupPriceEnabled = $groupPriceEnabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginalPrice() {
        return $this->_originalPrice;
    }

    /**
     * @param $originalPrice
     * @return $this
     */
    public function setOriginalPrice($originalPrice) {
        $this->_originalPrice = $originalPrice;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsDigital()
    {
        return $this->_isDigital;
    }

    /**
     * @param $isDigital
     * @return $this
     */
    public function setIsDigital($isDigital)
    {
        $this->_isDigital = $isDigital;

        return $this;
    }

    /**
     * @return int
     */
    public function getProdLength()
    {
        return $this->_prodLength;
    }

    /**
     * @param int $prodLength
     * @return int
     */
    public function setProdLength($prodLength)
    {
        $this->_prodLength = $prodLength;

        return $this;
    }

    /**
     * @return int
     */
    public function getProdWidth()
    {
        return $this->_prodWidth;
    }

    /**
     * @param int $prodWidth
     * @return int
     */
    public function setProdWidth($prodWidth)
    {
        $this->_prodWidth = $prodWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getProdDepth()
    {
        return $this->_prodDepth;
    }

    /**
     * @param int $prodDepth
     * @return int
     */
    public function setProdDepth($prodDepth)
    {
        $this->_prodDepth = $prodDepth;

        return $this;
    }

    /**
     * @return string
     */
    public function getGtin()
    {
        return $this->_gtin;
    }

    /**
     * @param string $gtin
     * @return string
     */
    public function setGtin($gtin)
    {
        $this->_gtin = $gtin;

        return $this;
    }


    /**
     * @return string
     */
    public function getAllowance()
    {
        return $this->_allowance;
    }

    /**
     * @param $allowance
     * @return $this
     */
    public function setAllowance($allowance)
    {
        $this->_allowance = $allowance;

        return $this;
    }
}