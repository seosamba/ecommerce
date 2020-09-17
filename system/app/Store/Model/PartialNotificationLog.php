<?php

/**
 * Class Store_Model_PartialNotificationLog
 */
class Store_Model_PartialNotificationLog extends Application_Model_Models_Abstract {

    protected $_cartId = '';

    protected $_notifiedAt = '';

    /**
     * @return string
     */
    public function getCartId()
    {
        return $this->_cartId;
    }

    /**
     * @param string $cartId
     * @return string
     */
    public function setCartId($cartId)
    {
        $this->_cartId = $cartId;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotifiedAt()
    {
        return $this->_notifiedAt;
    }

    /**
     * @param string $notifiedAt
     * @return string
     */
    public function setNotifiedAt($notifiedAt)
    {
        $this->_notifiedAt = $notifiedAt;

        return $this;
    }




}