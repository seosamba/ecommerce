<?php

/**
 * Filter.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Filter_Filter extends Widgets_Abstract
{
    const CACHE_KEY_SETTINGS = 'settings';

    const OPTION_PRICE_SLIDER = 'price-slider';

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

        if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT) && $request->isPost()) {
            $data = $request->getParam('show', array());

            Filtering_Mappers_Filter::getInstance()->saveSettings($filterId, $data);
        }

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
                if (empty($filter['value'])) {
                    return null;
                }

                // opt out if not exists in widget settings
                if (array_key_exists($filter['attribute_id'], $widgetSettings)
                    && empty($widgetSettings[$filter['attribute_id']])) {
                    return null;
                }

                $values = array_unique($filter['value'], SORT_STRING);
                if (!empty($widgetSettings[$filter['attribute_id']])) {
                    $values = array_intersect($values, $widgetSettings[$filter['attribute_id']]);
                }
                $filter['value'] = $values;

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
        $this->_view->tags = array_filter(array_map(
            function ($tag) use ($appliedFilters, $widgetSettings) {
                $tag = $tag->toArray();
                if (isset($widgetSettings['tags']) && is_array($widgetSettings['tags'])
                    && !in_array($tag['id'], $widgetSettings['tags'])) {
                    return null;
                }
                $tag['checked'] = isset($appliedFilters['category']) && in_array(
                        $tag['name'],
                        $appliedFilters['category']
                    );
                return $tag;
            },
            $this->_tags
        ));

        // apply user values to price range filter
        if (!empty($appliedFilters['price'])) {
            $price = array_pop($appliedFilters['price']);
            list($this->_priceRange['from'], $this->_priceRange['to']) = explode('-', $price, 2);
            unset($appliedFilters['price'], $price);
        }
        if (!isset($widgetSettings['price']) || !empty($widgetSettings['price'])) {
            $this->_view->priceSlider = in_array(self::OPTION_PRICE_SLIDER, $this->_options);
            $this->_view->priceRange = $this->_priceRange;
        }


        // mark selected brands
        $this->_view->brands = array_filter(array_map(
            function ($brand) use ($appliedFilters, $widgetSettings) {
                if (isset($widgetSettings['brands']) && is_array($widgetSettings['brands'])
                    && !in_array($brand['id'], $widgetSettings['brands'])
                ) {
                    return null;
                }

                    $brand['checked'] = isset($appliedFilters['brand']) && in_array(
                            $brand['name'],
                            $appliedFilters['brand']
                        );
                return $brand;
            },
            $this->_brands
        ));

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
            function ($tag) use ($widgetSettings) {
                $tag = $tag->toArray();
                if (!isset($widgetSettings['tags'])) {
                    $tag['checked'] = true;
                } else {
                    $tag['checked'] = (!empty($widgetSettings['tags']) && in_array($tag['id'], $widgetSettings['tags']));
                }
                return $tag;
            },
            $this->_tags
        );

        $this->_view->brands = array_map(
            function ($brand) use ($widgetSettings) {
                if (!isset($widgetSettings['brands'])) {
                    $brand['checked'] = true;
                } else {
                    $brand['checked'] = (!empty($widgetSettings['brands']) && in_array($brand['id'], $widgetSettings['brands']));
                }
                return $brand;
            },
            $this->_brands
        );

        $this->_view->filters = array_map(
            function ($filter) use ($widgetSettings) {
                // opt out if isset in widget settings
                if (array_key_exists($filter['attribute_id'], $widgetSettings)
                    && $widgetSettings[$filter['attribute_id']] === '0') {
                    $filter['show'] = false;
                } else {
                    $filter['show'] = true;
                }
                if (!empty($filter['value'])) {
                    $filter['value'] = array_unique($filter['value'], SORT_STRING);
                    if (!empty($widgetSettings[$filter['attribute_id']]) && is_array($widgetSettings[$filter['attribute_id']])) {
                        $filter['show'] = $widgetSettings[$filter['attribute_id']];
                    }
                } else {
                    $filter['show'] = false;
                }

                return $filter;
            },
            $this->_filters
        );

        return $this->_view->render('filter-product/editor.phtml');
    }
}
