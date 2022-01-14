<?php

class Api_Store_Filterpreset extends Api_Service_Abstract
{

    /**
     * System response helper
     *
     * @var null
     */
    protected $_responseHelper = null;

    /**
     * System session helper
     *
     * @var null
     */
    protected $_sessionHelper = null;

    /**
     * Mandatory fields
     *
     * @var array
     */
    protected $_mandatoryParams = array('filter_preset_name');

    /**
     * @var array Access Control List
     */
    protected $_accessList = array(
        Tools_Security_Acl::ROLE_SUPERADMIN => array(
            'allow' => array('get', 'post', 'put', 'delete')
        ),
        Tools_Security_Acl::ROLE_ADMIN => array(
            'allow' => array('get', 'post', 'put', 'delete')
        ),
        Shopping::ROLE_SALESPERSON => array(
            'allow' => array('get', 'post', 'put', 'delete')
        )
    );

    public function init()
    {
        parent::init();
        $this->_responseHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('response');
        $this->_sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
    }


    /**
     * Get filter preset by id
     *
     * Resource:
     * : /api/store/filterpreset/
     *
     * HttpMethod:
     * : GET
     *
     * @return JSON
     */
    public function getAction()
    {
        $limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
        $offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
        $sortOrder = filter_var($this->_request->getParam('order'), FILTER_SANITIZE_STRING);
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);


        $filterPresetMapper = Models_Mapper_FilterPresetMapper::getInstance();
        $currentUser = $this->_sessionHelper->getCurrentUser();
        $userId = 0;
        if ($id) {
            $data = $filterPresetMapper->findByIdRole($id, $userId);
        } else {
            $data = $filterPresetMapper->fetchAll(null, $sortOrder, $limit, $offset);
        }

