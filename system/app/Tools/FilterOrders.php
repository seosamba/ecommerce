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
        if (!empty($filter['status'])) {
            $statuses = (array)$filter['status'];
            $aliases = array(
                Models_Model_CartSession::CART_STATUS_PENDING => Tools_Misc::CS_ALIAS_PENDING,
                Models_Model_CartSession::CART_STATUS_PROCESSING => Tools_Misc::CS_ALIAS_PENDING,
                Models_Model_CartSession::CART_STATUS_CANCELED => Tools_Misc::CS_ALIAS_LOST_OPPORTUNITY
            );
            foreach ($statuses as $k => $v) {
                if (array_key_exists($v, $aliases)) {
                    $filter['status'][$k] = $aliases[$v];
                    $filter['exclude_gateway'] = self::GATEWAY_QUOTE;
                    $filter['gateway'] = self::GATEWAY_QUOTE;
                }
            }
        }

        $filter['exclude_empty_address'] = '';

        return $filter;
    }
}