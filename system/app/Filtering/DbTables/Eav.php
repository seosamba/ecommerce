<?php
/**
 * Eav.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */

class Filtering_DbTables_Eav extends Zend_Db_Table_Abstract {

    protected $_name = 'shopping_filtering_values';

    protected $_primary = array(
        'product_id',
        'attribute_id'
    );
}