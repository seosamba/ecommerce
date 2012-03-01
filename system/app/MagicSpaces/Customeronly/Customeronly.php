<?php
class MagicSpaces_Customeronly_Customeronly extends Tools_MagicSpaces_Abstract {

	protected function _run() {
		return (Tools_Security_Acl::isAllowed(Shopping::RESOURCE_CART)) ? $this->_spaceContent : '';
	}

}
