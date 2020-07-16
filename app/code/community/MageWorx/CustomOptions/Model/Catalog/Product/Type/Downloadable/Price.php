<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Model_Catalog_Product_Type_Downloadable_Price extends MageWorx_CustomOptions_Model_Catalog_Product_Type_Downloadable_Price_Abstract {

    /**
     * Apply options price
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     * @param double $finalPrice
     * @return double
     */         
    
    // 100% copy from  ../Price.php
    protected function _applyOptionsPrice($product, $qty, $finalPrice) {

        if ($optionIds = $product->getCustomOption('option_ids')) {
            $helper = Mage::helper('mageworx_customoptions');
            $basePrice = $finalPrice;
            $product->setActualPrice($basePrice);
            $finalPrice = 0;
            
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $product->setCurrentPrice($finalPrice);
                    $option->setProduct($product);

                    if ($option->getGroupByType()==Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT || $option->getGroupByType()==Mage_Catalog_Model_Product_Option::OPTION_GROUP_TEXT) {
                        $quoteItemOption = $product->getCustomOption('option_' . $option->getId());
                        $group = $option->groupFactory($option->getType())->setOption($option)->setQuoteItemOption($quoteItemOption);
                        $finalPrice += $group->getOptionPrice($quoteItemOption->getValue(), $basePrice, $qty);
                    } else {
                        $price = $helper->getOptionPriceByQty($option, $qty);
                        if ($price!=0) $price = $price / $qty;
                        $finalPrice += $price;
                    }
                }
            }
            $product->setBaseCustomoptionsPrice($finalPrice); // for additional info
            if (!$helper->getProductAbsolutePrice($product) || $finalPrice==0) $finalPrice += $basePrice;
        }        
        return $finalPrice;
    }
}