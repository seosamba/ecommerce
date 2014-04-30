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
        if ($id) {
            $data = $pickupLocationMapper->find($id);
        } elseif ($categoryId) {
            $data = $pickupLocationMapper->fetchByCategory($categoryId);
        } else {
            $data = $pickupLocationMapper->fetchAll();
        }
        return array_map(
            function ($pickupLocation) {
                $pickupLocationData = $pickupLocation->toArray();
                $pickupLocationData['workingHours'] = unserialize($pickupLocationData['workingHours']);
                return $pickupLocationData;
            },
            $data
        );
    }

    public function postAction()
    {
        $data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);
        $pickupLocationMapper = Store_Mapper_PickupLocationMapper::getInstance();
        $pickupLocationModel = new Store_Model_PickupLocation();
        if (empty($data)) {
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
        $pickupLocationModel->setAddress1($data['address1']);
        $pickupLocationModel->setAddress2($data['address2']);
        $pickupLocationModel->setCountry($data['country']);
        $pickupLocationModel->setCity($data['city']);
        $pickupLocationModel->setPhone($data['phone']);
        $pickupLocationModel->setZip($data['zip']);
        $coordinates = Tools_Geo::getMapCoordinates($data['country'].' '.$data['city'].' '.$data['address'].' '.$data['zip']);
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
            $pickupLocationModel->setWorkingHours(serialize($workingHours));
            $pickupLocationModel->setName($data['location-name']);
            $pickupLocationModel->setLocationCategoryId($data['categoryId']);
            $coordinates = Tools_Geo::getMapCoordinates($data['country'].' '.$data['city'].' '.$data['address'].' '.$data['zip']);
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
