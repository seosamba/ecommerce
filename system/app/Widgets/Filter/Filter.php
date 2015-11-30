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
        $layout->headLink()->appendStylesheet($this->_websiteUrl . 'system/css/seotoaster-ui.css');

        $mapper = Filtering_Mappers_Eav::getInstance();

        $pageId = intval($this->_toasterOptions['id']);
        $product = Models_Mapper_ProductMapper::getInstance()->findByPageId($pageId);
        if (!$product instanceof Models_Model_Product) {
            throw new Exceptions_SeotoasterWidgetException('This is not a product page');
        }

        $this->_view->productId = $product->getId();
        $this->_view->tags = $product->getTags();
        $this->_view->currentFilters = $mapper->getAttributes($product->getId());


        return $this->_view->render('builder.phtml');
    }

    private function _renderProduct()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('Filter widget: no options provided');
        }

        $options = array();
        $priceTax = '';
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

        // generating filter id
        $filterId = implode('_', array_merge(array($this->_toasterOptions['id']), $options['tagnames']));
        $filterId = substr(md5($filterId), 0, 16);
        $this->_view->filterId = $filterId;

        if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT) && $request->isPost()) {
            $data = $request->getParam('show', array());

            Filtering_Mappers_Filter::getInstance()->saveSettings($filterId, $data);
        }

        $widgetSettings = Filtering_Mappers_Filter::getInstance()->getSettings($filterId);
        $this->_widgetSettings = $widgetSettings;

        $eavMapper = Filtering_Mappers_Eav::getInstance();

        // fetch list filters by given tags
        $listFilters = $eavMapper->findListFiltersByTags($tagIds, $widgetSettings);
        $rangeFilters = $eavMapper->findRangeFiltersByTags($tagIds, $widgetSettings);

        $this->_filters = array_merge($rangeFilters, $listFilters);
        // fetch price range for filters
        $this->_priceRange = $eavMapper->getPriceRange($tagIds);
        if(!isset($priceTax) || empty($priceTax)){
            $this->_priceRange['min'] = floor($this->_priceRange['min']);
            $this->_priceRange['max'] = ceil($this->_priceRange['max']);
        }
        $this->_priceRange['name'] = 'price';
        $this->_priceRange['label'] = 'Price';

        $this->_brands = $eavMapper->getBrands($tagIds);

        // if this user allowed to manage content
        if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT) && !$request->has('filter_preview')) {
            // render editable filter widget
            return $this->_renderWidgetEdit();
        }

        // get applied filters from query
        $appliedFilters = Filtering_Tools::normalizeFilterQuery();
        $this->_view->appliedFilters = $appliedFilters;

        // mark disabled filters
        $this->_view->filters = array_filter(
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
            'nocount' => true
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
            'checked' => !empty($appliedFilters['brand']) ? $appliedFilters['brand'] : array()
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
