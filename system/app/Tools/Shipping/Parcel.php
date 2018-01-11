<?php


class Tools_Shipping_Parcel
{
    protected $_cubicWeight = 0;
    protected $_currentWeight = 0;
    protected $_currentLength = 0;
    protected $_currentWidth = 0;
    protected $_currentDepth = 0;

    protected $_items = array();

    public function __construct(array $constraints)
    {
            $this->_maxWeight = (float)$constraints['maxWeight'];
            $this->_maxLength = (float)$constraints['maxLength'];
            $this->_maxWidth = (float)$constraints['maxWidth'];
            $this->_maxDepth = (float)$constraints['maxDepth'];
    }

    public function addItem(array $item)
    {
        if ($this->_isItemCanBeAdded($item)) {
            $this->_items[] = $item;
            $this->_currentWeight += $item['weight'];
            if (!empty($this->_currentWidth)) {
                if ($this->_currentWidth < $item['width']) {
                    $this->_currentWidth = $item['width'];
                }
                $this->_currentDepth += $item['depth'];
            } else {
                $this->_currentLength = $item['length'];
                $this->_currentWidth = $item['width'];
                $this->_currentDepth = $item['depth'];
            }
            $this->_updateCubicWeight();
            return true;
        } else {
            return false;
        }
    }

    protected function _isItemCanBeAdded(array $item)
    {
        $itemWeight = $item['weight'];
        $itemLength = $item['length'];
        $itemWidth = $item['width'];
        $itemDepth = $item['depth'];
        $lengthFor_cubicWeight = $this->_currentLength > $itemLength ? $this->_currentLength : $itemLength;
        $widthFor_cubicWeight = $this->_currentWidth > $itemWidth ? $this->_currentWidth : $itemWidth;
        $new_cubicWeight = ($this->_currentDepth + $itemDepth) * $widthFor_cubicWeight * $lengthFor_cubicWeight * 200 / 1000000;
        if (($this->_currentDepth + $itemDepth) > $this->_maxDepth || ($this->_currentWeight + $itemWeight) > $this->_maxWeight || $new_cubicWeight >= $this->_maxWeight) {
            return false;
        }
        return true;
    }

    protected function _updateCubicWeight()
    {
        $this->_cubicWeight = $this->_currentDepth * $this->_currentWidth * $this->_currentLength * 200 / 1000000;
    }

    public function getWeight()
    {
        return $this->_cubicWeight > $this->_currentWeight ? $this->_cubicWeight : $this->_currentWeight;
    }
    public function getLength()
    {
        return $this->_currentLength;
    }
    public function getWidth()
    {
        return $this->_currentWidth;
    }
    public function getDepth()
    {
        return $this->_currentDepth;
    }


}