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

    /**
     * Translator
     *
     * @var Zend_Translate
     */
    protected $_translator = null;

    /**
     * Toaster response helper
     *
     * @var Helpers_Action_Response
     */
    protected $_responseHelper = null;

    public function init()
    {
        parent::init();
        $this->_eavMapper = Filtering_Mappers_Eav::getInstance();
        $this->_translator = Zend_Registry::get('Zend_Translate');
        $this->_responseHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('response');
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

        if(isset($data['checked'])){
            if (!empty($data['tagId'])) {
                $tagId = filter_var($data['tagId'], FILTER_SANITIZE_NUMBER_INT);
            }
            $productId =  filter_var($data['product_id'], FILTER_SANITIZE_NUMBER_INT);
            $attributeId = filter_var($data['attribute_id'], FILTER_SANITIZE_NUMBER_INT);
            $attributeName = filter_var($data['attributeVal'], FILTER_SANITIZE_STRING);

            if (!empty($tagId)) {
                $this->_assignUpdateFilterToTags($productId, $tagId, $attributeId, $attributeName, $data['checked']);
            }
            return $this->_responseHelper->success(array('message'=>$this->_translator->translate('Configuration updated')));
        }

        if (!empty($data['tags']) && is_array($data['tags'])) {
            $tags = filter_var_array($data['tags'], FILTER_SANITIZE_NUMBER_INT);
        }

        $container = $this->_eavMapper->saveEavContainer(
            intval($data['product_id']),
            intval($data['attribute_id']),
            strip_tags(htmlspecialchars($data['value']))
        );

        if (!empty($tags)) {
            $this->_assignFilterToTags($container, $tags);
        }

        return $container;
    }

    private function _assignUpdateFilterToTags($productId, $tagId, $attributeId , $attributeName,$checked = true)
    {
        if (empty($tagId)) {
            return false;
        }
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $mapper = Filtering_Mappers_Eav::getInstance();
        $currentData =  $mapper->getAttributesTagsProduct($productId, $tagId, $attributeId);

        if(!empty($currentData) && in_array('0',$currentData)){
            $dbTable = new Zend_Db_Table('shopping_filtering_tags_has_attributes');
            $dbTable->delete(array('attribute_id = ?' => $attributeId, 'tag_id = ?' => $tagId, 'product_id = ?' => '0'));
        }

        $mapper->saveEavContainer($productId, $attributeId, $attributeName);

        if($checked === true) {
            $sql = "INSERT IGNORE INTO shopping_filtering_tags_has_attributes (attribute_id, tag_id, product_id) VALUES (:attribute_id, :tag_id, :product_id)";
            $dbAdapter->query($sql,
                array('attribute_id' => $attributeId, 'tag_id' => $tagId, 'product_id' => $productId));

            return true;
        }
        $dbTable = new Zend_Db_Table('shopping_filtering_tags_has_attributes');
        $dbTable->delete(array('attribute_id = ?' => $attributeId, 'tag_id = ?' => $tagId, 'product_id = ?' => $productId));

        return true;
    }

    /**
     * The delete action handles DELETE requests and receives an 'id'
     * parameter; it should update the server resource state of the resource
     * identified by the 'id' value.
     */
    public function deleteAction()
    {
        $data = json_decode($this->_request->getRawBody(), true);
        if (empty($data['attributeId']) && empty($data['productId'])) {
            $this->_error();
        }
        $dbTable = new Zend_Db_Table('shopping_filtering_values');
        $dbTable->delete(array('attribute_id = ?' => $data['attributeId'], 'product_id = ?' => $data['productId']));

        return $this->_responseHelper->success(array('message'=>$this->_translator->translate('This attribute is deleted')));

    }

    private function _assignFilterToTags($eavContainer, $tags)
    {
        if (empty($tags)) {
            return false;
        }
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();

        $dbTable = new Zend_Db_Table('shopping_filtering_tags_has_attributes');

        $sql = "INSERT IGNORE INTO shopping_filtering_tags_has_attributes (attribute_id, tag_id, product_id) VALUES (:attribute_id, :tag_id, :product_id)";
        foreach ($tags as $tagId) {
            $dbTable->delete(array('attribute_id = ?' => $eavContainer['attribute_id'], 'tag_id = ?' => $tagId, 'product_id = ?' => $eavContainer['product_id']));
            $dbAdapter->query($sql, array('attribute_id' => $eavContainer['attribute_id'], 'tag_id' => $tagId, 'product_id' => $eavContainer['product_id']));
        }
        return true;
    }

}