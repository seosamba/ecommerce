<?php

/**
 * Filters.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Filtering_Filters extends Api_Service_Abstract
{

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
        $dbTable = new Filtering_DbTables_Attributes();
        $attributes = $dbTable->fetchAll();
        return $attributes->toArray();
    }

    /**
     * The post action handles POST requests; it should accept and digest a
     * POSTed resource representation and persist the resource state.
     */
    public function postAction()
    {
        $data = json_decode($this->_request->getRawBody(), true);
        if (empty($data['label'])) {
            $this->_error('Required parameter "label" is missing');
        }
        $label = filter_var($data['label'], FILTER_SANITIZE_STRING);
        if (!preg_match('/(?<label>.*)\[(?<name>.*)\]$/', $label, $matches)) {
            $name = strtolower(preg_replace(array('/\s/', '/[^\w\d-_]/'), array('_', ''), $label));
        } else {
            $name = $matches['name'];
            $label = $matches['label'];
        }


        if (!empty($label) && !empty($name)) {
            $dbTable = new Filtering_DbTables_Attributes();

            $filter = array(
                    'label' => strip_tags($label),
                    'name'  => $name
            );
            $row = $dbTable->fetchRow(array('name = ?' => $filter['name']));
            if (!$row) {
                $row = $dbTable->createRow($filter);
                $row->save();
            }

            $filter = $row->toArray();

            return $filter;
        }

        $this->_error();
    }

    /**
     * The put action handles PUT requests and receives an 'id' parameter; it
     * should update the server resource state of the resource identified by
     * the 'id' value.
     */
    public function putAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
        if ($id) {
            $data = json_decode($this->_request->getRawBody(), true);

            $dbTable = new Zend_Db_Table('plugin_filtering_attributes');

            $data = $dbTable->find($id);
            if (!$data->count()) {
                $this->_error(null, self::REST_STATUS_NOT_FOUND);
            }


        }
        $this->_error();
    }

    /**
     * The delete action handles DELETE requests and receives an 'id'
     * parameter; it should update the server resource state of the resource
     * identified by the 'id' value.
     */
    public function deleteAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
        if ($id) {
            $dbTable = new Zend_Db_Table('plugin_filtering_attributes');

            return $dbTable->delete(array('id = ?' => $id));
        }
        $this->_error();

    }

}
