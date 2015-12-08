<?php
/**
 * Tool FilterOrders.php
 */
class Tools_FilterOrders {

    const GATEWAY_QUOTE = 'Quote';

    public static function filter($filter = array()) {
        if (isset($filter['country'])) {
            if (!preg_match('/[A-Z]{2}/', $filter['country'])) {
                unset($filter['country']);
            }
        }
        if (isset($filter['state']) && $filter['state'] === '0') {
            unset($filter['state']);
        }
        if (isset($filter['date-from']) && !empty($filter['date-from'])) {
            $filter['date-from'] = date(Tools_System_Tools::DATE_MYSQL, strtotime($filter['date-from']));
        }
        if (isset($filter['date-to']) && !empty($filter['date-to'])) {
            $filter['date-to'] = date(Tools_System_Tools::DATE_MYSQL, strtotime($filter['date-to']));
        }

        $filter = array_filter(filter_var_array($filter, FILTER_SANITIZE_STRING));
        if(isset($filter['status']) && ($filter['status'] == Models_Model_CartSession::CART_STATUS_PENDING || $filter['status'] == Models_Model_CartSession::CART_STATUS_PROCESSING || $filter['status'] == Models_Model_CartSession::CART_STATUS_CANCELED)){
            $filter['exclude_gateway'] = self::GATEWAY_QUOTE;
        }
        if (!empty($filter['status'])) {
            $statuses = (array)$filter['status'];
            $aliases = array(
                Tools_Misc::CS_ALIAS_PENDING => Models_Model_CartSession::CART_STATUS_PENDING,
                Tools_Misc::CS_ALIAS_PROCESSING => Models_Model_CartSession::CART_STATUS_PROCESSING,
                Tools_Misc::CS_ALIAS_LOST_OPPORTUNITY => Models_Model_CartSession::CART_STATUS_CANCELED
            );
            foreach ($statuses as $k => $v) {
                if (array_key_exists($v, $aliases)) {
                    $filter['status'][$k] = $aliases[$v];
                    $filter['gateway'] = self::GATEWAY_QUOTE;
                }
            }
        }

        $filter['exclude_empty_address'] = '';

        return $filter;
    }
}