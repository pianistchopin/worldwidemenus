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
 * Class Ophirah_Qquoteadv_Model_Bundle
 */
class Ophirah_Qquoteadv_Model_Bundle extends Mage_Bundle_Model_Product_Type
{
    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param $option
     * @param $selection
     * @param $product
     * @return array
     */
    public function getBundleOptionsText($option, $selection, $product)
    {
        $optionText = $this->getOptionsByIds(array($option), $product)->getItems();

        if (!is_array($selection)) {
            $selection = array($selection);
        }
        $selectText = $this->getSelectionsByIds($selection, $product)->getItems();

        $optionSelection = array(
            'selection' => $selectText,
            'option' => $optionText
        );

        return $optionSelection;
    }

    /**
     * Get bundled selections (selections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param $product
     * @param $attribute
     * @return array
     */
    public function getBundleOptionsSelection($product, $attribute)
    {

        if (!is_array($attribute) && !($attribute instanceof Varien_Object)) {
            // changing string to array
            $attribute = unserialize($attribute);
        }

        $optionsQtyKey = $optionsQty = null;

        // getting the values for bundle_options
        // bundle_options key on $params contain key => value pairs of options and values
        // getting only the values using array_values function
        $options = array();
        if(isset($attribute['bundle_option'])){
            $options = $attribute['bundle_option'];
        }


        if (isset($attribute['bundle_option_qty'])) {
            // bundle options quantity
            $optionsQty = $attribute['bundle_option_qty'];
            // fetching keys from the array
            $optionsQtyKey = array_keys($optionsQty);
        }

        // making bundle options values array
        $data = array();
        $selectionData = array();


        foreach ($options as $keyO => $valueO) {
            $bundleOptions = $this->getBundleOptionsText($keyO, $valueO, $product);

            foreach ($bundleOptions as $key => $value) {
                if ($key == 'selection' && $value !== null) {

                    if ($value != null) { // If NONE is an option
                        foreach ($value as $itemKey => $item) {

                            $selectionData = array(
                                'id' => $item->getId(),
                                'title' => $item->getName(),
                                'price' => $item->getPrice(), //Mage::helper('checkout')->formatPrice((int)$item->getPrice()),
                                'qty' => ($item->getSelection_qty() > 1) ? $item->getSelection_qty() : 1,
                            );
                        }

                    } else {
                        $selectionData = array(
                            'id' => "",
                            'title' => "",
                            'price' => "",
                            'qty' => 0,
                        );
                    }
                }

                if ($key == 'option') {
                    foreach ($value as $itemKey => $item) {
                        // updating quantity in the selectionData array
                        if (is_array($optionsQtyKey) && in_array($item->getOptionId(), $optionsQtyKey)) {
                            $selectionData['qty'] = $optionsQty[$item->getOptionId()];
                        }

                        $data[$item->getOptionId()] = array(
                            'option_id' => $item->getOptionId(),
                            'label' => $item->getTitle(),
                            'value' => array($selectionData)
                        );

                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get Original Price for Bundle Product
     * with selected options and dynamic pricing.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array | Varien_Object $params // including BuyRequest
     * @return int
     */
    public function getOriginalPrice(Mage_Catalog_Model_Product $product, $params)
    {
        if (is_array($params) && !isset($params['qty'])) {
            $params['qty'] = 1;
        }
        if (!is_object($params)) {
            $request = new Varien_Object();
            $request->setData($params);
        } elseif ($params instanceof Varien_Object) {
            $request = $params;
        } else {
            $message = 'Product params are not valid';
            Mage::log('Message: ' .$message, null, 'c2q.log', true);
            return 0;
        }
        $originalPrice = 0;
        $quoteClone = Mage::getModel('sales/quote');
        $quoteClone->addProduct($product, $request);

        foreach ($quoteClone->getAllVisibleItems() as $item) {
            if ($item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $newProduct = $item->getProduct();
                Mage::helper('catalog/product')->setSkipSaleableCheck(true);
                $originalPrice = Mage::getModel('bundle/product_price')->getFinalPrice($params['qty'], $newProduct);
            }
        }
        return $originalPrice;
    }
}
