<?php
class Magecomp_AbsolutePricing_Model_Product_Option_Type_Default extends Mage_Catalog_Model_Product_Option_Type_Default
{
    public function getOptionPrice($optionValue, $basePrice)
    {
        $option = $this->getOption();

        return $this->_getChargableOptionPriceX(
            $option->getPrice(),
            $option->getPriceType(),
            $basePrice
        );
    }
    protected function _getChargableOptionPriceX($price, $priceType, $basePrice)
    {
		if ( $priceType == 'percent' ) {
            return ($basePrice * $price / 100);
        } elseif ( $priceType == 'fixed' ) {
            return $price;
        } elseif ( $priceType == 'absolute' || $priceType == 'absoluteonce') {
            return ($price - $basePrice);
        }
    }
}
