<?php


class Tools_Shipping_Parcel
{
    protected $_maxWeight = 22;
    protected $_maxLength = 240;
    protected $_maxWidth = 240;
    protected $_maxDepth = 240;

    public $currentWeight = 0;
    public $currentLength = 0;
    public $currentWidth = 0;
    public $currentDepth = 0;

    protected $_items = array();

    public function __construct(array $constraints)
    {
        if (!empty($constraints['maxWeight']) && !empty($constraints['maxLength']) && !empty($constraints['maxWidth']) && !empty($constraints['maxDepth'])) {
            $this->_maxWeight = (float)$constraints['maxWeight'];
            $this->_maxLength = (float)$constraints['maxLength'];
            $this->_maxWidth = (float)$constraints['maxWidth'];
            $this->_maxDepth = (float)$constraints['maxDepth'];
        }

    }

    public function addItem(array $item)
    {
        if ($this->_isItemCanBeAdded($item)) {
            $this->_items[] = $item;
            $this->currentWeight += $item[1];
            if (!empty($this->currentWidth)) {
                if ($this->currentWidth < $item[3]) {
                    $this->currentWidth = $item[3];
                }
                $this->currentDepth += $item[4];
            } else {
                $this->currentLength = $item[2];
                $this->currentWidth = $item[3];
                $this->currentDepth = $item[4];
            }

            return true;
        } else {
            return false;
        }
    }

    protected function _isItemCanBeAdded(array $item)
    {
        $itemWeight = $item[1];
        $itemDepth = $item[4];
        if (($this->currentDepth + $itemDepth) > $this->_maxDepth || ($this->currentWeight + $itemWeight) > $this->_maxWeight) {
            return false;
        }
        return true;
    }


}