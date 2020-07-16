<?php

class Aitoc_Aitcg_Model_Rewrite_Checkout_Cart extends Mage_Checkout_Model_Cart
{
    /**
     * @return Aitoc_Aitcg_Model_Sales_Order_Item_Converter
     */
    protected function _getConverter($orderItem)
    {
        return Mage::getModel('aitcg/sales_order_item_converter')->setOrderItem($orderItem);
    }

    public function addOrderItem($orderItem, $qtyFlag=null)
    {
        $orderItem = $this->_getConverter($orderItem)->getConvertedOrderItem();
        return parent::addOrderItem($orderItem, $qtyFlag);
    }

}
