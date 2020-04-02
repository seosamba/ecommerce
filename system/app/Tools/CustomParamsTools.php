<?php

/**
 * CustomParamsTools.php
 */
class Tools_CustomParamsTools
{


    public static function prepareCustomParamsOptions($customParamsOptions)
    {
        $preparedParams = array();

        foreach ($customParamsOptions as $optionData) {
            if (isset($preparedParams[$optionData['custom_param_id']])) {
                $preparedParams[$optionData['custom_param_id']][] = $optionData;
            } else {
                $preparedParams[$optionData['custom_param_id']][] = $optionData;
            }
        }

        return $preparedParams;
    }
}
