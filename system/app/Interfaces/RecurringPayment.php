<?php

/**
 * Interface Interfaces_RecurringPayment
 */
interface Interfaces_RecurringPayment {

    /**
     * Change recurring payment frequency
     *
     * @return mixed
     */
	public function updateRecurringPayment();

    /**
     * Suspend recurring payment
     *
     * @return mixed
     */
	public function suspendRecurringPayment();

    /**
     * cancel recurring subscription
     *
     * @return mixed
     */
    public function cancelRecurringPayment();

}
