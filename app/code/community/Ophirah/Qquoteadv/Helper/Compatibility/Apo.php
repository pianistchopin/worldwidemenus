<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Helper_Compatibility_Apo
 */
class Ophirah_Qquoteadv_Helper_Compatibility_Apo extends Mage_Core_Helper_Abstract
{
    /**
     * Check if APO is enabled
     *
     * @return bool
     */
    public function isApoEnabled()
    {
        $status = false;
        if (Mage::helper('core')->isModuleEnabled('MageWorx_CustomOptions')) {
            //check in try cache in case it doesnt exits
            try {
                $helper = $this->getApoHelper();
                if ($helper) {
                    if ($helper->isEnabled()) {
                        $status = true;
                    }
                }
            } catch (Exception $e) {
                $status = false;
            }
        }

        return $status && Mage::helper('qquoteadv')->apoIsEnabled();
    }

    /**
     * GetApoHelper
     *
     * @return bool
     */
    public function getApoHelper()
    {
        $helper = false;
        if (Mage::helper('core')->isModuleEnabled('MageWorx_CustomOptions')) {
            //check in try cache in case it doesnt exists
            try {
                if (is_object(Mage::getConfig()->getNode('global/helpers/customoptions'))) {
                    $helper = Mage::helper('customoptions');
                }
            } catch (Exception $e) {
                $helper = false;
            }

            if (!$helper) {
                try {
                    if (is_object(Mage::getConfig()->getNode('global/helpers/mageworx_customoptions'))) {
                        $helper = Mage::helper('mageworx_customoptions');
                    }
                } catch (Exception $e) {
                    $helper = false;
                }
            }
        }

        return $helper;
    }

    /**
     * Function that extracts the custom options from the given post data
     *
     * @param $postData
     * @return array
     */
    public function getOptionsFromPostData($postData){
        $products = Array();
        $options = Array();
        if (isset($postData['product'])) {
            $mainProduct = Mage::getModel('catalog/product')->load($postData['product']);
        }

        if ($postData['options'] && $mainProduct) {
            foreach ($postData['options'] as $productKey => $addedProductOption) {
                if (is_array($addedProductOption)) {
                    foreach ($addedProductOption as $subProductId) {
                        $valueModel = $this->getValueModel($mainProduct, $productKey, $subProductId);
                        if ($this->isValidOptionProduct($valueModel)) {
                            $productId = $this->getOptionSku($valueModel->getSku());
                            $products[$productKey][$subProductId] = Mage::getModel('catalog/product')->load($productId);
                        } else {
                            if ($valueModel->getOption()->getType() == 'multiswatch') {
                                $options[$productKey] = $addedProductOption;
                            } else {
                                $options[$productKey][$subProductId] = $addedProductOption;
                            }
                        }
                    }
                } else {
                    $valueModel = $this->getValueModel($mainProduct, $productKey, $addedProductOption);
                    if ($this->isValidOptionProduct($valueModel)) {
                        $productId = $this->getOptionSku($valueModel->getSku());
                        $products[$productKey] = Mage::getModel('catalog/product')->load($productId);
                    } else {
                        $options[$productKey] = $addedProductOption;
                    }

                }
            }
        }
        return array(
            'products' => $products,
            'options'  => $options
        );
    }

    /**
     * Function that extracts the qty of a product from the given post data
     *
     * @param $postData
     * @return array
     */
    public function getProductQtyFromPostData($postData){
        $qty = Array();
        if(isset($postData['options']) && is_array($postData['options'])){
            foreach($postData['options'] as $productKey => $productId){
                if(is_array($productId)){
                    foreach($productId as $subProductId){
                        if(isset($postData['options_'.$productKey.'_'.$subProductId.'_qty'])){
                            $qty[$productKey][$subProductId] = $postData['options_'.$productKey.'_'.$subProductId.'_qty'];
                        }else{
                            $qty[$productKey][$subProductId] = $postData['qty'];
                        }
                    }
                }else{
                    if(isset($postData['options_'.$productKey.'_qty'])){
                        $qty[$productKey] = $postData['options_'.$productKey.'_qty'];
                    }else{
                        $qty[$productKey] = $postData['qty'];
                    }
                }
            }
        }
        return $qty;
    }

    /**
     * Get the value model for a given product option value combination
     *
     * @param $mainProduct
     * @param $optionId
     * @param $optionValue
     * @return mixed
     */
    public function getValueModel($mainProduct, $optionId, $optionValue){
        $optionModel = $mainProduct->getOptionById($optionId);
        $optionModel->setProduct($mainProduct);
        return $optionModel->getValueById($optionValue);
    }

    /**
     * Check if value model is valid
     *
     * @param $valueModel
     * @return bool
     */
    public function isValidOptionProduct($valueModel){
        if(isset($valueModel) && $valueModel->getSku() && $this->getOptionSku($valueModel->getSku())){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Returns the id of a product for the given SKU
     *
     * @param $sku
     * @return bool
     */
    public function getOptionSku($sku){
        $returnSku = Mage::getModel('catalog/product')->getIdBySku($sku);
        if($returnSku){
            return $returnSku;
        }else{
            return false;
        }
    }
}