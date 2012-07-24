<?php
/**
 * ProductWatchdog.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_ProductWatchdog extends Tools_System_GarbageCollector {

	public function __construct($params = array()) {
		parent::__construct($params);
		$this->_cacheHelper     = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
		$this->_websiteConfig   = Zend_Registry::get('website');
	}


	protected function _runOnDefault() {

	}

	protected function _runOnCreate() {
		$pageMapper = Application_Model_Mappers_PageMapper::getInstance();
		$pageHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('page');
		$prodCatPage = $pageMapper->findByUrl(Shopping::PRODUCT_CATEGORY_URL);
		if (!$prodCatPage){
			$prodCatPage = new Application_Model_Models_Page(array(
				'h1'			=> Shopping::PRODUCT_CATEGORY_NAME,
				'headerTitle'	=> Shopping::PRODUCT_CATEGORY_NAME,
				'url'			=> Shopping::PRODUCT_CATEGORY_URL,
				'navName'		=> Shopping::PRODUCT_CATEGORY_NAME,
				'templateId'	=> Application_Model_Models_Template::ID_DEFAULT,
				'parentId'		=> 0,
				'system'		=> 1,
				'is404page'		=> 0,
				'protected'		=> 0,
				'memLanding'	=> 0,
				'showInMenu'	=> 0,
				'targetedKey'	=> Shopping::PRODUCT_CATEGORY_NAME
			));
			$prodCatPage->setId( $pageMapper->save($prodCatPage) );
		}

		$page = new Application_Model_Models_Page();
		$uniqName = array_map(function($str){
            $filter = new Zend_Filter_PregReplace(array(
                   'match'   => '/[^\w]+/u',
                   'replace' => '-'
                ));
            return trim($filter->filter($str), ' -');
            }
            , array( $this->_object->getBrand(), $this->_object->getName(), $this->_object->getSku() ));
		$uniqName = implode('-', $uniqName);
		$page->setTemplateId($this->_object->getPageTemplate() ? $this->_object->getPageTemplate() : Application_Model_Models_Template::ID_DEFAULT);
		$page->setParentId($prodCatPage->getId());
		$page->setNavName($this->_object->getName().' - '.$this->_object->getBrand());
        $page->setMetaDescription(strip_tags($this->_object->getShortDescription()));
		$page->setMetaKeywords('');
		$page->setHeaderTitle($this->_object->getBrand().' '.$this->_object->getName());
		$page->setH1($this->_object->getName());
		//$page->setUrl(strtolower($uniqName).'.html');
        $page->setUrl($pageHelper->filterUrl($uniqName));
		$page->setTeaserText(strip_tags($this->_object->getShortDescription()));
		$page->setLastUpdate(date(DATE_ATOM));
		$page->setIs404page(0);
		$page->setShowInMenu(1);
		$page->setSiloId(0);
		$page->setTargetedKey(Shopping::PRODUCT_CATEGORY_NAME);
		$page->setProtected(0);
		$page->setSystem(0);
		$page->setDraft((bool)$this->_object->getEnabled()?'0':'1');
		$page->setMemLanding(0);
		$page->setNews(0);

		$id = $pageMapper->save($page);

		if($id) {
			$page->setId($id);
			$this->_object->setPage($page);
			Models_Mapper_ProductMapper::getInstance()->updatePageIdForProduct($this->_object);
            //setting product photo as page preview
            if ($this->_object->getPhoto() != null){
                $miscConfig = Zend_Registry::get('misc');
                $savePath = $this->_websiteConfig['path'] . $this->_websiteConfig['preview'];
                $existingFiles = preg_grep('~^'.strtolower($uniqName).'\.(png|jpg|gif)$~i', Tools_Filesystem_Tools::scanDirectory($savePath, false, false));
                if  (!empty($existingFiles)){
                    foreach ($existingFiles as $file) {
                        Tools_Filesystem_Tools::deleteFile($savePath.$file);
                    }
                }
                $productImg = $this->_websiteConfig['path'] . $this->_websiteConfig['media'] . str_replace('/', '/small/' , $this->_object->getPhoto());
                $pagePreviewImg = $savePath.strtolower($uniqName).'.'.pathinfo($productImg, PATHINFO_EXTENSION);
                if (copy($productImg, $pagePreviewImg)) {
                    Tools_Image_Tools::resize($pagePreviewImg, $miscConfig['pageTeaserSize'], true, null, true);
                }
            }
		} else {
			error_log('Can not create page for product #'. $this->_object->getId());
		}
	}

	protected function _runOnUpdate() {
		if (!$this->_object->getPage()){
			$this->_runOnCreate();
		} else {
			$pageMapper = Application_Model_Mappers_PageMapper::getInstance();

			if (is_array($this->_object->getPage())) {
				$page = $this->_object->getPage();
				$pageId = $page['id'];
				unset($page);
			} elseif (is_object($this->_object->getPage())) {
				$pageId = $this->_object->getPage()->getId();
			}

            $page = $pageMapper->find($pageId);
            $isModified = false;

			if (!is_null($this->_object->getPageTemplate()) && $this->_object->getPageTemplate() !== $page->getTemplateId()){
				$page->setTemplateId($this->_object->getPageTemplate());
                $isModified = true;
			}

            if ((bool)$page->getDraft() !== !(bool)$this->_object->getEnabled()){
                $page->setDraft(!(bool)$this->_object->getEnabled());
                $this->_cacheHelper->clean(Helpers_Action_Cache::KEY_DRAFT, Helpers_Action_Cache::PREFIX_DRAFT);
                $isModified = true;
            }

			if ($isModified){
                $pageMapper->save($page);
				$this->_object->setPage(array(
					'id'         => $page->getId(),
					'templateId' => $page->getTemplateId(),
					'url'        => $page->getUrl()
				));
			}

			$this->_cacheHelper->clean('Widgets_Product_Product_byPage_'.$page->getId(), 'store_');
			$this->_cacheHelper->clean(false, false, array('prodid_'.$this->_object->getId(), 'pageid_'.$page->getId()));
		}
	}

	protected function _runOnDelete() {

	}

}
