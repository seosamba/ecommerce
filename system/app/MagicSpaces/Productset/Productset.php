<?php
/**
 *
 */
class MagicSpaces_Productset_Productset extends Tools_MagicSpaces_Abstract {

    protected function _run() {

        try {
            $product = $this->_invokeProduct();
        } catch(Exceptions_SeotoasterException $se) {
           return $this->_spaceContent = $se->getMessage();
        }
        $parts = $this->_getPartsFromContent();
        if($parts) {
            $product->setParts($parts);
            Models_Mapper_ProductMapper::getInstance()->save($product);
        }
        return $this->_spaceContent;
    }

    private function _invokeProduct() {
        $mapper  = Models_Mapper_ProductMapper::getInstance();
        $product = $mapper->findByPageId($this->_toasterData['id']);
        if(!$product instanceof Models_Model_Product) {
            throw new Exceptions_SeotoasterException('Cannot load product. This magic space (' . $this->_name . ') can be used only on product pages.');
        }
        return $product;
    }

    private function _getPartsFromContent() {
        preg_match_all('~<!--pid="([0-9]+)"-->~u', $this->_spaceContent, $found);
        if(!isset($found[1]) || !is_array($found[1]) || empty($found[1])) {
            preg_match_all('~data-productId="([0-9]+)"~u', $this->_spaceContent, $found);
            if(!isset($found[1]) || !is_array($found[1]) || empty($found[1])) {
                preg_match_all('~data-pid="([0-9]+)"~u', $this->_spaceContent, $found);
                if(!isset($found[1]) || !is_array($found[1]) || empty($found[1])) {
                    return false;
                }
            }
        }
        return $found[1];
    }
}
