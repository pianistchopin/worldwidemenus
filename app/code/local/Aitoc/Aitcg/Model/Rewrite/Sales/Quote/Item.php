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
       $post = Mage::app()->getRequest()->getParam('one_of_cost');
        $oneofcost=0;

        $quote=Mage::getSingleton('checkout/session')->getQuote();
        if(!empty($post) &&  in_array($post,array("off","on"))){
            $oneofcost=($post=="on")?1:0;
            $quote->setOneOffCost($oneofcost);
            // Mage::getSingleton('core/session')->setOneoffcost($oneofcost);
        }
        elseif($quote->hasData('one_off_cost')){
            $oneofcost=$quote->getData('one_off_cost');
        }else{
            // $oneofcost=Mage::getSingleton('core/session')->getOneoffcost();
        }
        $qty = $this->getTotalQty();

        $total = $this->getStore()->roundPrice($this->getCalculationPriceOriginal()) * $qty;
        $baseTotal = $this->getStore()->roundPrice($this->getBaseCalculationPriceOriginal()) * $qty;
        $qty = $this->getTotalQty();
        $_customOptions = $this->getProduct()->getTypeInstance(true)->getOrderOptions($this->getProduct());

        $productOpsCollection = Mage::getModel('catalog/product')->load($this->getProduct()->getId())->getProductOptionsCollection();

        $product = $this->getProduct();
        if($quote->hasData('one_cost_opts'))
        {
            $optArray=$quote->getData('one_cost_opts');
        }else{
            $optArray=array();
        }
        foreach($_customOptions['options'] as $_option) {
            if (!empty($_option['option_value'])) {
                if ($optionId = $_option['option_id']) {
                    if ($option = $this->getProduct()->getOptionById($optionId)) {
                        //option_for_section  shape_mask
                        $section=$option->getData('option_for_section');
                        if ($option->getData('is_one_of_cost') && $qty>0 && !empty($section)) {
                            $product=$this->getProduct();
                            $priceModel=$this->getProduct()->getPriceModel();
                            $confItemOption = $product->getCustomOption('option_'.$option->getId());
                            $group = $option->groupFactory($option->getType())
                                ->setOption($option)
                                ->setConfigurationItemOption($confItemOption);
                            $price=$group->getOptionPrice($confItemOption->getValue(),$priceModel->getBasePrice($this->getProduct()));

                            if(in_array($section,$optArray) && $oneofcost)
                            {
                                $baseTotal-=($price* ($qty));
                                $total-=($price* ($qty));
                            }
                            elseif($qty>1){
                                $baseTotal-=($price* ($qty-1));
                                $total-=($price* ($qty-1));

                            }
                            if(!in_array($section,$optArray))
                            {
                                $optArray[]=$section;
                                $quote->setOneCostOpts($optArray);
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
