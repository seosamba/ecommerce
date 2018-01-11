<?php


class Tools_Shipping_Packer

{
    public static function completeParcels(array $cartContent, array $constraints)
    {
        if (empty($cartContent) || empty($constraints)) {
            return false;
        }
        if (empty($errors = self::_checkForConstraints($cartContent, $constraints)['errors'])) {

            $parcels = array();
            $cartContentArray = self::_makeCartContentArray($cartContent);
            $parcelCounter = 0;
            $isNewParcel = true;

            while (!empty($cartContentArray)) {
                if ($isNewParcel) {
                    $parcels[$parcelCounter] = new Tools_Shipping_Parcel($constraints);
                    $isNewParcel = false;
                }
                $currentItem = array_pop($cartContentArray);
                if (!$parcels[$parcelCounter]->addItem($currentItem)) {
                    array_unshift($cartContentArray, $currentItem);
                    foreach (array_reverse($cartContentArray, true) as $index => $item) {
                        if ($parcels[$parcelCounter]->addItem($item)) {
                            unset($cartContentArray[$index]);
                        }
                    }
                    $isNewParcel = true;
                    $parcelCounter++;
                }
            }
            return array('parcels' => $parcels);

        } else {

            return array('errors' => $errors);
        }

    }

    protected static function _checkForConstraints($cartContent, $constraints)
    {
        $translator = Zend_Registry::get('Zend_Translate');

        $errorMessages = array();
        if (!empty($constraints['maxLength']) && !empty($constraints['maxWidth']) && !empty($constraints['maxDepth']) && !empty($constraints['maxWeight'])) {
            foreach ($cartContent as $cartItem) {
                if (empty((float)$cartItem['weight'])) {
                    $errorMessages[$cartItem['id']][] = $translator->translate('Product weight is empty');
                } elseif ((float)$cartItem['weight'] > $constraints['maxWeight']) {
                    $errorMessages[$cartItem['id']][] = $translator->translate('Wrong product weight');
                };
                if (empty((float)$cartItem['prodLength'])) {
                    $errorMessages[$cartItem['id']][] = $translator->translate('Product length is empty');
                } elseif ((float)$cartItem['prodLength'] > $constraints['maxLength']) {
                    $errorMessages[$cartItem['id']][] = $translator->translate('Wrong product length');
                }
                if (empty((float)$cartItem['prodWidth'])) {
                    $errorMessages[$cartItem['id']][] = $translator->translate('Product width is empty');
                } elseif ((float)$cartItem['prodWidth'] > $constraints['maxWidth']) {
                    $errorMessages[$cartItem['id']][] = $translator->translate('Wrong product width');
                }
                if (empty((float)$cartItem['prodDepth'])) {
                    $errorMessages[$cartItem['id']][] = $translator->translate('Product depth is empty');
                } elseif ((float)$cartItem['prodDepth'] > $constraints['maxDepth']) {
                    $errorMessages[$cartItem['id']][] = $translator->translate('Wrong product depth');
                }
            }
        } else {
            $errorMessages[] = $translator->translate('Required constraints missed');
        }
        return array('errors' => $errorMessages);
    }

    protected static function _makeCartContentArray($cartContent)
    {
        $result = array();
        foreach ($cartContent as $cartItem) {
            if ($cartItem['qty'] > 1) {
                for ($i = 1; $i < $cartItem['qty']; $i++) {
                    $result[] = array(
                        'id' => $cartItem['id'],
                        'weight' => (float)$cartItem['weight'],
                        'length' => (float)$cartItem['prodLength'],
                        'width' => (float)$cartItem['prodWidth'],
                        'depth' => (float)$cartItem['prodDepth']
                    );
                }
            }
            $result[] = array(
                'id' => $cartItem['id'],
                'weight' => (float)$cartItem['weight'],
                'length' => (float)$cartItem['prodLength'],
                'width' => (float)$cartItem['prodWidth'],
                'depth' => (float)$cartItem['prodDepth']
            );
        }
        usort($result, function ($a, $b) {
            return $a['length'] < $b['length'] ? 1 : -1;
        });
        return $result;
    }
}