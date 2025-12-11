<?php

/**
 * Products tools
 *
 * Class Tools_ProductsTools
 */
class Tools_ProductsTools
{

    public static function getSearchFilterData($filter = array(), $searchKey = '', $limit = null, $offset = null, $withoutCount = false) {

        $productMapper = Models_Mapper_ProductMapper::getInstance();
        $where = '';

        if((is_array($filter['tags']) && !empty($filter['tags']))) {
            $includesTags = true;
            $where .= '(' . $productMapper->getDbTable()->getAdapter()->quoteInto('pt.tag_id IN (?)', $filter['tags']) . ')';
        }

        if((is_array($filter['brands']) && !empty($filter['brands']))) {
            if (!empty($where)) {
                $where .= ' AND (';
            } else {
                $where .= ' (';
            }

            $where .= $productMapper->getDbTable()->getAdapter()->quoteInto('sb.name in (?)', $filter['brands']) . ')';
        }

        if((is_array($filter['inventory']) && !empty($filter['inventory']))) {
            if (!empty($where)) {
                $where .= ' AND (';
            } else {
                $where .= ' (';
            }

            if(in_array('unlimited', $filter['inventory'])){
                $where .= new Zend_Db_Expr('sp.inventory IS NULL');
                $foundUnlimitedParam = array_search('unlimited', $filter['inventory']);
                unset($filter['inventory'][$foundUnlimitedParam]);
                if (!empty($filter['inventory'])) {
                    $where .= ' OR ' . $productMapper->getDbTable()->getAdapter()->quoteInto('sp.inventory IN (?)', $filter['inventory']) . ')';
                } else {
                    $where .= ')';
                }
            }else{
                $where .= $productMapper->getDbTable()->getAdapter()->quoteInto('sp.inventory IN (?)', $filter['inventory']) . ')';
            }
        }

        if(!empty($searchKey)) {
            $includesTags = true;
            $useSearch = true;
            $search = $searchKey;

            if (!empty($where)) {
                $where .= ' AND ';
            }

            $likeWhere = array(
                'sp.name LIKE ?',
                'sp.sku LIKE ?',
                'sp.mpn LIKE ?',
                'sb.name LIKE ?',
                't.name LIKE ?'
            );

            $attributeValues = explode(' ', $search);
            $whereSplitSearch = ' (';

            foreach ($attributeValues as $key => $attrVal) {
                $whereSplitSearch .= $productMapper->getDbTable()->getAdapter()->quoteInto('sp.name LIKE ?',
                    '%' . $attrVal . '%');

                if (count($attributeValues) > $key + 1) {
                    $whereSplitSearch .= ' AND ';
                }

            }

            $whereSplitSearch .= ') OR ( ';

            foreach ($attributeValues as $key => $attrVal) {
                $whereSplitSearch .= $productMapper->getDbTable()->getAdapter()->quoteInto('sp.sku LIKE ?',
                    '%' . $attrVal . '%');

                if (count($attributeValues) > $key + 1) {
                    $whereSplitSearch .= ' AND ';
                }

            }

            $whereSplitSearch .= ') OR ( ';

            foreach ($attributeValues as $key => $attrVal) {
                $whereSplitSearch .= $productMapper->getDbTable()->getAdapter()->quoteInto('sp.mpn LIKE ?',
                    '%' . $attrVal . '%');

                if (count($attributeValues) > $key + 1) {
                    $whereSplitSearch .= ' AND ';
                }

            }

            $whereSplitSearch .= ') OR ( ';

            foreach ($attributeValues as $key => $attrVal) {
                $whereSplitSearch .= $productMapper->getDbTable()->getAdapter()->quoteInto('sb.name LIKE ?',
                    '%' . $attrVal . '%');

                if (count($attributeValues) > $key + 1) {
                    $whereSplitSearch .= ' AND ';
                }

            }

            $whereSplitSearch .= ') OR ( ';

            foreach ($attributeValues as $key => $attrVal) {
                $whereSplitSearch .= $productMapper->getDbTable()->getAdapter()->quoteInto('t.name LIKE ?',
                    '%' . $attrVal . '%');

                if (count($attributeValues) > $key + 1) {
                    $whereSplitSearch .= ' AND ';
                }

            }

            $whereSplitSearch .= ')';

            $where .= $whereSplitSearch;
        }

        $products = $productMapper->fetchAllData(
            $where,
            null,
            $limit,
            $offset,
            $withoutCount,
            false,
            '',
            array(),
            $includesTags,
            $useSearch
        );

        return $products;
    }

    /**
     * $percent 5 or -5
     *
     * @param $price
     * @param $percent
     * @param $precision
     * @return float
     */
    public static function changePriceByPercent($price, $percent, $precision = 2)
    {
        $price = (float)$price;
        $percent = (float)$percent;
        $newPrice = $price * (1 + $percent / 100);

        return round($newPrice, $precision);
    }

}
