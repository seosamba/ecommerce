<?php

/**
 * Eav.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Filtering_Eav extends Api_Service_Abstract
{

    /**
     * @var Filtering_Mappers_Eav
     */
    private $_eavMapper;

    public function init()
    {
        parent::init();
        $this->_eavMapper = Filtering_Mappers_Eav::getInstance();
    }

    /**
     * @var array Access Control List
     */
    protected $_accessList = array(
        Tools_Security_Acl::ROLE_SUPERADMIN => array(
            'allow' => array('get', 'post', 'put', 'delete')
        ),
        Tools_Security_Acl::ROLE_ADMIN      => array(
            'allow' => array('get', 'post', 'put', 'delete')
        )
    );

    /**
     * The get action handles GET requests and receives an 'id' parameter; it
     * should respond with the server resource state of the resource identified
     * by the 'id' value.
     */
    public function getAction()
    {
        // TODO: Implement getAction() method.
    }

    /**
     * The post action handles POST requests; it should accept and digest a
     * POSTed resource representation and persist the resource state.
     */
    public function postAction()
    {
        return $this->putAction();
    }

    /**
     * The put action handles PUT requests and receives an 'id' parameter; it
     * should update the server resource state of the resource identified by
     * the 'id' value.
     */
    public function putAction()
    {
        $data = json_decode($this->_request->getRawBody(), true);

        if (empty($data['product_id']) || empty($data['attribute_id'])) {
            $this->_error();
        }

        if (!empty($data['tags']) && is_array($data['tags'])) {
            $tags = filter_var_array($data['tags'], FILTER_SANITIZE_NUMBER_INT);
        }

        $container = $this->_eavMapper->saveEavContainer(
            intval($data['product_id']),
            intval($data['attribute_id']),
            htmlentities(strip_tags($data['value']))
        );

        if (!empty($tags)) {
            $this->_assignFilterToTags($container, $tags);
        }

        return $container;
    }

    /**
     * The delete action handles DELETE requests and receives an 'id'
     * parameter; it should update the server resource state of the resource
     * identified by the 'id' value.
     */
    public function deleteAction()
    {
        // TODO: Implement deleteAction() method.
    }

    private function _assignFilterToTags($eavContainer, $tags)
    {
        if (empty($tags)) {
            return false;
        }
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $sql = "INSERT IGNORE INTO shopping_filtering_tags_has_attributes (attribute_id, tag_id) VALUES (:attribute_id, :tag_id)";
        foreach ($tags as $tagId) {
            $dbAdapter->query($sql, array('attribute_id' => $eavContainer['attribute_id'], 'tag_id' => $tagId));
        }
        return true;
    }

}