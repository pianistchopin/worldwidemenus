<?php
class Magecomp_AbsolutePricing_Block_Product_View_Options extends Mage_Catalog_Block_Product_View_Options
{
    public function getJsonConfig()
    {
        $config = array();
        foreach ($this->getOptions() as $option) {
            $priceValue = 0;
            if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                $_tmpPriceValues = array();
                foreach ($option->getValues() as $value) {
				   if($value->getPriceType() == 'absolute' || $value->getPriceType() == 'absoluteonce')
					{
				   		$price = Mage::helper('core')->currency($value->getPrice(true), false, false);
				   		$_tmpPriceValues[$value->getId()] = array( 'type' => $value->getPriceType(), 'price' => $price );
					}
					else
					{
						$_tmpPriceValues[$value->getId()] = $this->_getPriceConfiguration($value);	
					}
                }
                $priceValue = $_tmpPriceValues;
            } else {
                $priceValue = Mage::helper('core')->currency($option->getPrice(true), false, false);
            }
            $config[$option->getId()] = $priceValue;
        }
        return Mage::helper('core')->jsonEncode($config);
    }
}