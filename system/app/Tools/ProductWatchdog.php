<?php

/**
 * ProductWatchdog.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_ProductWatchdog extends Tools_System_GarbageCollector
{

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_cacheHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
        $this->_websiteConfig = Zend_Registry::get('website');
    }


    protected function _runOnDefault()
    {

    }

    protected function _runOnCreate()
    {
        $pageMapper = Application_Model_Mappers_PageMapper::getInstance();
        $pageHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('page');
        $productCategoryPage = $pageMapper->findByUrl(Shopping::PRODUCT_CATEGORY_URL);
        if (!$productCategoryPage) {
            $productCategoryPage = new Application_Model_Models_Page(array(
                'h1'          => Shopping::PRODUCT_CATEGORY_NAME,
                'headerTitle' => Shopping::PRODUCT_CATEGORY_NAME,
                'url'         => Shopping::PRODUCT_CATEGORY_URL,
                'navName'     => Shopping::PRODUCT_CATEGORY_NAME,
                'templateId'  => Application_Model_Models_Template::ID_DEFAULT,
                'parentId'    => 0,
                'system'      => 0,
                'is404page'   => 0,
                'protected'   => 0,
                'memLanding'  => 0,
                'showInMenu'  => 0,
                'draft'       => 0,
                'targetedKey' => Shopping::PRODUCT_CATEGORY_NAME
            ));
            $pageMapper->save($productCategoryPage);
        }

        $page = new Application_Model_Models_Page();

        $uniqName = array_map(
            function ($str) {
                $filter = new Zend_Filter_PregReplace(array(
                    'match'   => '/[^\w]+/u',
                    'replace' => '-'
                ));
                return trim($filter->filter($str), ' -');
            }
            ,
            array($this->_object->getBrand(), $this->_object->getName(), $this->_object->getSku())
        );
        $uniqName = implode('-', $uniqName);
        $page->setTemplateId(
            $this->_object->getPageTemplate() ? $this->_object->getPageTemplate(
            ) : Application_Model_Models_Template::ID_DEFAULT
        )
            ->setParentId($productCategoryPage->getId())
            ->setNavName($this->_object->getName() . ' - ' . $this->_object->getBrand())
            ->setMetaDescription(strip_tags($this->_object->getShortDescription()))
            ->setMetaKeywords('')
            ->setHeaderTitle($this->_object->getBrand() . ' ' . $this->_object->getName())
            ->setH1($this->_object->getName())
            ->setUrl($pageHelper->filterUrl($uniqName))
            ->setTeaserText(strip_tags($this->_object->getShortDescription()))
            ->setLastUpdate(date(DATE_ATOM))
            ->setIs404page(0)
            ->setShowInMenu(1)
            ->setSiloId(0)
            ->setTargetedKeyPhrase($this->_object->getName())
            ->setProtected(0)
            ->setSystem(0)
            ->setDraft((bool)$this->_object->getEnabled() ? '0' : '1')
            ->setMemLanding(0)
            ->setNews(0);

        if ($pageMapper->save($page)) {
            $this->_object->setPage($page);
            Models_Mapper_ProductMapper::getInstance()->updatePageIdForProduct($this->_object);
            //setting product photo as page preview
            if ($this->_object->getPhoto() != null) {
                $miscConfig = Zend_Registry::get('misc');
                $savePath = $this->_websiteConfig['path'] . $this->_websiteConfig['preview'];
                $existingFiles = preg_grep(
                    '~^' . strtolower($uniqName) . '\.(png|jpg|gif)$~i',
                    Tools_Filesystem_Tools::scanDirectory($savePath, false, false)
                );
                if (!empty($existingFiles)) {
                    foreach ($existingFiles as $file) {
                        Tools_Filesystem_Tools::deleteFile($savePath . $file);
                    }
                }
                $pathToCropPreview = $this->_websiteConfig['path'] . $this->_websiteConfig['preview'] . 'crop';
                list($folder, $imgName) = explode('/', $this->_object->getPhoto());
                $productImg = $this->_websiteConfig['path'] . $this->_websiteConfig['media'] . $folder . DIRECTORY_SEPARATOR . 'small' . DIRECTORY_SEPARATOR . $imgName;
                $pagePreviewImg = $savePath . strtolower($uniqName) . '.' . pathinfo($productImg, PATHINFO_EXTENSION);
                if (is_file($productImg) && copy($productImg, $pagePreviewImg)) {
                    if (Tools_Image_Tools::resize(
                        $pagePreviewImg,
                        $miscConfig['pageTeaserSize'],
                        true,
                        $pathToCropPreview,
                        true
                    )
                    ) {
                        $pageMapper->save($page->setPreviewImage(Tools_Filesystem_Tools::basename($pagePreviewImg)));
                    }
                }
            }
            $page->notifyObservers();
            $this->_cleanUpCache();
        } else {
            error_log('Can not create page for product #' . $this->_object->getId());
        }
    }

    protected function _runOnUpdate()
    {
        if (!$this->_object->getPage()) {
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

            if (!is_null($this->_object->getPageTemplate()) && $this->_object->getPageTemplate(
                ) !== $page->getTemplateId()
            ) {
                $page->setTemplateId($this->_object->getPageTemplate());
                $isModified = true;
            }

            if ((bool)$page->getDraft() !== !(bool)$this->_object->getEnabled()) {
                $page->setDraft((bool)$this->_object->getEnabled() ? 0 : 1);
                $this->_cacheHelper->clean(Helpers_Action_Cache::KEY_DRAFT, Helpers_Action_Cache::PREFIX_DRAFT);
                $isModified = true;
            }

            if ($isModified) {
                $page->registerObserver(
                    new Tools_Page_GarbageCollector(array(
                        'action' => Tools_System_GarbageCollector::CLEAN_ONUPDATE
                    ))
                );
                $pageMapper->save($page);
                $page->notifyObservers();
                $this->_object->setPage(
                    array(
                        'id'         => $page->getId(),
                        'templateId' => $page->getTemplateId(),
                        'url'        => $page->getUrl()
                    )
                );
            }

            $this->_cleanUpCache();
        }
        $this->_updateSearchIndex();
    }

    protected function _runOnDelete()
    {
        $this->_cleanUpCache();
    }

    protected function _cleanUpCache()
    {
        $cacheTags = array(
            'prodid_all',
            'prodbrand_' . $this->_object->getBrand(),
            'prodid_' . $this->_object->getId(),
            'productlist',
            'productListWidget',
            'productindex'
        );
        if (($page = $this->_object->getPage()) instanceof Application_Model_Models_Page) {
            $cacheTags[] = 'pageid_' . $page->getId();
        }

        $tags = $this->_object->getTags();
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                array_push($cacheTags, 'prodtag_' . $tag['id']);
            }
        }

        $this->_cacheHelper->clean(false, false, $cacheTags);
        $this->_cacheHelper->clean('products', Helpers_Action_Cache::PREFIX_SITEMAPS);
    }

    private function _updateSearchIndex()
    {
        $page = $this->_object->getPage();

        if (!empty($page) && !$page instanceof Application_Model_Models_Page) {
            $page = Application_Model_Mappers_PageMapper::getInstance()->find($page['id']);
        }

        $searchIndex = Tools_Search_Tools::initIndex();

        Tools_Search_Tools::removeFromIndex($page->getId());
        $page->setH1(implode(', ', array($this->_object->getName(), $this->_object->getSku(), $this->_object->getMpn(), $page->getH1())));
        $page->setTeaserText(
            implode(
                PHP_EOL,
                array(
                    $this->_object->getShortDescription(),
                    $this->_object->getFullDescription(),
                    $page->getTeaserText(),
                    implode(
                        ', ',
                        array_map(
                            function ($t) {
                                return $t['name'];
                            },
                            $this->_object->tags
                        )
                    )
                )
            )
        );

        Tools_Search_Tools::addPageToIndex($page);
    }

}
