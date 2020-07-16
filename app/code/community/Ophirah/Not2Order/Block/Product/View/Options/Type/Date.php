<?php

class Ophirah_Not2Order_Block_Product_View_Options_Type_Date
    extends Mage_Catalog_Block_Product_View_Options_Type_Date
{

    public function getFormatedPrice()
    {

        if ($this->getOption() && Mage::helper('not2order')->getShowPrice($this->getProduct())) {
            $option = $this->getOption();

            return $this->_formatPrice(
                array(
                    'is_percent' => ($option->getPriceType() == 'percent'),
                    'pricing_value' => $option->getPrice($option->getPriceType() == 'percent'))
            );
        }
        return '';
    }
}
