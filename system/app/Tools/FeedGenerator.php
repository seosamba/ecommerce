<?php
/**
 * FeedGenerator
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_FeedGenerator {

	protected static $_instance = null;

	private function __construct() {
        $this->_websiteHelper   = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
        $this->_shoppingConfig  = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
    }

	private function __clone() { }

    private function __wakeup() { }

	public static function getInstance() {
        if (is_null(self::$_instance)){
            self::$_instance = new Tools_FeedGenerator();
        }

        return self::$_instance;
    }

	public function generateProductFeed(){
		$websiteUrl = $this->_websiteHelper->getUrl();

		$indexPage = Application_Model_Mappers_PageMapper::getInstance()->findByUrl('index.html');
		$feedData = array(
			'title' => $indexPage->getHeaderTitle(),
			'link' => $this->_websiteHelper->getUrl().'sitemapproducts.xml',
			'description' => $indexPage->getMetaDescription(),
			'lastBuildDate' => date(DATE_RFC822),
			'generator' => 'SEOTOASTER CMS 2.0 ('.Tools_System_Tools::REMOTE_TOASTER_URL.')'
		);
		unset($indexPage);

		$feed = new DOMDocument('1.0', 'utf-8');
		$feed->formatOutput = true;
		$rss = $feed->createElement('rss');
		$rss->setAttribute('version', '2.0');
		$rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', 'http://base.google.com/ns/1.0');
		$rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:c', 'http://base.google.com/cns/1.0');
		$feed->appendChild($rss);
		$channel = $feed->createElement('channel');
		$rss->appendChild($channel);

		foreach ($feedData as $name => $value){
			$element = $feed->createElement($name, $value);
			$channel->appendChild($element);
		}

		/**
		 * @var $product Models_Model_Product
		 */
		$products = Models_Mapper_ProductMapper::getInstance()->fetchAll("enabled = '1'");

		if (empty($products)){
			return false;
		}
		foreach ($products as $product) {
            $productPage = $product->getPage();
            if(!$productPage instanceof Application_Model_Models_Page) {
                continue;
            }
			$item = $feed->createElement('item');
			$item->appendChild($feed->createElement('title', $product->getName()));
			$item->appendChild($feed->createElement('link', $websiteUrl.$productPage->getUrl()));
			$item->appendChild($feed->createElement('description', $product->getShortDescription()));
			$item->appendChild($feed->createElement('g:id', $product->getId()));
			$item->appendChild($feed->createElement('g:condition', 'new'));
			$item->appendChild($feed->createElement('g:availability', 'in stock'));

            if ($product->getCurrentPrice() !== null && $product->getExtraProperties()) {
                $product->setCurrentPrice(null);
            }
            $item->appendChild(
                $feed->createElement(
                    'g:price',
                    number_format(Tools_ShoppingCart::getInstance()->calculateProductPrice($product, $product->getDefaultOptions()), 2, '.', '')
                    . ' ' . $this->_shoppingConfig['currency']
                )
            );
			$item->appendChild($feed->createElement('g:brand', $product->getBrand()));

			if ($product->getMpn()){
				$item->appendChild($feed->createElement('g:mpn', $product->getMpn()));
			}

			$tags = $product->getTags();
			if (!empty($tags)){
				$tags = array_map(function($tag){ return $tag['name']; }, $tags);
				$item->appendChild($feed->createElement('g:product_type', $tags ? htmlspecialchars(implode(',', $tags)): ''));
			}
			unset($tags);

            $item->appendChild(
                $feed->createElement(
                    'g:image_link',
                    Tools_Misc::prepareProductImage($product->getPhoto())
                )
            );

			if (null !== ($weight = $product->getWeight())){
				$item->appendChild($feed->createElement('g:shipping_weight', $weight.' '.$this->_shoppingConfig['weightUnit'] ));
				unset($weight);
			}

			if (null !== $product->getExtraProperties()){
				$this->_parseExtendedPropeties($feed, $item, $product->getExtraProperties());
			}

			if ($product->getDefaultOptions()){
				foreach ($this->_parseProductOptions($product->getDefaultOptions()) as $name => $value) {
					//Exclude names that starts from number because - not valid xml tag name
					if(is_numeric($name[0])) {
						continue;
					}
					$item->appendChild($feed->createElement('c:'.$name, $value));
					unset($name, $value);
				}

			}

			$channel->appendChild($item);
			unset($item, $product);
		}

		return $feed->saveXML();
	}

	private function _parseExtendedPropeties($feed, $item, $properties){
		foreach ($properties as $name => $value) {
			if (is_array($value) && !empty($value)){
				$this->_parseExtendedPropeties($feed, $item, $value);
			} else {
				$item->appendChild($feed->createElement($name, $value));
			}
		}
	}

	private function _parseProductOptions($options){
		$pairs = array();

		if (!empty($options)){
			foreach ($options as $option) {
				if ($option['type'] === Models_Model_Option::TYPE_DROPDOWN || $option['type'] === Models_Model_Option::TYPE_RADIO){
					$name = strtolower(preg_replace('/[^\w\d_]/u', '_',$option['title']));
					$name =  trim($name, '_');
					$pairs[$name] = urlencode(implode(',', array_map(function($selection){return $selection['title'];}, $option['selection'])));
				}
			}
		}
		return $pairs;
	}
}
