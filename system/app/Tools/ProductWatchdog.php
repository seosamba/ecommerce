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
		$productCategoryPage = $pageMapper->findByUrl(Shopping::PRODUCT_CATEGORY_URL);
		if (!$productCategoryPage){
			$productCategoryPage = new Application_Model_Models_Page(array(
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
			$pageMapper->save($productCategoryPage);
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
		$page->setTemplateId($this->_object->getPageTemplate() ? $this->_object->getPageTemplate() : Application_Model_Models_Template::ID_DEFAULT)
			->setParentId($productCategoryPage->getId())
			->setNavName($this->_object->getName().' - '.$this->_object->getBrand())
			->setMetaDescription(strip_tags($this->_object->getShortDescription()))
			->setMetaKeywords('')
			->setHeaderTitle($this->_object->getBrand().' '.$this->_object->getName())
			->setH1($this->_object->getName())
			->setUrl($pageHelper->filterUrl($uniqName))
			->setTeaserText(strip_tags($this->_object->getShortDescription()))
			->setLastUpdate(date(DATE_ATOM))
			->setIs404page(0)
			->setShowInMenu(0)
			->setSiloId(0)
			->setTargetedKeyPhrase(Shopping::PRODUCT_CATEGORY_NAME)
			->setProtected(0)
			->setSystem(0)
			->setDraft((bool)$this->_object->getEnabled()?'0':'1')
			->setMemLanding(0)
			->setNews(0);

		if($pageMapper->save($page)) {
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
                $pathToCropPreview = $this->_websiteConfig['path'] . $this->_websiteConfig['preview'] . 'crop';
                $productImg = $this->_websiteConfig['path'] . $this->_websiteConfig['media'] . str_replace('/', '/small/' , $this->_object->getPhoto());
                $pagePreviewImg = $savePath.strtolower($uniqName).'.'.pathinfo($productImg, PATHINFO_EXTENSION);
                if (is_file($productImg) && copy($productImg, $pagePreviewImg)) {
                    Tools_Image_Tools::resize($pagePreviewImg, $miscConfig['pageTeaserSize'], true, $pathToCropPreview, true);
                }
            }
            $this->_cleanUpCache();
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

			$this->_cleanUpCache();
		}
	}

	protected function _runOnDelete() {
		$this->_cleanUpCache();
	}

	protected function _cleanUpCache(){
		$cacheTags = array(
			'prodid_all',
			'prodbrand_'.$this->_object->getBrand(),
			'prodid_'.$this->_object->getId(),
            'productlist',
			'productListWidget',
            'productindex'
		);
		if (($page = $this->_object->getPage()) instanceof Application_Model_Models_Page){
			$cacheTags[] = 'pageid_'.$page->getId();
			$this->_cacheHelper->clean('Widgets_Product_Product_byPage_'.$page->getId(), 'store_');
		}

		$tags = $this->_object->getTags();
		if (!empty($tags)){
			foreach ($tags as $tag){
				array_push($cacheTags, 'prodtag_'.$tag['id']);
			}
		}

		$this->_cacheHelper->clean(false, false, $cacheTags);
	}

}
