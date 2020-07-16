<?php
class Magecomp_AbsolutePricing_Model_Product_Type_Price extends Mage_Catalog_Model_Product_Type_Price
{
    public function getFinalPrice($qty=null, $product)
    {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }
        $finalPrice = $product->getPrice();
        $finalPrice = $this->_applyTierPrice($product, $qty, $finalPrice);
        $finalPrice = $this->_applySpecialPrice($product, $finalPrice);
        $product->setFinalPrice($finalPrice);
        Mage::dispatchEvent('catalog_product_get_final_price', array('product'=>$product, 'qty' => $qty));
        $finalPrice = $product->getData('final_price');
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        return max(0, $finalPrice);
    }
    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        if ($optionIds = $product->getCustomOption('option_ids')) {
            $basePrice = $finalPrice;
            $finalPrice = 0;
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {

                    $quoteItemOption = $product->getCustomOption('option_'.$option->getId());
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);
                    $price = $group->getOptionPrice($quoteItemOption->getValue(), $basePrice);
					$finalPrice += $price;
					$values = $option->getValues();
					foreach($values as $v){
						if($v->getPriceType() == 'fixed')
						{
							return $finalPrice;
						}
						
						if($v->getPriceType() == 'percent')
						{
							return $finalPrice;
						}
						
						if($quoteItemOption->getValue() == $v->getId() && $v->getPriceType() == 'absoluteonce')
						{
							if($qty > 1)
							{
							 	$finalPrice = $finalPrice - $price;
							 	$total = $v->getPrice() / $qty;
							 	$finalPrice += $total;
							}
							if($qty == 1)
							{
								$finalPrice += $basePrice;
							}
						}
						
						// For Qty - 1
						if($quoteItemOption->getValue() == $v->getId() && $qty == 1 && $v->getPriceType() != 'absoluteonce' && $v->getPriceType() != 'absolute')
						{
							$finalPrice -= $basePrice;
						}
						
						// For Qty - 2
						if($quoteItemOption->getValue() == $v->getId() && $qty > 1 && $v->getPriceType() == 'absoluteonce')
						{
							$finalPrice += $basePrice;
						}
						
						if($quoteItemOption->getValue() == $v->getId() && $qty > 1 && $v->getPriceType() != 'absoluteonce' && $v->getPriceType() != 'absolute')
						{
							$finalPrice -= $basePrice;
						}
					}
                }
            }
        }
        return $finalPrice;
    }
}