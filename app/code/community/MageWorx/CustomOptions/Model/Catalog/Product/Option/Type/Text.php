<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_Catalog_Product_Option_Type_Text extends Mage_Catalog_Model_Product_Option_Type_Text
{
    public function getOptionPrice($optionValue, $basePrice, $qty = 1)
    {
        $option = $this->getOption();

        switch ($option->getPriceType()) {
            case 'percent':
            case MageWorx_CustomOptions_Model_Catalog_Product_Option::PRICE_TYPE_OPTIONS_PERCENT:
                $price = ($basePrice * $option->getPrice() / 100);
                if ($option->getCustomoptionsIsOnetime() && $qty) {
                    $price = $price / $qty;
                }
                return $price;
                break;

            case MageWorx_CustomOptions_Model_Catalog_Product_Option::PRICE_TYPE_FIXED_PER_CHARACTER:
                $optionValueWithoutSpaces = str_replace(' ','',$optionValue);
                $optionValueWithoutSpaces = Mage::helper('core/string')->strlen($optionValueWithoutSpaces);
                $price = $option->getPrice() * $optionValueWithoutSpaces;
                if ($option->getCustomoptionsIsOnetime() && $qty) {
                    $price = $price / $qty;
                }
                return $price;
                break;

            case 'fixed':
            default:
                $price = $option->getPrice();
                if ($option->getCustomoptionsIsOnetime() && $qty) {
                    $price = $price / $qty;
                }
                return $price;
                break;
        }
    }
}