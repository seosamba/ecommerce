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

        $editMode = Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT);
        if ($editMode) {
            $layout = Zend_Layout::getMvcInstance()->getView();
            $layout->headScript()->appendFile(
                $this->_websiteUrl . 'plugins/shopping/web/js/modules/filtering/filtering-product' . (APPLICATION_ENV === 'production' ? '.min' : '') . '.js'
            );
        }

        $tagsNames = explode(',', $this->_options[0]);
        $tags = Models_Mapper_Tag::getInstance()->findByName($tagsNames, false);
        $tagIds = array_map(
            function ($tag) {
                return $tag->getId();
            },
            $tags
        );

        // generating filter id
        $filterId = implode('_', array_merge(array($this->_toasterOptions['id']), $tagsNames));
        $filterId = substr(md5($filterId), 0, 16);
        $this->_view->filterId = $filterId;

        $settings = Filtering_Mappers_Filter::getInstance()->getSettings($filterId);
        $this->_view->settings = $settings;

        $eavMapper = Filtering_Mappers_Eav::getInstance();

        // fetch filters by given tags
        $filters = $eavMapper->findFiltersByTags($tagIds);

        // get applied filters from query
        $appliedFilters = Filtering_Tools::normalizeFilterQuery();

        $filters = array_map(
            function ($filter) use ($appliedFilters, $settings) {
                // opt out if isset in widget settings
                if (array_key_exists($filter['attribute_id'], $settings)
                    && $settings[$filter['attribute_id']] === '1') {
                    return null;
                }
                if (!empty($filter['value'])) {
                    $values = array_unique($filter['value'], SORT_STRING);
                    if (isset($settings[$filter['attribute_id']]) && is_array($settings[$filter['attribute_id']])) {
                        $values = array_diff($values, $settings[$filter['attribute_id']]);
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
            $filters
        );

        $this->_view->filters = array_filter($filters);

        $this->_view->tags = array_map(
            function ($tag) use ($appliedFilters) {
                $tag = $tag->toArray();
                $tag['checked'] = isset($appliedFilters['category']) && in_array(
                        $tag['name'],
                        $appliedFilters['category']
                    );
                return $tag;
            },
            $tags
        );

        // fetch price range
        $priceRange = $eavMapper->getPriceRange($tagIds);
        if (!empty($appliedFilters['price'])) {
            $price = array_pop($appliedFilters['price']);
            list($priceRange['from'], $priceRange['to']) = explode('-', $price, 2);
            unset($appliedFilters['price'], $price);
        }
        $this->_view->priceRange = $priceRange;


        // loading brands for current filter
        $brands = $eavMapper->getBrands($tagIds);
        $this->_view->brands = array_map(function($brand) use ($appliedFilters) {
                $brand['checked'] = isset($appliedFilters['brand']) && in_array($brand['name'], $appliedFilters['brand']);
                return $brand;
            }, $brands);

        if ($editMode && !$request->has('filter_preview')) {
            return $this->_view->render('filter-product/editor.phtml');
        }

//        $this->_view->currentFilters = $appliedFilters;
        return $this->_view->render('filter-product/widget.phtml');
    }
}