        return $data;
    }

    /**
     * Create new filter preset
     *
     * Resource:
     * : /api/store/filterpreset/
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON
     */
    public function postAction()
    {
        $data = filter_var_array($this->_request->getParams(), FILTER_SANITIZE_STRING);
        $translator = Zend_Registry::get('Zend_Translate');

        $fieldDataMissing = array_filter($this->_mandatoryParams, function ($param) use ($data) {
            if (!array_key_exists($param, $data) || empty($data[$param])) {
                return $param;
            }
        });

        if (!empty($fieldDataMissing)) {
            $this->_error($translator->translate('Missing mandatory params'));
        }

        $secureToken = $this->getRequest()->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $tokenValid = Tools_System_Tools::validateToken($secureToken, Shopping::SHOPPING_SECURE_TOKEN);
        if (!$tokenValid) {
            $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
            $websiteUrl = $websiteHelper->getUrl();
            $this->_error($translator->translate('Your session has timed-out. Please Log back in '.'<a href="'.$websiteUrl.'go">here</a>'));
        }

        $filterPresetMapper = Models_Mapper_FilterPresetMapper::getInstance();

        $filterPresetModel = new Models_Model_FilterPresetModel();
        $data['filter_preset_name'] = preg_replace('~[^\w\s\_]~ui', '', $this->_request->getParam('filter_preset_name'));
        if (!empty($data['filter_preset_data'])) {
            $data['filter_preset_data'] = json_encode($data['filter_preset_data']);
        }
        $filterPresetModel->setOptions($data);

        $presetExistingModel = $filterPresetMapper->getByName($data['filter_preset_name']);
        if (!empty($presetExistingModel)) {
            $this->_error($translator->translate('Filter preset with such name already exists'));
        }

        $userId = $this->_sessionHelper->getCurrentUser()->getId();
        if (!empty($data['is_default'])) {
            $filterPresetMapper->resetDefaultByCreatorId($userId);
        }

        $filterPresetModel->setCreatorId($userId);
        $filterPresetModel = $filterPresetMapper->save($filterPresetModel);

        $this->_responseHelper->success(array(
            'message' => $translator->translate('Filter has been created'),
            'id' => $filterPresetModel->getId()
        ));
    }

    /**
     * Update preset data
     *
     * Resource:
     * : /api/store/filterpreset/
     *
     * HttpMethod:
     * : PUT
     *
     * ## Parameters:
     * id (type integer)
     * : filter preset id to update
     *
     * @return JSON
     */
    public function putAction()
    {
        $data = json_decode($this->_request->getRawBody(), true);
        if (!empty($data['id']) && !empty($data[Tools_System_Tools::CSRF_SECURE_TOKEN])) {
            $translator = Zend_Registry::get('Zend_Translate');
            $secureToken = $data[Tools_System_Tools::CSRF_SECURE_TOKEN];
            $tokenValid = Tools_System_Tools::validateToken($secureToken, Shopping::SHOPPING_SECURE_TOKEN);
            if (!$tokenValid) {
                $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
                $websiteUrl = $websiteHelper->getUrl();
                $this->_error($translator->translate('Your session has timed-out. Please Log back in '.'<a href="'.$websiteUrl.'go">here</a>'));
            }
            $presetId = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
            $filterPresetMapper = Models_Mapper_FilterPresetMapper::getInstance();
            $filterPresetModel = $filterPresetMapper->find($presetId);
            if (!$filterPresetModel instanceof Models_Model_FilterPresetModel) {
                $this->_responseHelper->fail($translator->translate('Config doesn\'t exists'));
            }

            $currentUser = $this->_sessionHelper->getCurrentUser();
            $userId = $currentUser->getId();
            $userRole = $currentUser->getRoleId();
            if ($userRole !== Tools_Security_Acl::ROLE_SUPERADMIN && $userRole !== Tools_Security_Acl::ROLE_ADMIN) {
                $creatorId = $filterPresetModel->getCreatorId();
                if ($creatorId != $userId) {
                    $this->_error('Editing not allowed');
                }
            }

            $oldFilterPresetName = $filterPresetModel->getFilterPresetName();
            $data['filter_preset_name'] = preg_replace('~[^\w\s\_]~ui', '', $data['filter_preset_name']);
            $newFilterPresetName = $data['filter_preset_name'];
            if ($oldFilterPresetName !== $newFilterPresetName) {
                $presetExistingModel = $filterPresetMapper->getByName($data['filter_preset_name']);
                if (!empty($presetExistingModel)) {
                    $this->_error($translator->translate('Filter preset with such name already exists'));
                }
            }

            //$userId = $this->_sessionHelper->getCurrentUser()->getId();
            if (!empty($data['is_default'])) {
                $creatorId = $filterPresetModel->getCreatorId();

                $filterPresetMapper->resetDefaultByCreatorId($creatorId);
            }

            if (!empty($data['filter_preset_data'])) {
                $data['filter_preset_data'] = json_encode($data['filter_preset_data']);
            }

            $filterPresetModel->setOptions($data);
            $filterPresetMapper->save($filterPresetModel);
            $this->_responseHelper->success(array(
                'message' => $translator->translate('Filter updated'),
                'id' => $filterPresetModel->getId()
            ));
        }

    }

    /**
     * Delete filter preset
     *
     * Resource:
     * : /api/store/filterpreset/
     *
     * HttpMethod:
     * : DELETE
     *
     * ## Parameters:
     * id (type integer)
     * : filter preset id to delete
     *
     * @return JSON
     */
    public function deleteAction()
    {
        $currentUser = $this->_sessionHelper->getCurrentUser();
        $userId = $currentUser->getId();
        $userRole = $currentUser->getRoleId();

        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            $this->_responseHelper->fail('');
        }
        $translator = Zend_Registry::get('Zend_Translate');
        $filterPresetMapper = Models_Mapper_FilterPresetMapper::getInstance();
        $filterPresetModel = $filterPresetMapper->find($id);
        if ($filterPresetModel instanceof Models_Model_FilterPresetModel) {
            if ($userRole !== Tools_Security_Acl::ROLE_SUPERADMIN && $userRole !== Tools_Security_Acl::ROLE_ADMIN) {
                $creatorId = $filterPresetModel->getCreatorId();
                if ($creatorId != $userId) {
                    $this->_responseHelper->fail('Editing not allowed');
                }
            }

            $filterPresetMapper->delete($id);

            $this->_responseHelper->success($translator->translate('Filter has been deleted'));
        } else {
            $this->_responseHelper->fail('');
        }

    }

}
