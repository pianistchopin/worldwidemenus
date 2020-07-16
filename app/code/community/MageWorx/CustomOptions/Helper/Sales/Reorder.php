<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Helper_Sales_Reorder extends Mage_Sales_Helper_Reorder {
    
    public function canReorder(Mage_Sales_Model_Order $order) {
        
        $helper = Mage::helper('mageworx_customoptions');
        
        if (!$helper->isEnabled() || !$helper->isOptionSkuPolicyEnabled()) return parent::canReorder($order);
        
        if (!$this->isAllow()) return false;
        
        
        // copy from $order->canReorder():
        if ($order->canUnhold() || $order->isPaymentReview() || !$order->getCustomerId()) {
            return false;
        }
        
        $products = array();
        foreach ($order->getItemsCollection() as $item) {
            $products[] = $item->getProductId();
        }
        
        if (!empty($products)) {
            foreach ($products as $productId) {
                if ($productId==0) continue;
                $product = Mage::getModel('catalog/product')
                    ->setStoreId($order->getStoreId())
                    ->load($productId);
                if (!$product->getId() || !$product->isSalable()) {
                    return false;
                }
            }
        }
        
        if ($order->getActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_REORDER) === false) {
            return false;
        }

        return true;
    }
    
}