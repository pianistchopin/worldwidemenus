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
 * Class Ophirah_Qquoteadv_Model_Module_Observer
 */
class Ophirah_Qquoteadv_Model_Module_Observer
{
    /**
     * Filter for Aitoc module to allow
     * simples with options to be added
     * as a child product in a grouped product
     *
     * @param $observer
     */
    public function addFilterDataBeforeAitoc($observer)
    {
        $addProduct = array();
        $newParams = array();

        $eventData = $observer->getEvent()->getData();
        $prodParams = $eventData['prodParams']->params;

        // Check if product is a grouped product with options data
        if (isset($prodParams['super_group']) && array_sum($prodParams['super_group']) > 0 && isset($prodParams['options']) && count($prodParams['options']) > 0) {
            $optionArray = $this->getAitocOptionArray($prodParams);

            foreach ($prodParams['super_group'] as $key => $value) {
                if (is_numeric($value) && $value > 0) {
                    $addProduct['product'] = $key;
                    $addProduct['qty'] = $value;

                    // Adding options
                    if (isset($optionArray[$key]['options'])) {
                        $addProduct['options'] = $optionArray[$key]['options'];
                    }

                    // Adding file upload info
                    if (isset($optionArray[$key]['options_file_action']) && is_object($optionArray[$key]['options_file_action'])) {
                        // set file data to params
                        $fileData = $optionArray[$key]['options_file_action'];
                        $addProduct[$fileData->getData('key')] = $fileData->getData('value');
                    }

                    // Add simple product params to array
                    $newParams[] = $addProduct;
                }
            }

            // Adding 'cart' key to trigger
            // foreach loop in indexController
            if (count($newParams) > 0) {
                $finalParams['cart'] = $newParams;
            } else {
                $finalParams = false; // keep original params
            }

            if ($finalParams) {
                $eventData['prodParams']->params = $finalParams;
            }
        }
    }

    /**
     * @param $prodParams
     * @return array|bool
     */
    public function getAitocOptionArray($prodParams)
    {
        $options = $prodParams['options'];
        if (!is_array($options)) {
            return false;
        }

        // Check for file uploads
        $fileUpload = $this->aitocFileUPload($prodParams);

        $return = array();
        foreach ($options as $key => $value) {
            $explodeKey = explode('_', $key);
            if (count($explodeKey) > 2) {
                $optionId = $explodeKey[0];
                $prodId = $explodeKey[1];

                $return[$prodId]['options'][$optionId] = $value;

                // Adding file upload data to return array
                $fileData = $fileUpload->getData($prodId);
                if ($fileData) {
                    if ($fileData->getData('key') && $fileData->getData('value')) {
                        $return[$prodId]['options_file_action'] = $fileData;
                    }
                }
            }
        }

        if (count($return) > 0) {
            return $return;
        }

        return false;
    }

    /**
     * @param $prodParams
     * @return bool|Varien_Object
     */
    public function aitocFileUPload($prodParams)
    {
        if (!is_array($prodParams)) {
            return false;
        }

        // Check Product params
        $return = new Varien_Object();
        foreach ($prodParams as $key => $value) {
            // check for file upload key
            if (substr($key, 0, 8) == 'options_') {
                $explodeKey = explode('_', $key);

                // Assumed array is constructed as follows
                // ["options_[OPTIONID]_[PRODUCTID]_file_action"]
                if ($explodeKey[3] == 'file' && $explodeKey[4] == 'action') {
                    // build new key name with option number
                    $newKey = $explodeKey[0] . '_' . $explodeKey[1] . '_' . $explodeKey[3] . '_' . $explodeKey[4];
                    // add data to new key
                    $fileData = new Varien_Object();
                    $fileData->setData('key', $newKey);
                    $fileData->setData('value', $value);
                    $return->setData($explodeKey[2], $fileData);
                }
            }
        }

        return $return;
    }

    /**
     * Function that checks for SCP and changes the configurable products to simples like SCP does.
     *
     * OrganicInternet_SimpleConfigurableProducts
     *
     * @param $observer
     */
    public function addScp($observer)
    {
        //only run if SCP is enabled
        if (Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')) {
            /** @var \Ophirah_Qquoteadv_IndexController $prodParams */
            $indexController = $observer->getEvent()->getData('prodParams');
            $productParams = $indexController->params;
            if (isset($productParams['product']) && isset($productParams['super_attribute'])) {
                $productId = $productParams['product'];
                $superAttribute = $productParams['super_attribute'];

                // load configurable product
                $product = Mage::getModel('catalog/product')->load($productId);
                $simple = Mage::getModel('catalog/product_type_configurable')
                    ->getProductByAttributes($superAttribute, $product);

                //switch configurable id for simple id
                if ($simple) {
                    $indexController->params['product'] = $simple->getId();
                    unset($indexController->params['super_attribute']);
                }
            }
        }

        return;
    }
}
