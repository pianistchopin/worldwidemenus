<?php
class Magecomp_AbsolutePricing_Model_Product_Option_Type_Select extends Mage_Catalog_Model_Product_Option_Type_Select
{
    public function getOptionPrice($optionValue, $basePrice)
    {
        $option = $this->getOption();
        $result = 0;
        if (!$this->_isSingleSelection()) {
            foreach(explode(',', $optionValue) as $value) {
                if ($_result = $option->getValueById($value)) {
					$result += $this->_getChargableOptionPriceXX(
                        $_result->getPrice(),
                        $_result->getPriceType(),
                        $basePrice
                    );
                } else {
                    if ($this->getListener()) {
                        $this->getListener()
                                ->setHasError(true)
                                ->setMessage(
                                    Mage::helper('catalog')->__('Some of the products below do not have all the required options. Please remove them and add again with all the required options.')
                                );
                        break;
                    }
                }
            }
        } elseif ($this->_isSingleSelection()) {
            if ($_result = $option->getValueById($optionValue)) {
					$result = $this->_getChargableOptionPriceXX(
                    $_result->getPrice(),
                    $_result->getPriceType(),
                    $basePrice
                );
            } else {
                if ($this->getListener()) {
                    $this->getListener()
                            ->setHasError(true)
                            ->setMessage(
                                Mage::helper('catalog')->__('Some of the products below do not have all the required options. Please remove them and add again with all the required options.')
                            );
                }
            }
        }
        return $result;
    }
    protected function _getChargableOptionPriceXX($price, $priceType, $basePrice)
    {
		$retValue = 0;
		if ( $priceType == 'percent' ) {
            $retValue = $basePrice + ($basePrice * $price / 100);
        } elseif ( $priceType == 'fixed' ) {
            $retValue = $basePrice + $price;
        } elseif ( $priceType == 'absolute' || $priceType == 'absoluteonce' ) {
            $retValue = $price;
        }
		return $retValue;
    }

}
