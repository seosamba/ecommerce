<?php
class MagicSpaces_Related_Related extends Tools_MagicSpaces_Abstract {

    protected $_view = null;

    protected function _init() {
        $this->_view        = new Zend_View(array('scriptPath' => __DIR__ . '/views'));
    }

    protected function _run() {
		$this->_saveRelatedProducts();

        //if current user has no permissions to edit content we return only magic space content
        if(!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT)) {
            return $this->_spaceContent;
        }

        //return magic space content wrapped into a small controll panel
        $this->_view->content = $this->_spaceContent;
        return $this->_view->render('related.phtml');
	}

	private function _saveRelatedProducts() {
		$mapper  = Models_Mapper_ProductMapper::getInstance();
		$product = $mapper->findByPageId($this->_toasterData['id']);
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
					return false;
				}
			}
		}
		$product->setRelated($found[1]);
		$mapper->save($product);
	}
}
