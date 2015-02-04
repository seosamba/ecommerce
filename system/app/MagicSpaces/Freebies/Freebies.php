<?php
/**
 *  MAGICSPACE: freebies
 * {freebies} ... {/freebies} - Freebies magicspace is processing all products inside magic space and makes them as free.
 *
 * Class MagicSpaces_Freebies_Freebies
 */
class MagicSpaces_Freebies_Freebies extends Tools_MagicSpaces_Abstract {

    protected $_view = null;

    protected function _init() {
        $this->_view        = new Zend_View(array('scriptPath' => __DIR__ . '/views'));
    }

    protected function _run() {
		$this->_saveFreebiesProducts();

        //if current user has no permissions to edit content we return only magic space content
        if(!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT)) {
            return $this->_spaceContent;
        }

        //return magic space content wrapped into a small control panel
        $this->_view->content = $this->_spaceContent;
        return $this->_view->render('freebies.phtml');
	}

	private function _saveFreebiesProducts() {
		$productMapper  = Models_Mapper_ProductMapper::getInstance();
        $freebiesSettingsMapper = Models_Mapper_ProductFreebiesSettingsMapper::getInstance();
		$product = $productMapper->findByPageId($this->_toasterData['id']);
		if(!$product instanceof Models_Model_Product) {
			if(Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL)) {
				$this->_spaceContent = '<h3>Cannot load product. This magic space (' . $this->_name . ') can be used only on product pages.</h3>' . $this->_spaceContent;
			} else {
				$this->_spaceContent = '<!-- <h3>Cannot load product. This magic space (' . $this->_name . ') can be used only on product pages.</h3> -->' . $this->_spaceContent;
			}
			return false;
		}

		preg_match_all('~<!--pid="([0-9]+)"-->~u', $this->_spaceContent, $found);
		if(!isset($found[1]) || !is_array($found[1]) || empty($found[1])) {
			preg_match_all('~data-productId="([0-9]+)"~u', $this->_spaceContent, $found);
			if(!isset($found[1]) || !is_array($found[1]) || empty($found[1])) {
				preg_match_all('~data-pid="([0-9]+)"~u', $this->_spaceContent, $found);
				if(!isset($found[1]) || !is_array($found[1]) || empty($found[1])) {
                    if(Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
                        $existFreebies = $freebiesSettingsMapper->getProductHasFreebiesByPageId($product->getId());
                        if(!empty($existFreebies)){
                            $product->setFreebies(array());
                            $productMapper->save($product);
                        }
                    }
					return false;
				}
			}
		}
        $productId = $product->getId();
        $freebiesExist = $freebiesSettingsMapper->find($productId);
        if(!empty($freebiesExist)){
            $this->_view->currentFreebiesPrice      = $freebiesExist['price_value'];
            $this->_view->currentFreebiesQuantity   = $freebiesExist['quantity'];
        }
        $this->_view->currentProductId = $productId;
        if(Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
            if(isset($found[1]) && !empty($found[1])){
                $existFreebies = $freebiesSettingsMapper->getFreebiesIdsByProductId($product->getId());
                $oldFreebiesChanged = array_diff($existFreebies, $found[1]);
                $newFreebiesChanged = array_diff($found[1], $existFreebies);

                if(!empty($oldFreebiesChanged ) || !empty($newFreebiesChanged)){
                    $product->setFreebies($found[1]);
                    $productMapper->save($product);
                }
            }
        }
	}
}
