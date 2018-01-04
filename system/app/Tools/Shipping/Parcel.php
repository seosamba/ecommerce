<?php


class Tools_Shipping_Parcel
{
    protected $_maxWeight = 22;
    protected $_maxLength = 240;
    protected $_maxWidth = 240;
    protected $_maxDepth = 240;

    protected $_cubicWeight = 0;
    protected $_currentWeight = 0;
    protected $_currentLength = 0;
    protected $_currentWidth = 0;
    protected $_currentDepth = 0;

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
            $this->_currentWeight += $item[1];
            if (!empty($this->_currentWidth)) {
                if ($this->_currentWidth < $item[3]) {
                    $this->_currentWidth = $item[3];
                }
                $this->_currentDepth += $item[4];
            } else {
                $this->_currentLength = $item[2];
                $this->_currentWidth = $item[3];
                $this->_currentDepth = $item[4];
            }
            $this->_update_cubicWeight();
            return true;
        } else {
            return false;
        }
    }

    protected function _isItemCanBeAdded(array $item)
    {
        $itemWeight = $item[1];
        $itemLength = $item[2];
        $itemWidth = $item[3];
        $itemDepth = $item[4];
        $lengthFor_cubicWeight = $this->_currentLength > $itemLength ? $this->_currentLength : $itemLength;
        $widthFor_cubicWeight = $this->_currentWidth > $itemWidth ? $this->_currentWidth : $itemWidth;
        $new_cubicWeight = ($this->_currentDepth + $itemDepth) * $widthFor_cubicWeight * $lengthFor_cubicWeight * 200 / 1000000;
        if (($this->_currentDepth + $itemDepth) > $this->_maxDepth || ($this->_currentWeight + $itemWeight) > $this->_maxWeight || $new_cubicWeight >= $this->_maxWeight) {
            return false;
        }
        return true;
    }

    protected function _update_cubicWeight()
    {
        $this->_cubicWeight = $this->_currentDepth * $this->_currentWidth * $this->_currentLength * 200 / 1000000;
    }

    public function getWeight()
    {
        return $this->_cubicWeight > $this->_currentWeight ? $this->_cubicWeight : $this->_currentWeight;
    }


}