<?php
/**
 * Class Api_Store_Pickuplocations
 */
class Api_Store_Pickuplocations extends Api_Service_Abstract
{

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

    public function getAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        $categoryId = filter_var($this->_request->getParam('categoryId'), FILTER_SANITIZE_NUMBER_INT);
        $pickupLocationMapper = Store_Mapper_PickupLocationMapper::getInstance();
        $limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
        $offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
        $sortOrder = filter_var($this->_request->getParam('order'), FILTER_SANITIZE_STRING);
        if ($id) {
            $data = $pickupLocationMapper->find($id);
        } elseif ($categoryId) {
            $count = (bool)$this->_request->has('count');
            if ($count) {
                $pickupLocationMapper->lastQueryResultCount($count);
            }
            $data = $pickupLocationMapper->fetchAll($categoryId, $sortOrder, $limit, $offset);
            if (isset($data['data'])) {
                $locationInfo = array_map(
                    function ($pickupLocation) {
                        $pickupLocationData = $pickupLocation;
                        $pickupLocationData['workingHours'] = unserialize($pickupLocationData['working_hours']);
                        return $pickupLocationData;
                    },
                    $data['data']
                );
                $data['data'] = $locationInfo;
                return $data;
            }
        } else {
            $data = $pickupLocationMapper->fetchAll();
        }
        if ($data !== null) {
            return array_map(
                function ($pickupLocation) {
                    $pickupLocationData = $pickupLocation;
                    $pickupLocationData['workingHours'] = unserialize($pickupLocationData['working_hours']);
                    return $pickupLocationData;
                },
                $data
            );

        }
        return array();
    }

    public function postAction()
    {
        $data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);
        $pickupLocationMapper = Store_Mapper_PickupLocationMapper::getInstance();
        $pickupLocationModel = new Store_Model_PickupLocation();
        if (empty($data)) {
            $this->_error();
        }
        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, Api_Store_Pickuplocationcategories::PICKUPLOCATIONS_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        $workingHours = array(
            'sunday' => $data['working-hours-sunday'],
            'monday' => $data['working-hours-monday'],
            'tuesday' => $data['working-hours-tuesday'],
            'wednesday' => $data['working-hours-wednesday'],
            'thursday' => $data['working-hours-thursday'],
            'friday' => $data['working-hours-friday'],
            'saturday' => $data['working-hours-saturday']
        );
        $pickupLocationModel->setAddress1($data['address1']);
        $pickupLocationModel->setAddress2($data['address2']);
        $pickupLocationModel->setCountry($data['country']);
        $pickupLocationModel->setCity($data['city']);
        $pickupLocationModel->setPhone($data['phone']);
        $pickupLocationModel->setWeight($data['weight']);
        $pickupLocationModel->setZip($data['zip']);
        $coordinates = Tools_Geo::getMapCoordinates(
            $data['country'] . ' ' . $data['city'] . ' ' . $data['address1'] . ' ' . $data['zip']
        );
        $pickupLocationModel->setLat($coordinates['lat']);
        $pickupLocationModel->setLng($coordinates['lng']);
        $pickupLocationModel->setWorkingHours(serialize($workingHours));
        $pickupLocationModel->setName($data['location-name']);
        $pickupLocationModel->setLocationCategoryId($data['categoryId']);
        $pickupLocationMapper->save($pickupLocationModel);
    }

    public function putAction()
    {
        $data = array();
        parse_str($this->getRequest()->getRawBody(), $data);
        $id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            $this->_error();
        }
        $workingHours = array(
            'sunday' => $data['working-hours-sunday'],
            'monday' => $data['working-hours-monday'],
            'tuesday' => $data['working-hours-tuesday'],
            'wednesday' => $data['working-hours-wednesday'],
            'thursday' => $data['working-hours-thursday'],
            'friday' => $data['working-hours-friday'],
            'saturday' => $data['working-hours-saturday']
        );
        $pickupLocationMapper = Store_Mapper_PickupLocationMapper::getInstance();
        $pickupLocationModel = $pickupLocationMapper->find($id);
        if ($pickupLocationModel instanceof Store_Model_PickupLocation) {
            $pickupLocationModel->setAddress1($data['address1']);
            $pickupLocationModel->setAddress2($data['address2']);
            $pickupLocationModel->setCountry($data['country']);
            $pickupLocationModel->setCity($data['city']);
            $pickupLocationModel->setPhone($data['phone']);
            $pickupLocationModel->setZip($data['zip']);
            $pickupLocationModel->setWeight($data['weight']);
            $pickupLocationModel->setWorkingHours(serialize($workingHours));
            $pickupLocationModel->setName($data['location-name']);
            $pickupLocationModel->setLocationCategoryId($data['categoryId']);
            $coordinates = Tools_Geo::getMapCoordinates(
                $data['country'] . ' ' . $data['city'] . ' ' . $data['address1'] . ' ' . $data['zip']
            );
            $pickupLocationModel->setLat($coordinates['lat']);
            $pickupLocationModel->setLng($coordinates['lng']);
            $pickupLocationMapper->save($pickupLocationModel);
        } else {
            $this->_error();
        }
    }

    public function deleteAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            $this->_error();
        }

        return Store_Mapper_PickupLocationMapper::getInstance()->delete($id);
    }


}