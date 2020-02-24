<?php
class Tools_AllowanceObserver implements Interfaces_Observer
{

    /**
     * Remove allowance row and disable the product
     * @param $object
     */
    public function notify($object)
    {
        $allowanceProductsMapper = Store_Mapper_AllowanceProductsMapper::getInstance();

        $prodId = $object->getId();
        if(!empty($prodId)) {
            $timeToDisableProducts = $allowanceProductsMapper->fetchAll("product_id = ". $prodId ." AND allowance_due < '" . date('Y-m-d', time()) ."'");
            if(!empty($timeToDisableProducts)) {
                $productMapper = Models_Mapper_ProductMapper::getInstance();
                $currentProduct = $productMapper->findByProductId($prodId);
                if(!empty($currentProduct) && !empty($currentProduct['enabled'])) {
                    $productDbTable = new Models_DbTable_Product();
                    $currentProduct['enabled'] = 0;
                    $currentProduct['updated_at'] = date(Tools_System_Tools::DATE_MYSQL);

                    $where = $productDbTable->getAdapter()->quoteInto('id = ?', $prodId);
                    $productDbTable->update($currentProduct, $where);
                }

                $allowanceProductsMapper->deleteByProductId($prodId);
            }
        }
    }

}
