<?php
/**
 * Store brands REST API controller
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @package Store
 * @since 2.0.0
 */
class Api_Store_Brands extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post')
		),
        Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get', 'post')
		),
        Shopping::ROLE_SALESPERSON => array(
			'allow' => array('get', 'post')
		)
	);

	/**
	 * Returns brands list
	 *
	 * Resourse:
	 * : /api/store/brands/
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * @return json Set of brands
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
	 * Creates new brand
	 *
	 * Resourse:
	 * : /api/store/brands/
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * @return JSON Newly created brand representation
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
	 * Reserved for future usage
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

	/**
	 * Reserved for future usage
	 */
	public function deleteAction() {
		// TODO: Implement deleteAction() method.
	}

}
