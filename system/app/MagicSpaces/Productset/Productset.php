<?php
/**
 *  MAGICSPACE: productset
 * {productset} ... {/productset} - creating set of products
 *
 * Class MagicSpaces_Productset_Productset
 */
class MagicSpaces_Productset_Productset extends Tools_MagicSpaces_Abstract {

    protected $_view   = null;

    /**
     * Shopping config
     *
     * @var null|array
     */
    protected $_config = null;

    protected function _init() {
        $this->_view   = new Zend_View(array('scriptPath' => __DIR__ . '/views'));
        $this->_config = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
    }

    protected function _run() {
        //if nothing inside the magic space, return it back
        if(!$this->_spaceContent) {
            return $this->_spaceContent;
        }

        //Get the product (main set product) for the current product page
        try {
            $product = $this->_invokeProduct();
        } catch (Exception $e) {
            Tools_System_Tools::debugMode() && error_log($e->getMessage());
            return $e->getMessage();
        }

        //get the set settings
        $settingsMapper =  Models_Mapper_ProductSetSettingsMapper::getInstance();
        $setSettings    = $settingsMapper->find($product->getId());
        if($setSettings === null) {
            $autoCalculatePrice = true;
            $settingsMapper->save(array('productId' => $product->getId(), 'autoCalculatePrice' => true));
        } else {
            $autoCalculatePrice = (isset($setSettings['autoCalculatePrice']) && $setSettings['autoCalculatePrice']);
        }


        //Get all parts for this product  from the content ant attach them
        $parts = $this->_getPartsFromContent();

        if($parts) {
            //set parts
            $mapper = Models_Mapper_ProductMapper::getInstance();

            $product->setParts($parts);

            if($autoCalculatePrice) {

                //set the new price for a product
                //$price  = $product->getPrice();
                $price       = 0;
                $taxIncluded = isset($this->_config['showPriceIncTax']) && $this->_config['showPriceIncTax'];
                array_walk($parts, function($partId) use(&$price, $mapper, $taxIncluded) {
                    $part = $mapper->find($partId);
                    if($part instanceof Models_Model_Product) {
                        $price += Tools_ShoppingCart::getInstance()->calculateProductPrice($part, $part->getDefaultOptions());

                        // because calculateProductPrice gives price with tax (if proper settings is on) we will subtract
                        // tax of each part from the final set price
                        if($taxIncluded) {
                            $price -= Tools_Tax_Tax::calculateProductTax($part);
                        }
                    }
                });

                //if price changed during the calculation we're setting the new price
                if($price != $product->getPrice()) {
                    $currency       = Zend_Registry::get('Zend_Currency');
                    $oldPrice       = Tools_ShoppingCart::getInstance()->calculateProductPrice($product, $product->getDefaultOptions());

                    $product->setPrice($price);

                    $this->_content = str_replace($currency->toCurrency($oldPrice), $currency->toCurrency($price + Tools_Tax_Tax::calculateProductTax($product)), $this->_content);

                }
            }
            $product = $mapper->save($product);
            Zend_Controller_Action_HelperBroker::getStaticHelper('cache')->clean(null, null, array('prodid_' . $product->getId()));
        }

        //if current user has no permissions to edit content we return only magic space content
        if(!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT)) {
            return $this->_spaceContent;
        }

        $this->_view->autoCalculatePrice = $autoCalculatePrice;
        $this->_view->currentSetPrice    = $product->getPrice();
        $this->_view->currentSetId       = $product->getId();


        //return magic space content wrapped into a small controll panel
        $this->_view->content = $this->_spaceContent;
        return $this->_view->render('productset.phtml');

    }

    /**
     * Finding the product that corresponds to this product page
     *
     * @return Models_Model_Product
     * @throws Exceptions_SeotoasterException
     */
    private function _invokeProduct() {
        $product = Models_Mapper_ProductMapper::getInstance()->findByPageId($this->_toasterData['id']);
        if(!$product instanceof Models_Model_Product) {
            throw new Exceptions_SeotoasterException('Cannot load product. This magic space (' . $this->_name . ') can be used only on product pages.');
        }
        return $product;
    }

    /**
     * Get parts of the product set based on the product template content
     *
     * @return array
     */
    private function _getPartsFromContent() {
        $foundParts        = array();

        //available templates
        $productIdPatterns = array(
            '<!--pid="([0-9]+)"-->',
            'data-productId="([0-9]+)"',
            'data-pid="([0-9]+)"'
        );

        foreach($productIdPatterns as $pattern) {
            if(preg_match_all('~' . $pattern . '~u', $this->_spaceContent, $foundParts)) {
                break;
            }
        }

        if(!empty($foundParts) && isset($foundParts[1])) {
            return $foundParts[1];
        }
        return $foundParts;
    }
}
