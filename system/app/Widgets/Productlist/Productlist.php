<?php

class Widgets_Productlist_Productlist extends Widgets_Abstract {

	protected $_configHelper  = null;

	protected $_websiteHelper = null;

	protected $_productMapper = null;

	public function _init() {
		parent::_init();
		if (empty($this->_options)){
			throw new Exceptions_SeotoasterWidgetException('No options provided');
		}
		$this->_websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
	    $this->_configHelper  = Zend_Controller_Action_HelperBroker::getExistingHelper('config');

		$this->_view = new Zend_View(array(
			'scriptPath' => dirname(__FILE__) . '/views'
		));
	    $this->_view->websiteUrl = $this->_websiteHelper->getUrl();

		$this->_productMapper = Models_Mapper_ProductMapper::getInstance();
	}

	public function _load() {
		$template = Application_Model_Mappers_TemplateMapper::getInstance()->findByName(array_shift($this->_options));
		if($template !== null) {
			$products = array();
			if(!empty($this->_options)) {
				foreach($this->_options as $option) {
					$explodedOption = explode('-', $option);
					if(isset($explodedOption[0])) {
						switch($explodedOption['0']) {
							case 'categories':
								$products = $this->_productMapper->findByCategories(explode(',', $explodedOption[1]));
							break;
							case 'brands':
								$brandNames = explode(',', $explodedOption[1]);
								if(!empty($products)) {
									foreach($products as $key => $product) {
										if(!in_array($product->getBrand(), $brandNames)) {
											unset($products[$key]);
										}
									}
								}
								else {
									$products = $this->_productMapper->findByBrands($brandNames);
								}
							break;
							case 'order':
								$sortTerms = explode(',', $explodedOption[1]);
								if(is_array($sortTerms) && !empty($products)) {

								}
							break;
						}
					}
				}
			}
			else {
				$products = $this->_productMapper->fetchAll();
			}

			if(!empty($products)) {
				$parsedListing = '';
				foreach($products as $product) {
					$themeConfig = Zend_Registry::get('theme');
		            $parserOptions = array(
		                'websiteUrl'   => $this->_websiteHelper->getUrl(),
		                'websitePath'  => $this->_websiteHelper->getPath(),
		                'currentTheme' => $this->_configHelper->getConfig('currentTheme'),
		                'themePath'    => $themeConfig['path']
		            );
		            unset($themeConfig);

		            $parser = new Tools_Content_Parser($template->getContent(), $product->getPage()->toArray(), $parserOptions);
		            $parsedListing .= $parser->parse();
				}
				return $parsedListing;
			}
		}
		throw new Exceptions_SeotoasterWidgetException('Product template doesn\'t exist');

	}
}
