<?php

/**
 * Filter.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Filter_Filter extends Widgets_Abstract
{
    const CACHE_KEY_SETTINGS = 'settings';

    private $_allowedOptions = array(
        'builder', 'product'
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
        $layout = Zend_Layout::getMvcInstance()->getView();
        $layout->headScript()
            ->appendFile($this->_websiteUrl . 'system/js/external/underscore/underscore.min.js')
            ->appendFile($this->_websiteUrl . 'system/js/external/backbone/backbone.min.js')
            ->appendFile(
                $this->_websiteUrl . 'plugins/shopping/web/js/modules/filtering/filtering-builder' . (APPLICATION_ENV === 'production' ? '.min' : '') . '.js'
            );

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
            throw new Exceptions_SeotoasterWidgetException('Filter widget: at least one tag name should be provided');
        }
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $tagsNames = explode(',', $this->_options[0]);
        $this->_tags = Models_Mapper_Tag::getInstance()->findByName($tagsNames, false);
        $tagIds = array_map(
            function ($tag) {
                return $tag->getId();
            },
            $this->_tags
        );

        // generating filter id
        $filterId = implode('_', array_merge(array($this->_toasterOptions['id']), $tagsNames));
        $filterId = substr(md5($filterId), 0, 16);
        $this->_view->filterId = $filterId;

        $widgetSettings = Filtering_Mappers_Filter::getInstance()->getSettings($filterId);
        $this->_widgetSettings = $widgetSettings;

        $eavMapper = Filtering_Mappers_Eav::getInstance();

        // fetch filters by given tags
        $this->_filters = $eavMapper->findFiltersByTags($tagIds);
        // fetch price range for filters
        $this->_priceRange = $eavMapper->getPriceRange($tagIds);
        // fetch brands
        $this->_brands = $eavMapper->getBrands($tagIds);

        // if this user allowed to manage content
        if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT) && !$request->has('filter_preview')) {
            // render editable filter widget
            return $this->_renderWidgetEdit();
        }

        // get applied filters from query
        $appliedFilters = Filtering_Tools::normalizeFilterQuery();

        // mark disabled filters
        $this->_view->filters = array_filter(array_map(
            function ($filter) use ($appliedFilters, $widgetSettings) {
                // opt out if isset in widget settings
                if (array_key_exists($filter['attribute_id'], $widgetSettings)
                    && $widgetSettings[$filter['attribute_id']] === '1') {
                    return null;
                }
                if (!empty($filter['value'])) {
                    $values = array_unique($filter['value'], SORT_STRING);
                    if (isset($widgetSettings[$filter['attribute_id']]) && is_array($widgetSettings[$filter['attribute_id']])) {
                        $values = array_diff($values, $widgetSettings[$filter['attribute_id']]);
                    }
                    $filter['value'] = $values;
                } else {
                    return null;
                }
                if (isset($appliedFilters[$filter['name']])) {
                    $filter['checked'] = $appliedFilters[$filter['name']];
                } else {
                    $filter['checked'] = array();
                }
                return $filter;
            },
            $this->_filters
        ));

        // assign tags to view with checked attributes
        $this->_view->tags = array_map(
            function ($tag) use ($appliedFilters) {
                $tag = $tag->toArray();
                $tag['checked'] = isset($appliedFilters['category']) && in_array(
                        $tag['name'],
                        $appliedFilters['category']
                    );
                return $tag;
            },
            $this->_tags
        );

        // apply user values to price range filter
        if (!empty($appliedFilters['price'])) {
            $price = array_pop($appliedFilters['price']);
            list($this->_priceRange['from'], $this->_priceRange['to']) = explode('-', $price, 2);
            unset($appliedFilters['price'], $price);
        }
        $this->_view->priceRange = $this->_priceRange;


        // mark selected brands
        $this->_view->brands = array_map(
            function ($brand) use ($appliedFilters) {
                $brand['checked'] = isset($appliedFilters['brand']) && in_array(
                        $brand['name'],
                        $appliedFilters['brand']
                    );
                return $brand;
            },
            $this->_brands
        );

        return $this->_view->render('filter-product/widget.phtml');
    }

    private function _renderWidgetEdit ()
    {
        $layout = Zend_Layout::getMvcInstance()->getView();
        $layout->headScript()->appendFile(
            $this->_websiteUrl . 'plugins/shopping/web/js/modules/filtering/filtering-product' . (APPLICATION_ENV === 'production' ? '.min' : '') . '.js'
        );

        $widgetSettings = $this->_widgetSettings;
        $this->_view->settings = $widgetSettings;

        $this->_view->tags = array_map(
            function ($tag) {
                return $tag->toArray();
            },
            $this->_tags
        );

        $this->_view->brands = $this->_brands;

        $this->_view->filters = array_map(
            function ($filter) use ($widgetSettings) {
                // opt out if isset in widget settings
                if (array_key_exists($filter['attribute_id'], $widgetSettings)
                    && $widgetSettings[$filter['attribute_id']] === '1') {
                    $filter['disabled'] = true;
                }
                if (!empty($filter['value'])) {
                    $filter['value'] = array_unique($filter['value'], SORT_STRING);
                    if (isset($widgetSettings[$filter['attribute_id']]) && is_array($widgetSettings[$filter['attribute_id']])) {
                        $filter['disabled'] = $widgetSettings[$filter['attribute_id']];
                    }
                } else {
                    $filter['disabled'] = true;
                }

                return $filter;
            },
            $this->_filters
        );

        $this->_view->priceRange = $this->_priceRange;

        return $this->_view->render('filter-product/editor.phtml');
    }
}
