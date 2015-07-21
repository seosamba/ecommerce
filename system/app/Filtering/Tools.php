<?php

/**
 * Tools.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Filtering_Tools
{
    const TYPE_LIST = 'list';

    const TYPE_RANGE = 'range';

    public static $_rangeFilters = array(
        'price',
        'width',
        'height',
        'depth',
        'weight'
    );

    /**
     * Parse URL query string to array of filter params
     * @param string $queryString
     * @return array
     */
    public static function normalizeFilterQuery($queryString = null)
    {
        if (is_null($queryString)) {
            $queryString = $_SERVER['QUERY_STRING'];
            $queryString = urldecode($queryString);
        }

        $urlFilters = array();

        if (!empty($queryString)) {
            foreach (explode('&', $queryString) as $queryParam) {
                $queryParam = explode('=', $queryParam);
                $filter = $queryParam[0];
                $value = isset($queryParam[1]) ? urldecode($queryParam[1]) : null;
                if (!isset($urlFilters[$filter])) {
                    $urlFilters[$filter] = array();
                }
                if (!empty($value)) {
                    if (!in_array($filter, self::$_rangeFilters)) {
                        array_push($urlFilters[$filter], $value);
                    } else {
                        list ($urlFilters[$filter]['from'], $urlFilters[$filter]['to']) = explode('-', $value);
                    }
                }

            }
        }
        $urlFilters = array_filter($urlFilters);

        return $urlFilters;
    }
}
