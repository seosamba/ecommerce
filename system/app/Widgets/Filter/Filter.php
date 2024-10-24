<?php

/**
 * Filter.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Filter_Filter extends Widgets_Abstract
{
    const CACHE_KEY_SETTINGS = 'settings';

    const CACHE_KEY_OTHERS_ARRAY = 'others_filter_';

    const OPTION_PRICE_SLIDER = 'price-slider';

    const FILTER_OTHERS = '_other';

    const FILTER_READONLY = 'readonly';

    /**
     * Show all filter values without All others group
     */
    const FILTER_ALLITEMS = 'allitems';

    const WITHOUT_OPTION_COUNTER = 'without-option-counter';

    private $_allowedOptions = array(
        'builder', 'product', 'attribute'
    );

    protected function _init()
    {
        parent::_init();

        $this->_cachePrefix = strtolower(__CLASS__);

        // disable cache if we are in development environment
        $this->_cacheable = !(APPLICATION_ENV === 'development');

        if ((!empty($this->_options) && $this->_options[0] === 'builder') || (!empty($_SERVER['REQUEST_URI']))) {
            // disabling cache if rendering builder widget
            $this->_cacheable = false;
        }

        $this->_view = new Zend_View();
        $this->_view->setScriptPath(__DIR__ . '/views/');
        $this->_websiteUrl = Zend_Controller_Action_HelperBroker::getStaticHelper('website')->getUrl();
        $this->_view->websiteUrl = $this->_websiteUrl;
    }


    protected function _load()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided. Usage: '
                . implode(', ', $this->_allowedOptions));
        }

        // check if first option is allowed method
        if (in_array($this->_options[0], $this->_allowedOptions)) {
            $method = array_shift($this->_options);
            $method = '_render' . ucfirst(strtolower($method));
            if (method_exists($this, $method)) {
                // render widget
                return $this->$method();
            }
        }

        // render default
        return $this->_renderProduct();
    }

    private function _renderBuilder()
    {
        if (!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT)) {
            return '';
        }
        $layout = Zend_Layout::getMvcInstance()->getView();
        $layout->headScript()
            ->appendFile($this->_websiteUrl . 'system/js/external/underscore/underscore.min.js')
            ->appendFile($this->_websiteUrl . 'system/js/external/backbone/backbone.min.js')
            ->appendFile(
                $this->_websiteUrl . 'plugins/shopping/web/js/modules/filtering/filtering-builder' . (APPLICATION_ENV === 'production' ? '.min' : '') . '.js'
            );
        $layout->headLink()
            ->appendStylesheet($this->_websiteUrl . 'system/css/seotoaster-ui.css')
            ->appendStylesheet($this->_websiteUrl . 'system/css/icons.css');

        $mapper = Filtering_Mappers_Eav::getInstance();

        $pageId = intval($this->_toasterOptions['id']);
        $product = Models_Mapper_ProductMapper::getInstance()->findByPageId($pageId);
        if (!$product instanceof Models_Model_Product) {
            throw new Exceptions_SeotoasterWidgetException('This is not a product page');
        }

        $this->_view->productId = $product->getId();

        $productTags = $product->getTags();
        $this->_view->tags = $productTags;

        $tagIds = array();
        if(!empty($productTags)) {
            foreach ($productTags as $tag) {
                $tagIds[] = $tag['id'];
            }
        }

        $currentFilters = $mapper->getAttributes($product->getId());

        if(!empty($currentFilters) && !empty($tagIds)) {
            $attributes = array();
            foreach ($currentFilters as $key => $filter) {
                $filter['tags'] = $tagIds;

                if(array_key_exists($filter['attribute_id'], $attributes)) {
                    $attributes[$filter['attribute_id']]['id'] .= ',' . $filter['id'];
                    $attributes[$filter['attribute_id']]['value'] .= Filtering_Mappers_Eav::ATTRIBUTE_VALUE_SEPARATOR . $filter['value'];
                } else {
                    $attributes[$filter['attribute_id']] = $filter;
                }
            }

            $currentFilters = $attributes;
        }

        $this->_view->currentFilters = $currentFilters;

        return $this->_view->render('builder.phtml');
    }

    private function _renderProduct()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('Filter widget: no options provided');
        }

        $options = array();
        $priceTax = '';
        $useProduct = false;
        $additionalAttributeName = '';
        $additionalAttributeLabel = '';
        $showAllItems = false;
        $withoutOptionCounter = false;
        foreach ($this->_options as $option) {
            if (preg_match('/^(brands|tagnames|order)-(.*)$/u', $option, $parts)) {
                $options[$parts[1]] = explode(',', $parts[2]);
            }
            if(preg_match('/(tax)$/u', $option)){
                $priceTax = Filtering_Mappers_Filter::getInstance()->getTaxRate();
                if($priceTax !== null){
                   $priceTax = $priceTax[0]['rate1'];
                }
            }

            if(in_array($option, Filtering_Tools::$allowedAdditionalOptions)) {
                $additionalAttributeData = Filtering_Mappers_Eav::getInstance()->findAttributeDataByName(strtolower($option));
                if(!empty($additionalAttributeData)) {
                    $additionalAttributeName = $additionalAttributeData['name'];
                    $additionalAttributeLabel =  $additionalAttributeData['label'];
                    $useProduct = true;
                }
            }

            if(in_array(self::FILTER_ALLITEMS, $this->_options)) {
                $showAllItems = true;
            }

            if(in_array(self::WITHOUT_OPTION_COUNTER, $this->_options)) {
                $withoutOptionCounter = true;
            }
        }

        if (empty($options['tagnames'])) {
            throw new Exceptions_SeotoasterWidgetException('Filter widget: at least one tag name should be provided');
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $this->_tags = Models_Mapper_Tag::getInstance()->findByName($options['tagnames'], true);

        if (empty($this->_tags)) {
            return 'Given tags not found';
        }

        $tagIds = array_keys($this->_tags);
        $filtersBrands = array();
        $productBrandTags = array();
        $priceFilter = array();
        $productsIds = array();

        // generating filter id
        $filterId = implode('_', array_merge(array($this->_toasterOptions['id']), $options['tagnames']));
        $filterId = substr(md5($filterId), 0, 16);
        $this->_view->filterId = $filterId;



        if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT) && $request->isPost()) {
            $data = $request->getParam('show', array());

            $useSortData = $request->getParam('useSortData', array());
            $sortedFiltersAttributes = array();

            if(!empty($useSortData)) {
                $countParams = count($useSortData);
                asort($useSortData, SORT_STRING);

                $i = 1;
                foreach ($useSortData as $key => $param) {
                    if(empty($param)) {
                        $sortedFiltersAttributes[$key] = $countParams+$i;
                        //return 'Please fill all sorted inputs!';
                    } else {
                        $sortedFiltersAttributes[$key] = $param;
                    }
                    $i++;
                }

                asort($sortedFiltersAttributes, SORT_NUMERIC);

                $data['useSortData'] = $sortedFiltersAttributes;
            }

            $usesortValuesData = $request->getParam('usesortValuesData', array());
            $sortedFiltersAttributes = array();

            if(!empty($usesortValuesData)) {
                foreach ($usesortValuesData as $key => $params) {
                    $countParams = count($params);
                    asort($params, SORT_STRING);

                    $i = 1;
                    foreach ($params as $pKey => $param) {
                        if(empty($param)) {
                            $sortedFiltersAttributes[$key][$pKey] = $countParams+$i;
                        } else {
                            $sortedFiltersAttributes[$key][$pKey] = (int) $param;
                        }
                        $i++;
                    }
                }

                asort($sortedFiltersAttributes, SORT_NUMERIC);

                foreach ($sortedFiltersAttributes as $filterName => $filterValues) {
                    asort($filterValues, SORT_NUMERIC);
                    $sortedFiltersAttributes[$filterName] = $filterValues;
                }

                $data['usesortValuesData'] = $sortedFiltersAttributes;
            }

            Filtering_Mappers_Filter::getInstance()->saveSettings($filterId, $data);
        }

        $widgetSettings = Filtering_Mappers_Filter::getInstance()->getSettings($filterId);

        $usesort = false;
        $usesortData = array();
        $usesortvalues = false;
        $usesortValuesData = array();
        if(in_array('usesort', $this->_options)) {
            if(!empty($widgetSettings['useSortData'])) {
                $usesort = true;
                $usesortData = $widgetSettings['useSortData'];
            }

            if(in_array('usesortvalues', $this->_options)) {
                $usesortvalues = true;
                if(!empty($widgetSettings['usesortValuesData'])) {
                    $usesortValuesData = $widgetSettings['usesortValuesData'];
                }
            }
        }

        $this->_view->usesort = $usesort;
        $this->_view->usesortData = $usesortData;
        $this->_view->usesortvalues = $usesortvalues;
        $this->_view->usesortValuesData = $usesortValuesData;

        $this->_widgetSettings = $widgetSettings;

        // fetch list filters by given tags
        $eavMapper = Filtering_Mappers_Eav::getInstance();

        $listFilters = $eavMapper->findListFiltersByTags($tagIds);

        $smartProductlistFilter = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('smartFilter');

        if(!empty($smartProductlistFilter)) {
            $urlFilter = Filtering_Tools::normalizeFilterQuery();

            if(!empty($urlFilter['category']) && !empty($this->_tags)) {
                foreach ($this->_tags as $tagId => $tagName) {
                    if(!in_array($tagName, $urlFilter['category'])) {
                        unset($this->_tags[$tagId]);
                    } else {
                        array_push($this->_cacheTags, 'prodtag_' . $tagId);
                    }
                }

                $tagIds = array_keys($this->_tags);
            }

            if (!empty($urlFilter['brand'])) {
                $filtersBrands = $urlFilter['brand'];
                unset($urlFilter['brand']);
            }

            if (!empty($filtersBrands)) {
                foreach ($filtersBrands as $brand) {
                    array_push($this->_cacheTags, 'prodbrand_' . $brand);
                }
            }

            if(!empty($urlFilter['price'])) {
                $tax = Filtering_Mappers_Filter::getInstance()->getTaxRate();
                if($tax !== null){
                    $tax = $tax[0]['rate1'];
                }

                $priceFilter = Tools_GroupPriceTools::reduceFilterPrice($urlFilter['price'], $tax, $tagIds);
            }

            $productIds = Filtering_Mappers_Eav::getInstance()->findProductIdsByAttributes($urlFilter);

            $idsWhere = Zend_Db_Table_Abstract::getDefaultAdapter()->quoteInto('p.id IN (?)', $productIds);

            $enabledOnly = Models_Mapper_ProductMapper::getInstance()->getDbTable()->getAdapter()->quoteInto('p.enabled=?', '1');

            if(!empty($productIds)) {
                $enabledOnly = $idsWhere . ' AND ' . $enabledOnly;
            }

            $filters = array(
                'tags'   => $tagIds,
                'brands' => $filtersBrands,
                'order'  => null
            );

            $tagIdsToFind = array();
            $tagIdsTmp = array();

            $data = Models_Mapper_ProductMapper::getInstance()->fetchAllProductByParams(
                $enabledOnly,
                $filters['order'],
                0,
                null,//$limit
                null,
                $filters['tags'],
                $filtersBrands,
                false,
                false,
                array(),
                $priceFilter,
                'DESC',//$orderSql
                false,
                array()//$productPriceFilter
            );

            if(!empty($data)) {
                foreach ($data as $product) {
                    $productsIds[] = $product['id'];

                    $tags = $product['tags'];
                    if(!empty($tags)) {
                        foreach ($tags as $tag) {
                            $productBrandTags[] = $tag['id'];
                            if(!in_array($tag['id'] , $tagIdsTmp)) {
                                array_push($tagIdsTmp, $tag['id']);
                            }
                        }
                    }
                }
            }

            if(!empty($tagIdsTmp) && !empty($tagIds)) {
                $tagIdsToFind = $tagIds;
                foreach ($tagIdsToFind as $key => $tagId) {
                    if(!in_array($tagId, $tagIdsTmp)) {
                        unset($tagIdsToFind[$key]);
                    }
                }
                $tagIds = $tagIdsToFind;
            }

            if(!empty($tagIdsToFind)) {
                $listFilters = $eavMapper->findListFiltersByTags($tagIdsToFind, $productsIds);
            }
        }

        $rangeFilters = $eavMapper->findRangeFiltersByTags($tagIds, $widgetSettings);

        $this->_filters = array_merge($rangeFilters, $listFilters);
        // fetch price range for filters
        $this->_priceRange = $eavMapper->getPriceRange($tagIds, $productsIds);

        $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
        $currentUser = $sessionHelper->getCurrentUser()->getId();

        $dbTable = new Models_DbTable_CustomerInfo();
        $select = $dbTable->select()->from('shopping_customer_info', array('user_id', 'group_id'));
        $allCustomersGroups =  $dbTable->getAdapter()->fetchAssoc($select);

        if(!empty($allCustomersGroups)) {
            if(array_key_exists($currentUser, $allCustomersGroups)){
                $groupId = $allCustomersGroups[$currentUser]['group_id'];
                $allProductsGroups = Store_Mapper_GroupMapper::getInstance()->fetchAssocAll();
                if(isset($allProductsGroups[$groupId])){
                    if(!empty($this->_priceRange)) {
                        foreach ($this->_priceRange as $key => $range) {
                            $priceNow = $range;
                            $priceValue = $allProductsGroups[$groupId]['priceValue'];
                            $priceSign  = $allProductsGroups[$groupId]['priceSign'];
                            $priceType  = $allProductsGroups[$groupId]['priceType'];
                            $nonTaxable = $allProductsGroups[$groupId]['nonTaxable'];

                            if($priceType == 'percent'){
                                $priceModificationValue = ($priceNow*$priceValue)/100;
                            }
                            if($priceType == 'unit'){
                                $priceModificationValue = $priceValue;
                            }

                            if($priceSign == 'minus'){
                                $resultPrice = $priceNow - $priceModificationValue;
                            }
                            if($priceSign == 'plus'){
                                $resultPrice = $priceNow + $priceModificationValue;
                            }

                            if(empty($nonTaxable)) {
                                $tax = Filtering_Mappers_Filter::getInstance()->getTaxRate();
                                if($tax !== null){
                                    $tax = $tax[0]['rate1'];

                                    $resultPriceParam = ($resultPrice*$tax)/100;
                                    $resultPrice = $resultPrice + $resultPriceParam;
                                }
                            }

                            $this->_priceRange['group'][$key] = $resultPrice;
                        }
                    }
                }
            }
        }

        if(!isset($priceTax) || empty($priceTax)){
            $this->_priceRange['min'] = floor($this->_priceRange['min']);
            $this->_priceRange['max'] = ceil($this->_priceRange['max']);
        }
        $this->_priceRange['name'] = 'price';
        $this->_priceRange['label'] = 'Price';

        $productPriceRange = array();
        $this->_view->useProduct = false;
        if($useProduct) {
            $this->_view->useProduct = true;
            $this->_view->additionalAttributeName = $additionalAttributeName;
            $this->_view->additionalAttributeLabel = $additionalAttributeLabel;

            $productPriceData = $eavMapper->getPriceRangeForProduct($tagIds, $additionalAttributeName);
            if(!empty($productPriceData)) {
                $productPriceRange['min'] = floor(min($productPriceData));
                $productPriceRange['max'] = ceil(max($productPriceData));
            }

            $productPriceRange['name'] = $additionalAttributeName;
            $productPriceRange['label'] = ucfirst($additionalAttributeLabel);
        }

        $this->productPriceRange = $productPriceRange;

        $brands = $eavMapper->getBrands($tagIds, $productsIds);

        $tmpBrandsArr = array();
        if(!empty($brands)) {
            foreach ($brands as $pId => $brandName) {
                $tmpBrandsArr[$brandName] += 1;
            }
            arsort($tmpBrandsArr);
        }

        $this->_brands = $tmpBrandsArr;

        // if this user allowed to manage content
        if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT) && !$request->has('filter_preview')) {
            // render editable filter widget
            return $this->_renderWidgetEdit();
        }

        // get applied filters from query
        $appliedFilters = Filtering_Tools::normalizeFilterQuery();
        $this->_view->appliedFilters = $appliedFilters;

        // mark disabled filters
        $clearedFilters = array_filter(
            array_map(
                function ($filter) use ($appliedFilters, $widgetSettings) {
                    if (isset($widgetSettings[$filter['name']]) && !is_array($widgetSettings[$filter['name']])) {
                        return null;
                    }
                    if (isset($appliedFilters[$filter['name']])) {
                        if (isset($filter['values'])) {
                            $filter['checked'] = $appliedFilters[$filter['name']];
                        } else {
                            $filter['from'] = $appliedFilters[$filter['name']]['from'];
                            $filter['to'] = $appliedFilters[$filter['name']]['to'];
                        }
                    } else {
                        $filter['checked'] = array();
                    }

                    return $filter;
                },
                $this->_filters
            )
        );

        if(!empty($widgetSettings) && !empty($clearedFilters)) {
            $this->_view->filters = Tools_Misc::processProductFilters($widgetSettings, $clearedFilters);
        }

        if(!empty($this->_tags) && !empty($productBrandTags)) {
            foreach ($this->_tags as $tagId => $tagValue) {
                if(!in_array($tagId, $productBrandTags)) {
                    unset($this->_tags[$tagId]);
                }
            }
        }

        if (!empty($widgetSettings['tags'])) {
            $showList = $widgetSettings['tags'];
            if(is_array($showList)) {
                $tagValues = array_filter(
                    $this->_tags,
                    function ($tag) use ($showList) {
                        return array_key_exists($tag, $showList);
                    }
                );
            }else{
                $tagValues = array();
            }
            unset($showList);
        } else {
            $tagValues = array_values($this->_tags);
        }
        // assign tags to view with checked attributes
        $this->_view->tags = array(
            'name' => 'category',
            'values' => $tagValues,
            'checked' => !empty($appliedFilters['category']) ? $appliedFilters['category'] : array(),
            'nocount' => true,
            'withoutOptionCounter' => true
        );

        if (!empty($widgetSettings['brands'])) {
            $brandValues = array();
            if (is_array($widgetSettings['brands'])) {
                foreach ($this->_brands as $brandName => $itemCount) {
                    if (!array_key_exists($brandName, $widgetSettings['brands'])) {
                        continue;
                    }
                    $brandValues[$brandName] = $itemCount;
                }
            }
        } else {
            $brandValues = $this->_brands;
        }
        // assign brands to view with checked attributes
        $this->_view->brands = array(
            'name' => 'brand',
            'values' => $brandValues,
            'checked' => !empty($appliedFilters['brand']) ? $appliedFilters['brand'] : array(),
            'withoutOptionCounter' => $withoutOptionCounter
        );

        if(isset($priceTax) && !empty($priceTax)){
            $this->_view->priceTax = $priceTax;
        }

        // apply user values to price range filter
        if (!empty($appliedFilters['price'])) {
            $this->_priceRange = array_merge($this->_priceRange, $appliedFilters['price']);
            unset($appliedFilters['price'], $price);
        }
        if (!isset($widgetSettings['price']) || !empty($widgetSettings['price'])) {
            $this->_view->priceRange = $this->_priceRange;
        }

        if (!empty($appliedFilters['productsqft'])) {
            $this->productPriceRange = array_merge($this->productPriceRange, $appliedFilters['productsqft']);
            unset($appliedFilters['productsqft'], $price);
        }

        if (!isset($widgetSettings['productsqft']) || !empty($widgetSettings['productsqft'])) {
            $this->_view->productPriceRange = $this->productPriceRange;
        }

        if($showAllItems) {
            $this->_view->showAllItems = $showAllItems;
        }

        $this->_view->withoutOptionCounter = $withoutOptionCounter;

        return $this->_view->render('filter-widget.phtml');
    }

    private function _renderWidgetEdit ()
    {
        $layout = Zend_Layout::getMvcInstance()->getView();
        $layout->headScript()->appendFile(
            $this->_websiteUrl . 'plugins/shopping/web/js/modules/filtering/filtering-product' . (APPLICATION_ENV === 'production' ? '.min' : '') . '.js'
        );

        $widgetSettings = $this->_widgetSettings;
        $this->_view->settings = $widgetSettings;

        $this->_view->tags = $this->_tags;

        $this->_view->brands = $this->_brands;

        $usesort = false;
        $usesortData = array();
        $usesortvalues = false;
        $usesortValuesData = array();
        if(in_array('usesort', $this->_options)) {
            $usesort = true;
            if(!empty($widgetSettings['useSortData'])) {
                $usesortData = $widgetSettings['useSortData'];
                unset($widgetSettings['useSortData']);
            }

            if(in_array('usesortvalues', $this->_options)) {
                $usesortvalues = true;
                if(!empty($widgetSettings['usesortValuesData'])) {
                    $usesortValuesData = $widgetSettings['usesortValuesData'];
                    unset($widgetSettings['usesortValuesData']);
                }
            }

        }

        $this->_view->usesort = $usesort;
        $this->_view->usesortData = $usesortData;
        $this->_view->usesortvalues = $usesortvalues;
        $this->_view->usesortValuesData = $usesortValuesData;

        $this->_view->filters = array_map(
            function ($filter) use ($widgetSettings) {
                // opt out if isset in widget settings
                if (array_key_exists($filter['attribute_id'], $widgetSettings)
                    && $widgetSettings[$filter['attribute_id']] === '0') {
                    $filter['show'] = false;
                } else {
                    $filter['show'] = true;
                }

                if (in_array($filter['name'], Filtering_Tools::$_rangeFilters)) {
                    if (isset($filter['values'])) {
                        unset($filter['values']);
                    }
                    return $filter;
                } else {
                    if (!empty($filter['values'])) {
                        if (!empty($widgetSettings[$filter['attribute_id']]) && is_array($widgetSettings[$filter['attribute_id']])) {
                            $filter['show'] = (bool) $widgetSettings[$filter['attribute_id']];
                        }
                    } else {
                        $filter['show'] = false;
                    }
                }



                return $filter;
            },
            $this->_filters
        );

        return $this->_view->render('filter-editor.phtml');
    }

    private function _renderAttribute()
    {
        if (isset($this->_options[0])) {
            $readonly = array_search(self::FILTER_READONLY, $this->_options);
            $eavMapper = Filtering_Mappers_Eav::getInstance();
            $pageId = intval($this->_toasterOptions['id']);
            $product = Models_Mapper_ProductMapper::getInstance()->findByPageId($pageId);
            if (!$product instanceof Models_Model_Product) {
                throw new Exceptions_SeotoasterWidgetException('This is not a product page');
            }
            if ($readonly) {
                $attributeExist = $eavMapper->getByAttrName($this->_options[0], $product->getId());
                if (!empty($attributeExist)) {
                    return $attributeExist['value'];
                }
                return '';
            }

        } elseif (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_PLUGINS)) {
            return $this->_translator->translate('Attribute name is missing');
        }
    }
}
