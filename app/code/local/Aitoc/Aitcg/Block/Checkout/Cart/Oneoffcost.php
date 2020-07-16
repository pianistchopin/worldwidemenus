<?php
class Aitoc_Aitcg_Block_Checkout_Cart_Oneoffcost extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        return parent::_toHtml();
    }
    public function getOneOffCost(){
        $quote = Mage::getModel('checkout/cart')->getQuote();
        if($quote->hasData('one_off_cost')){
            return $quote->getData('one_off_cost');
        }
        else{
            return 0;
        }
    }

}