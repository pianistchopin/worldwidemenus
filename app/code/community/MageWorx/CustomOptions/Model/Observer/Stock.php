<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Model_Observer_Stock {
    
    public function checkProductOptionsQty($observer) {
        $session = Mage::getSingleton('checkout/session');
        $helper = Mage::helper('mageworx_customoptions');
        if (!$this->_canMinMaxOptionsQtyCheck($helper)) {
            return $this;
        }
        
        $quoteItem = $observer->getEvent()->getItem();
        
        $product = $quoteItem->getProduct();
        if (!$product) {
            return $this;
        }
        if (is_null($product->getHasOptions())) {
            $product->load($product->getId());
        }
        if (!$product->getHasOptions()) {
            return $this;
        }
        
        $currentAction = $this->_getAction();
        if ($currentAction == "checkout_cart_updateItemOptions") {
            $post = Mage::app()->getRequest()->getParams(); 
        } else {
            $post = $helper->getInfoBuyRequest($product);
        }

        if (count($post) == 0) {
            return $this;
        }
        
        $invalidOpValues = $this->_getInvalidateOptions($product, $post);

        if (count($invalidOpValues)) {
            $session->getQuote()->setHasError(true);
            if ($session->getMessages()->count() == 0) {
                $session->addError($helper->__('The quantity of some custom options of products is not allowed for purchase.')); 
            }
            $this->_setMessageItems($quoteItem, $invalidOpValues['invalid_min'], 'minimum');
            $this->_setMessageItems($quoteItem, $invalidOpValues['invalid_max'], 'maximum');
            
            if ($currentAction == "checkout_cart_updateItemOptions") {
                $this->_redirectBack();
            }
        }
        
        return $this;
    }
    
    public function addToCartCheckProductOptionsQty($observer) {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$this->_canMinMaxOptionsQtyCheck($helper)) {
            return $this;
        }
        $error = false;
        $quoteItem = $observer->getEvent()->getQuoteItem();
        
        $product = $quoteItem->getProduct();
        if (!$product) {
            return $this;
        }
        if (is_null($product->getHasOptions())) {
            $product->load($product->getId());
        }
        if (!$product->getHasOptions()) {
            return $this;
        }
        
        $post = $helper->getInfoBuyRequest($product);
        if (count($post) == 0) {
            return $this;
        }
        
        $invalidOpValues = $this->_getInvalidateOptions($product, $post);
        if (count($invalidOpValues)) {
            $invalidMin = $invalidOpValues['invalid_min'];
            $invalidMax = $invalidOpValues['invalid_max'];
            
            $this->_updateValueQty($product, $invalidMin, $post, 'minimum');
            
            if (count($invalidMax)) {
                $quoteItem->setHasError(true);
            }
        }
        
        $product->getCustomOption('info_buyRequest')->setValue(serialize($post));
        
        return $this;
    }
    
    private function _updateValueQty($product, $values, &$post, $updateCode) {
        $helper = Mage::helper('mageworx_customoptions');
        
        if (count($values)) {
            foreach ($values as $value) {
                $option = $value->getOption();
                $qtyCode = $this->_getQtyCode($option, $value);
                
                if ($updateCode == 'minimum') {
                    $qty = $helper->removeQtyZeroes($value->getCustomoptionsMinQty());
                } else if ($updateCode == 'maximum') {
                    $qty = $helper->removeQtyZeroes($value->getCustomoptionsMaxQty());
                }

                if (isset($post[$qtyCode])) {
                    $post[$qtyCode] = $qty;
                }
            }
        }
    }
    
    private function _getPostQty($option, $value, $post) {
        $qty = 1;        
        $qtyCode = $this->_getQtyCode($option, $value);
        
        if (isset($post[$qtyCode])) {
            $qty = $post[$qtyCode];
        }
        
        return $qty;
    }
    
    private function _getQtyCode($option, $value) {
        $qtyCode = '';
        switch ($option->getType()) {
            case 'checkbox':
            case 'multiswatch':
            case 'hidden':
                $qtyCode = 'options_'.$option->getId().'_'.$value->getId().'_qty';
                break;
            case 'drop_down':
            case 'radio':
            case 'swatch':  
                $qtyCode = 'options_'.$option->getId().'_qty';
                break;                     
        }
        return $qtyCode;
    }
    
    private function _getInvalidateOptions($product, $post) {
        $helper = Mage::helper('mageworx_customoptions');
        $invalidOpValues = array();
        
        if (isset($post['options'])) {
            $options = $post['options'];
        } else {
            return $invalidOpValues;
        }
        
        foreach ($options as $optionId => $option) {
            $productOption = $product->getOptionById($optionId);
            if (!$productOption->getQntyInput()) continue;
            
            $optionType = $productOption->getType();
            if ($productOption->getGroupByType($optionType)!=Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) continue;                                        
            if (!is_array($option)) $option = array($option);

            foreach ($option as $optionTypeId) {
                if (!$optionTypeId) continue;
                $value = $productOption->getValueById($optionTypeId);
                if (!$value) continue;
                if (!is_object($value)) $value = new Varien_Object($value);
                
                $minQty = $helper->removeQtyZeroes($value->getCustomoptionsMinQty());
                $maxQty = $helper->removeQtyZeroes($value->getCustomoptionsMaxQty());
                $optionQty = $helper->getPostCustomoptionQty($product, $productOption, $value, $post);
                //$optionQty = $this->_getPostQty($productOption, $value, $post);
                
                if ($helper->allowedMinQtyInCart() && is_numeric($minQty) && $optionQty<$minQty) {
                    $invalidOpValues['invalid_min'][] = $value;                    
                }

                if ($helper->allowedMaxQtyInCart() && is_numeric($maxQty) && $optionQty>$maxQty) {
                    $invalidOpValues['invalid_max'][] = $value;
                }
            }
        }
        
        return $invalidOpValues;
    }

    private function _setMessageItems($item, $values, $code) {
        $helper = Mage::helper('mageworx_customoptions');
        if (count($values)) {
            $listErrors = $this->_getListErrors($values, $code);
            
            if (count($listErrors)) {
                foreach ($listErrors as $error) {
                    $item->setMessage($error);
                }
            }
        }
    }
    
    private function _getListErrors($values, $code) {
        $helper = Mage::helper('mageworx_customoptions');
        $listErrors = array();
        if (count($values)) {
            foreach ($values as $value) {
                $option = $value->getOption();
                $optionTitle = $option->getTitle();
                $valueTitle = $value->getTitle();
                $minQty = $value->getCustomoptionsMinQty();
                $maxQty = $value->getCustomoptionsMaxQty();
                
                if ($code == 'minimum') {
                    $listErrors[] = $helper->__('The minimum quantity allowed for purchase "%s - %s" option is %s.', $optionTitle, $valueTitle, $minQty * 1);
                }
                if ($code == 'maximum') {
                    $listErrors[] = $helper->__('The maximum quantity allowed for purchase "%s - %s" option is %s.', $optionTitle, $valueTitle, $maxQty * 1);
                }
            }
        }
        return $listErrors;
    }
    
    private function _canMinMaxOptionsQtyCheck($helper) {
        $error = true;
        
        if (!$helper->isEnabled() || !$helper->isInventoryEnabled() 
            || (!$helper->allowedMinQtyInCart() && !$helper->allowedMaxQtyInCart())) {
            $error = false;
        }
        
        return $error;
    }
    
    private function _getAction() {
        $request = Mage::app()->getRequest();
        return $request->getModuleName().'_'.$request->getControllerName().'_'.$request->getActionName();
    }
    
    private function _redirectBack() {
        $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer()  : Mage::getUrl();
        Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        Mage::app()->getResponse()->sendResponse();
        exit;
    }
}