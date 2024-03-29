<?php
/**
 * Products REST API controller
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @package Store
 * @since 2.0.0
 */
class Api_Store_Products extends Api_Service_Abstract {

	/**
	 * @var Models_Mapper_ProductMapper Mapper for Products
	 */
	private $_productMapper;

    /**
     * @var Helpers_Action_Language Language helper
     * @ignore
     */
    private $_translator = null;

	/**
	 * @var array Access Control List
	 */
    protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
        Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
        Shopping::ROLE_SALESPERSON => array(
			'allow' => array('get', 'post', 'put', 'delete')
		)
	);

	/**
	 * Initialization
	 * @ignore
	 */
	public function init() {
		parent::init();
		$this->_productMapper   = Models_Mapper_ProductMapper::getInstance();
		$this->_cacheHelper     = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
        $this->_translator      = Zend_Controller_Action_HelperBroker::getStaticHelper('language');
	}

	/**
	 * Get products by giving contitions
	 *
	 * Resourse:
	 * : /api/store/products/id/:id
	 *
	 * Method:
	 * : GET
	 *
	 * ## Parameters:
	 * id (type string)
	 * : Product id to fetch single product
	 *
	 * ## Optional parameters (оnly if ID is not defined)
	 *
	 * limit (type integer)
	 * : Maximum number of results
	 *
	 * offset (type integer)
	 * : Number of results to skip
	 *
	 * count (type boolean)
	 * : Include total result count if true
	 *
	 * key (type string)
	 * : Filter results by keyword. Searches across name, mpn, sku, brand name or tags
	 *
	 * ftag (type array)
	 * : Filter results by given tag IDs
	 *
	 * fbrand (type string)
	 * : Filter results by given brand names
	 *
	 * @return JSON List of products
	 */
	public function getAction() {
		$data = array();
		$id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));
		if (!empty($id)) {
			$product = $this->_productMapper->find($id);

			if ($product instanceof Models_Model_Product) {
				$data = $product->toArray();
			} elseif (is_array($product) && !empty($product)){
				$data = array_map(function($prod){
					return $prod->toArray();
				}, $product);
			} else {
				$this->_error(null, self::REST_STATUS_NOT_FOUND);
			}
		} else {
			$order  = filter_var($this->_request->getParam('order', null), FILTER_SANITIZE_STRING);
			$offset = filter_var($this->_request->getParam('offset', 0), FILTER_SANITIZE_NUMBER_INT);
			$limit  = filter_var($this->_request->getParam('limit', Shopping::PRODUCT_DEFAULT_LIMIT), FILTER_SANITIZE_NUMBER_INT);
            $key    = str_replace('*-amp-*', '&', filter_var($this->_request->getParam('key', null), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
            $count  = filter_var($this->_request->getParam('count', false), FILTER_VALIDATE_BOOLEAN);

			$filter['tags']       = array_filter(filter_var_array((array)$this->_request->getParam('ftag'), FILTER_SANITIZE_NUMBER_INT));
			$filter['brands']     = array_filter(filter_var_array((array)$this->_request->getParam('fbrand'), FILTER_SANITIZE_STRING));
            $filter['inventory']  = filter_var_array((array)$this->_request->getParam('fqty'), FILTER_SANITIZE_STRING);
			$filter['order']     = array_filter(filter_var_array((array)$this->_request->getParam('forder'), FILTER_SANITIZE_STRING));

            if (empty($order) && !empty($filter['order'])) {
                $order = array_unique($filter['order']);
            }

            $organicSearch = filter_var($this->_request->getParam('os', 0), FILTER_SANITIZE_NUMBER_INT);
            if ($key && $organicSearch) {
                $key = explode(' ', $key);
//                $filter['brands'] = $key;
            }

            // if this set to true product mapper will search for products that have all the tags($filter['tags']) at the same time ('AND' logic)
            $strictTagsCount      = (boolean)filter_var($this->_request->getParam('stc', 0), FILTER_SANITIZE_NUMBER_INT);

            $cacheKey             = 'get_product_'.md5(implode(',', $filter['tags']) . implode(',', $filter['brands']) . implode(',', $filter['inventory']) . implode(',', $filter['order']). $offset . $limit . (($organicSearch && is_array($key)) ? md5(implode(',', $key)) : $key) . $count . $strictTagsCount);
			if(($data = $this->_cacheHelper->load($cacheKey, 'store_')) === null) {

				$products = $this->_productMapper->logSelectResultLength($count)->fetchAll(
				    null,
                    $order,
                    $offset,
                    $limit,
                    (bool)$key?$key:null,
					(is_array($filter['tags']) && !empty($filter['tags'])) ? $filter['tags'] : null,
					(is_array($filter['brands']) && !empty($filter['brands'])) ? $filter['brands']: null,
                    $strictTagsCount,
                    $organicSearch,
                    array(),
                    array(),
                    null,
                    true,
                    array(),
                    (is_array($filter['inventory']) && !empty($filter['inventory'])) ? $filter['inventory']: null
                );

				$data = !is_null($products) ? array_map(function($prod){
					//cleanup unnecessary values
					if ($prod->getPage()){
						$prod->setPage(array(
                            'id'         => $prod->getPage()->getId(),
                            'url'        => $prod->getPage()->getUrl(),
                            'templateId' => $prod->getPage()->getTemplateId()
                        ));
					}
					return $prod->toArray();
				}, $products) : array();

				if ($count) {
					$data = array(
						'totalCount' => $this->_productMapper->lastSelectResultLength(),
						'count'      => sizeof($data),
						'data'       => $data
					);
				}

				$this->_cacheHelper->save($cacheKey, $data, 'store_', array('productlist'), Helpers_Action_Cache::CACHE_NORMAL);
			}
		}

		return $data;
	}


	/**
	 * New product creation
	 *
	 * Resourse:
	 * : /api/store/products/
	 *
	 * HttpMethod:
	 * : POST
	 *
	 * @return JSON New product model
	 */
	public function postAction() {
		$srcData = Zend_Json_Decoder::decode($this->_request->getRawBody());
		$validator = new Zend_Validate_Db_NoRecordExists(array(
			'table' => 'shopping_product',
			'field' => 'sku'
		));

        $configMapper = Models_Mapper_ShoppingConfig::getInstance();
        $productSizeMandatory = $configMapper->getConfigParam('productSizeMandatory');
        $productWeightMandatory = $configMapper->getConfigParam('productWeightMandatory');

        if (!empty($productSizeMandatory)) {
            if (empty($srcData['prodLength']) || !is_numeric($srcData['prodLength']) || $srcData['prodLength'] <= 0) {
                $this->_error(htmlentities($this->_translator->translate('Product length is missing. Please check product dimensions tab.')), self::REST_STATUS_BAD_REQUEST);
            }
            if (empty($srcData['prodWidth']) || !is_numeric($srcData['prodWidth']) || $srcData['prodWidth'] <= 0) {
                $this->_error(htmlentities($this->_translator->translate('Product width is missing. Please check product dimensions tab.')), self::REST_STATUS_BAD_REQUEST);
            }
            if (empty($srcData['prodDepth']) || !is_numeric($srcData['prodDepth']) || $srcData['prodDepth'] <= 0) {
                $this->_error(htmlentities($this->_translator->translate('Product depth is missing. Please check product dimensions tab.')), self::REST_STATUS_BAD_REQUEST);
            }
        }

        if (!empty($productWeightMandatory)) {
            if (empty($srcData['weight']) || !is_numeric($srcData['weight']) || $srcData['weight'] <= 0) {
                $this->_error(htmlentities($this->_translator->translate('Product weight is missing.')), self::REST_STATUS_BAD_REQUEST);
            }
        }

        if (!$validator->isValid($srcData['sku'])){
	        $this->_error(htmlentities($this->_translator->translate('You already have a product with this SKU')), self::REST_STATUS_BAD_REQUEST);
        }

        try {
            $newProduct = $this->_productMapper->save($srcData);
        } catch (Exception $e){
	        $this->_error($e->getMessage(), self::REST_STATUS_OK);
        }

		if ($newProduct instanceof Models_Model_Product){
			return $newProduct->toArray();
		}
		$this->_error();
	}

	/**
	 * Update product model fields
	 *
	 * Resourse:
	 * : /api/store/products/
	 *
	 * HttpMethod:
	 * : PUT
	 *
	 * @return JSON Updated product model
	 */
	public function putAction() {
		$data = array();
		$id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));
		$srcData = json_decode($this->_request->getRawBody(), true);
		if (empty($srcData)){
			$this->_error('Empty data');
		}

        $configMapper = Models_Mapper_ShoppingConfig::getInstance();
        $productSizeMandatory = $configMapper->getConfigParam('productSizeMandatory');
        $productWeightMandatory = $configMapper->getConfigParam('productWeightMandatory');

        if (!empty($productSizeMandatory)) {
            if (empty($srcData['prodLength']) || !is_numeric($srcData['prodLength']) || $srcData['prodLength'] <= 0) {
                $this->_error(htmlentities($this->_translator->translate('Product length is missing. Please check product dimensions tab.')), self::REST_STATUS_BAD_REQUEST);
            }
            if (empty($srcData['prodWidth']) || !is_numeric($srcData['prodWidth']) || $srcData['prodWidth'] <= 0) {
                $this->_error(htmlentities($this->_translator->translate('Product width is missing. Please check product dimensions tab.')), self::REST_STATUS_BAD_REQUEST);
            }
            if (empty($srcData['prodDepth']) || !is_numeric($srcData['prodDepth']) || $srcData['prodDepth'] <= 0) {
                $this->_error(htmlentities($this->_translator->translate('Product depth is missing. Please check product dimensions tab.')), self::REST_STATUS_BAD_REQUEST);
            }
        }

        if (!empty($productWeightMandatory)) {
            if (empty($srcData['weight']) || !is_numeric($srcData['weight']) || $srcData['weight'] <= 0) {
                $this->_error(htmlentities($this->_translator->translate('Product weight is missing.')), self::REST_STATUS_BAD_REQUEST);
            }
        }

        if(empty($srcData['negativeStock'])) {
            if(gmp_sign((int)$srcData['inventory']) < 0) {
                $this->_error(htmlentities($this->_translator->translate('You can\'t save product with negative qty, please enter positive qty of product or enable "Negative stock" checkbox')), self::REST_STATUS_BAD_REQUEST);
            }
        }

		if (!empty($id)){
			$products = $this->_productMapper->find($id);

			if($products instanceof Models_Model_Product){
                if($products->getSku() != $srcData['sku']){
                    $validator = new Zend_Validate_Db_NoRecordExists(array(
                        'table' => 'shopping_product',
                        'field' => 'sku'
                    ));

                    if (!$validator->isValid($srcData['sku'])){
                        $this->_error(htmlentities($this->_translator->translate('You already have a product with this SKU')), self::REST_STATUS_BAD_REQUEST);
                    }

                }
            }

			!is_array($products) && $products = array($products);
			if (isset($srcData['id'])){
				unset($srcData['id']);
			}
		} else {
			$key    = $this->_request->getParam('key');
			if (!is_null($key)){
				$key = filter_var($key, FILTER_SANITIZE_STRING);
			}
			$tags   = $this->_request->getParam('ftag');
			if ($tags){
				$tags = filter_var_array($tags, FILTER_SANITIZE_NUMBER_INT);
			}
			$brands  = $this->_request->getParam('fbrand');
			if ($brands){
				$brands = filter_var_array($brands, FILTER_SANITIZE_STRING);
			}

            $inventory  = $this->_request->getParam('fqty');
            if ($inventory){
                $inventory = filter_var_array($inventory, FILTER_SANITIZE_STRING);
            }

			$products = $this->_productMapper->fetchAll(null, array(), null, null, $key, $tags, $brands, false, false, array(), array(), null, false, array(), $inventory);
		}

		if (!empty($products)){
            $allowanceProductsMapper = Store_Mapper_AllowanceProductsMapper::getInstance();
            $filteringMapper = Filtering_Mappers_Eav::getInstance();

			foreach ($products as $product) {
			    $productId = $product->getId();
                $allowanceProduct = $allowanceProductsMapper->findByProductId($productId);

                if($allowanceProduct instanceof Store_Model_AllowanceProducts && empty($srcData['allowance'])) {
                    $allowanceProductsMapper->deleteByProductId($productId);
                }

				$product->setOptions($srcData);
                $currentProductWithSkuModel = $this->_productMapper->findBySku($product->getSku());
                if ($currentProductWithSkuModel instanceof Models_Model_Product) {
                    if ($productId != $currentProductWithSkuModel->getId()) {
                        $this->_error('Product with the same sku already exists');
                    }
                }
                try {
                    if ($this->_productMapper->save($product)) {
                        $productAttributes = $filteringMapper->getAttributes($productId);
                        $productTags = $product->getTags();
                        if(!empty($productAttributes) && !empty($productTags)) {
                            foreach ($productAttributes as $attribute) {
                               $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                               $sql = "INSERT IGNORE INTO shopping_filtering_tags_has_attributes (attribute_id, tag_id) VALUES (:attribute_id, :tag_id)";
                                foreach ($productTags as $tag) {
                                    $dbAdapter->query($sql, array('attribute_id' => $attribute['attribute_id'], 'tag_id' => $tag['id']));
                                }
                            }
                        }

                        $data[] = $product->toArray();
                    }
                } catch (Exception $e) {
                    $this->_error('Something went wrong. Please contact support.');
                }
			}

			if (count($data) === 1){
				$data = array_shift($data);
			}

			return $data;
		}
	}


	/**
	 * Remove product
	 *
	 * Resourse:
	 * : /api/store/products/
	 *
	 * HttpMethod:
	 * : DELETE
	 *
	 * ## Parameters:
	 * id (type string)
	 * : Id of product to delete
	 *
	 * ## Optional parameters (оnly if ID is not defined)
	 *
	 * key (type string)
     * : Filter results by keyword. Searches across name, mpn, sku, brand name or tags.
     *
     * ftag (type array)
     * : Filter results by given tag IDs
     *
     * fbrand (type string)
     * : Filter results by given brand names
	 *
	 * @return JSON Result of deletion
	 */
	public function deleteAction() {
		$ids = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));

		if (!empty($ids)) {
			$products = $this->_productMapper->find($ids);
		} else {
			$key    = filter_var($this->_request->getParam('key'), FILTER_SANITIZE_STRING);
			$tags   = filter_var_array($this->_request->getParam('ftag', array()), FILTER_SANITIZE_NUMBER_INT);
			$brands  = filter_var_array($this->_request->getParam('fbrand', array()), FILTER_SANITIZE_STRING);
			$inventory  = filter_var_array($this->_request->getParam('fqty', array()), FILTER_SANITIZE_STRING);
			if (empty($key) && empty($tags) && empty($brands) && empty($inventory)){
				return array(
					'error'		=> true,
					'code'		=> 400,
					'message'	=> 'Bad request'
				);
				$this->_error();
			}

			$products = $this->_productMapper->fetchAll(null, array(), null, null, $key, $tags, $brands, false, false, array(), array(), null, false, array(), $inventory);
		}

		if (isset($products) && !is_null($products)) {
			!is_array($products) && $products = array($products);
			$results = array();
			foreach ($products as $product){
				$results[$product->getId()] = $this->_productMapper->delete($product);
                unset($product);
			}
            if (!empty($results) && in_array(false, $results)){
                 $this->_error($results);
            }
			return $results;
		} else {
			$this->_error('Requested product not found', self::REST_STATUS_NOT_FOUND);
		}
	}


}
