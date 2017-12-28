<?php


class Tools_Shipping_Packer

{
    protected static $_errorMessages = array();
    protected static $_cartContent = array();
    protected static $_constraints = array();

    protected static function _setCartContent($cartContent)
    {
        self::$_cartContent = $cartContent;
    }

    public static function completeParcels(array $cartContent, array $constraints)
    {
        self::_setCartContent($cartContent);
        if (true === self::_checkForConstraints($constraints)) {

            $parcels = array();
            $cartContentArray = self::_makeCartContentArray();
            $parcelCounter = 0;
            $isNewParcel = true;

            while (!empty($cartContentArray)) {
                if ($isNewParcel) {
                    $parcels[$parcelCounter] = new Tools_Shipping_Parcel($constraints);
                    $isNewParcel = false;
                }
                $currentItem = array_pop($cartContentArray);
                if ($parcels[$parcelCounter]->addItem($currentItem)) {

                } else {
                    array_unshift($cartContentArray, $currentItem);
                    foreach (array_reverse($cartContentArray, true) as $index=>$item) {
                        if ($parcels[$parcelCounter]->addItem($item)) {
                            unset($cartContentArray[$index]);
                        }
                    }
                    $isNewParcel = true;
                    $parcelCounter++;
                }
            }
            return $parcels;

        } else {

            return self::$_errorMessages;
        }

    }

    protected function _checkForConstraints($constraints)
    {
        $translator = Zend_Registry::get('Zend_Translate');

        $errorMessages = array();
        if (!empty($constraints['maxLength']) && !empty($constraints['maxWidth']) && !empty($constraints['maxDepth']) && !empty($constraints['maxWeight'])) {
            if (!empty(self::$_cartContent)) {
                foreach (self::$_cartContent as $cartItem) {
                    if (empty((float)$cartItem['weight'])) {
                        $errorMessages[$cartItem['id']][] = $translator->translate('Product weight is empty');
                    } elseif ((float)$cartItem['weight'] > $constraints['maxWeight']) {
                        $errorMessages[$cartItem['id']][] = $translator->translate('Wrong weight');
                    };
                    if (empty((float)$cartItem['prodLength'])) {
                        $errorMessages[$cartItem['id']][] = $translator->translate('Product length is empty');
                    } elseif ((float)$cartItem['prodLength'] > $constraints['maxLength']) {
                        $errorMessages[$cartItem['id']][] = $translator->translate('Wrong length');
                    }
                    if (empty((float)$cartItem['prodWidth'])) {
                        $errorMessages[$cartItem['id']][] = $translator->translate('Product width is empty');
                    } elseif ((float)$cartItem['prodWidth'] > $constraints['maxWidth']) {
                        $errorMessages[$cartItem['id']][] = $translator->translate('Wrong width');
                    }
                    if (empty((float)$cartItem['prodDepth'])) {
                        $errorMessages[$cartItem['id']][] = $translator->translate('Product depth is empty');
                    } elseif ((float)$cartItem['prodDepth'] > $constraints['maxDepth']) {
                        $errorMessages[$cartItem['id']][] = $translator->translate('Wrong depth');
                    }
                }
                if (empty($errorMessages)) {
                    return true;
                }
            }
        } else {
            $errorMessages[] = $translator->translate('Required constraints missed');
        }
        self::$_errorMessages = $errorMessages;
    }

    protected static function _makeCartContentArray()
    {
        $result = array();
        foreach (self::$_cartContent as $cartItem) {
            $item = array();
            if ($cartItem['qty'] > 1) {
                for ($i = 1; $i < $cartItem['qty']; $i++) {
                    $item[] = $cartItem['id'];
                    $item[] = (float)$cartItem['weight'];
                    $item[] = (float)$cartItem['prodLength'];
                    $item[] = (float)$cartItem['prodWidth'];
                    $item[] = (float)$cartItem['prodDepth'];
                    $result[] = $item;
                    unset($item);
                }
            }
            $item[] = $cartItem['id'];
            $item[] = (float)$cartItem['weight'];
            $item[] = (float)$cartItem['prodLength'];
            $item[] = (float)$cartItem['prodWidth'];
            $item[] = (float)$cartItem['prodDepth'];
            $result[] = $item;
            unset($item);
        }
        return self::_sortCartItemsByLength($result);
    }

    protected static function _sortCartItemsByLength($itemsArray)
    {
        function lengthCmp($a, $b)
        {
            if ($a[2] == $b[2]) {
                return 0;
            }
            return $a[2] > $b[2] ? 1 : -1;
        }

        usort($itemsArray, 'lengthCmp');
        return $itemsArray;
    }
}