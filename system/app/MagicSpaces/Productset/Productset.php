<?php
/**
 *
 */
class MagicSpaces_Productset_Productset extends Tools_MagicSpaces_Abstract {

    protected $_view = null;

    protected function _init() {
        $this->_view        = new Zend_View(array('scriptPath' => __DIR__ . '/views'));
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
            if(Tools_System_Tools::debugMode()) {
                error_log($e->getMessage());
            }
            return $e->getMessage();
        }

        //get the set settings
        $setSettings        = Models_Mapper_ProductSetSettingsMapper::getInstance()->find($product->getId());
        $autoCalculatePrice = (isset($setSettings['autoCalculatePrice']) && $setSettings['autoCalculatePrice']);

        //Get all parts for this product  from the content ant attach them
        $parts = $this->_getPartsFromContent();

        if($parts) {
            //set parts
            $product->setParts($parts);

            if($autoCalculatePrice) {

                //set the new price for a product
                $price  = $product->getPrice();
                $mapper = Models_Mapper_ProductMapper::getInstance();

                array_walk($parts, function($partId) use($price, $mapper) {
                    $part = $mapper->find($partId);
                    if($part instanceof Models_Model_Product) {
                        $price += Tools_ShoppingCart::getInstance()->calculateProductPrice($part, $part->getDefaultOptions());
                    }
                });

                //if price changed during the calculation we're setting the new price
                if($price != $product->getPrice()) {
                    $product->setPrice($price);
                }
                $product = $mapper->save($product);
            }
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
