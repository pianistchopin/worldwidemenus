<?php

class Aitoc_Aitcg_Model_Rewrite_Sales_Quote_Item  extends Mage_Sales_Model_Quote_Item{
    public function calcRowTotal()
    {
       // echo "<pre>"; print_r(get_class_methods($this->getQuote())); die;
        /*if ($this->customRowTotalPrice !== null) {
            $this->setRowTotal($this->getStore()->roundPrice($this->customRowTotalPrice));
            $this->setBaseRowTotal($this->getStore()->roundPrice($this->customRowTotalPrice));
            return $this;
        }*/

        $qty = $this->getTotalQty();

        $total = $this->getStore()->roundPrice($this->getCalculationPriceOriginal()) * $qty;
        $baseTotal = $this->getStore()->roundPrice($this->getBaseCalculationPriceOriginal()) * $qty;
        $qty = $this->getTotalQty();
        $_customOptions = $this->getProduct()->getTypeInstance(true)->getOrderOptions($this->getProduct());

        $productOpsCollection = Mage::getModel('catalog/product')->load($this->getProduct()->getId())->getProductOptionsCollection();

        $product = $this->getProduct();

        foreach($_customOptions['options'] as $_option) {

            if (!empty($_option['option_value'])) {
                if ($optionId = $_option['option_id']) {
                    if ($option = $this->getProduct()->getOptionById($optionId)) {
                        if ($option->getData('is_one_of_cost') && $qty>1) {
                            $product=$this->getProduct();
                            $priceModel=$this->getProduct()->getPriceModel();
                            $confItemOption = $product->getCustomOption('option_'.$option->getId());
                            $group = $option->groupFactory($option->getType())
                                ->setOption($option)
                                ->setConfigurationItemOption($confItemOption);
                            $price=$group->getOptionPrice($confItemOption->getValue(),$priceModel->getBasePrice($this->getProduct()));

                            if($price > 0)
                            {
                                $baseTotal-=($price* ($qty-1));
                                $total-=($price* ($qty-1));
                            }

                        }
                    }
                }
            }
        }


        $this->setRowTotal($this->getStore()->roundPrice($total));
       $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));




        return $this;
    }
}
