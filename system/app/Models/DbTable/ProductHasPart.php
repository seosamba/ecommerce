<?php
/**
 *
 */
class Models_DbTable_ProductHasPart extends Zend_Db_Table_Abstract {

    protected $_name = 'shopping_product_has_part';

    protected $_referenceMap = array(
        'Product' => array(
            'columns'		=> 'product_id',
            'refTableClass'	=> 'Models_DbTable_Product',
            'refColumns'	=> 'id'
        ),
        'Part' => array(
            'columns'		=> 'part_id',
            'refTableClass'	=> 'Models_DbTable_Product',
            'refColumns'	=> 'id'
        )
    );

}
