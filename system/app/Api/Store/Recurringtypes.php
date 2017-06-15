<?php

/**
 * Recurringtypes REST API controller
 *
 *
 * @package Store
 * @since   2.4.2
 */
class Api_Store_Recurringtypes extends Api_Service_Abstract
{

    /**
     * secure token
     */
    const RECURRING_TYPES_SECURE_TOKEN = 'RecurringToken';

    /**
     * Recurring payment type day (each day payment period)
     */
    const RECURRING_PAYMENT_TYPE_DAY = 'recurring-payment-day';

    /**
     * Recurring payment type week (each week payment period)
     */
    const RECURRING_PAYMENT_TYPE_WEEK = 'recurring-payment-week';

    /**
     * Recurring payment type month (each month payment period)
     */
    const RECURRING_PAYMENT_TYPE_MONTH = 'recurring-payment-month';

    /**
     * Recurring payment type quarter (each 3 month payment period)
     */
    const RECURRING_PAYMENT_TYPE_QUARTER = 'recurring-payment-quarter';

    /**
     * Recurring payment type semester (each 6 month payment period)
     */
    const RECURRING_PAYMENT_TYPE_SEMESTER = 'recurring-payment-semester';

    /**
     * Recurring payment type year (each year payment period)
     */
    const RECURRING_PAYMENT_TYPE_YEAR = 'recurring-payment-year';

    /**
     * Recurring payment types statuses
     */
    const RECURRING_PAYMENT_TYPE_STATUS_ENABLED = 'enabled';

    const RECURRING_PAYMENT_TYPE_STATUS_DISABLED = 'disabled';


    /**
     * Recurring accepted types
     *
     * @var array
     */
    public static $recurringAcceptType = array(
        'day' => self::RECURRING_PAYMENT_TYPE_DAY,
        'week' => self::RECURRING_PAYMENT_TYPE_WEEK,
        'month' => self::RECURRING_PAYMENT_TYPE_MONTH,
        'quarter' =>  self::RECURRING_PAYMENT_TYPE_QUARTER,
        'semester' => self::RECURRING_PAYMENT_TYPE_SEMESTER,
        'year' => self::RECURRING_PAYMENT_TYPE_YEAR
    );

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
    }

    /**
     * Create or update recurring types
     *
     * Resource:
     * : /api/store/recurringtypes/
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON Add update recurring type model
     */
    public function postAction()
    {
        $data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);

        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::RECURRING_TYPES_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        unset($data[Tools_System_Tools::CSRF_SECURE_TOKEN]);
        $recurringParams = array();
        if (!empty($data['recurringPeriodType']) && !empty($data['recurringTypeStatus']) && in_array($data['recurringPeriodType'],
                self::$recurringAcceptType)
        ) {
            $recurringParams = array($data['recurringPeriodType'] => $data['recurringTypeStatus']);
        }

        if (isset($data['recurringPaymentFreePeriod'])) {
            $recurringPaymentFreePeriod = !empty($data['recurringPaymentFreePeriod']) ? $data['recurringPaymentFreePeriod'] : 0;
            $recurringParams = array('recurringPaymentFreePeriod' => $recurringPaymentFreePeriod);
        }

        if (!empty($recurringParams)) {
            Models_Mapper_ShoppingConfig::getInstance()->save($recurringParams);

            return $recurringParams;
        }

        $this->_error();
    }

    public function putAction()
    {


    }

    public function deleteAction()
    {

    }


}
