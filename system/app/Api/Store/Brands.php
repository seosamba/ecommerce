<?php
/**
 * Brands.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Brands extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post')
		)
	);

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
	 */
	public function getAction() {
		$brandsList = Models_Mapper_Brand::getInstance()->fetchAll(null, array('name'));
        $pagesUrls = Application_Model_Mappers_PageMapper::getInstance()->fetchAllUrls();
        return array_map(function($brand) use ($pagesUrls) {
	        $item = $brand->toArray();
            if (in_array(strtolower($brand->getName()).'.html', $pagesUrls)){
                $item['url'] = strtolower($brand->getName()).'.html';
            }
            return $item;
        }, $brandsList);
	}

	/**
	 * The post action handles POST requests; it should accept and digest a
	 * POSTed resource representation and persist the resource state.
	 */
	public function postAction() {
		$postData = json_decode($this->_request->getRawBody(), true);
        if (!empty($postData)){
            $brand = Models_Mapper_Brand::getInstance()->save($postData);
            if ($brand instanceof Models_Model_Brand){
                return $brand->toArray();
            }
        } else {
            $this->_error('No data provided');
        }
	}

	/**
	 * The put action handles PUT requests and receives an 'id' parameter; it
	 * should update the server resource state of the resource identified by
	 * the 'id' value.
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

	/**
	 * The delete action handles DELETE requests and receives an 'id'
	 * parameter; it should update the server resource state of the resource
	 * identified by the 'id' value.
	 */
	public function deleteAction() {
		// TODO: Implement deleteAction() method.
	}

}
