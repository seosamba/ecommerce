<?php

/**
 * MAGICSPACE: productoptions
 * {productoptions} ... {/productoptions} - Return wrapper for the single product options on the product page
 *
 * Class MagicSpaces_ProductoptionsProductoptions
 */
class MagicSpaces_Productoptions_Productoptions extends Tools_MagicSpaces_Abstract
{
    /**
     * Product options Magic Space
     * {productoptions}
     *  Return wrapper for the product options on the product page (support price live reload)
     * {/productoptions}
     * @return string
     */
    protected function _run()
    {
        $productMapper = Models_Mapper_ProductMapper::getInstance();
        $product = $productMapper->findByPageId($this->_toasterData['id']);
        if ($product instanceof Models_Model_Product) {
            return '<div class="product-options-listing" data-productId="'.$product->getId().'">'.$this->_spaceContent.'</div>';
        }

        return '';
    }

}
