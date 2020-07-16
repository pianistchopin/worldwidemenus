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
 * @license     https://www.cart2quote.com/ordering-licenses
 */

/**
 * Class Ophirah_Qquoteadv_Model_Observer_Products_Massupdate
 */
class Ophirah_Qquoteadv_Model_Observer_Products_Massupdate
{

    /**
     * Exclude the allow quote groups from the default mass action form.
     * @param $observer
     */
    public function excludeFromGrid($observer){
        $attributeTab = $observer->getObject();

        $excludedFieldList = $attributeTab->getFormExcludedFieldList();
        foreach($this->getFormFieldsForExclusion() as $field){
            $excludedFieldList = $this->excludeFromForm($excludedFieldList, $field);
        }

        $attributeTab->setFormExcludedFieldList($excludedFieldList);
    }

    /**
     * Mass update the Cart2Quote products
     * @param $observer
     */
    public function updateProducts($observer){
        $products = Mage::helper('adminhtml/catalog_product_edit_action_attribute')->getProductIds();
        $request = $observer->getControllerAction()->getRequest();

        foreach($this->getMassUpdateFields() as $field){
            $this->massUpdate($field, $products, $request);
        }
    }

    /**
     * Mass update the products by the given post values of the mass update.
     * @param $type         // Group type
     * @param $products     // Products that needs to be updated.
     * @param $request      // Post data
     * @return $this
     */
    protected function massUpdate($type, $products, $request)
    {
        $postField = Mage::helper('qquoteadv/massupdate')->getMassPostTypes($type);
        Mage::helper('qquoteadv/massupdate')->update(
            $type,
            $products,
            $request->getParam($postField, false)
        );
        return $this;
    }

    /**
     * Adds a field item to the exclude list.
     * @param array $excludedFieldList
     * @param $item
     * @return array
     */
    protected function excludeFromForm(array $excludedFieldList, $item){
        $excludedFieldList[] = $item;
        return $excludedFieldList;
    }

    /**
     * Array of fields that needs to be excluded
     * @return array
     */
    protected function getFormFieldsForExclusion(){
        return Mage::helper('qquoteadv/massupdate')->getMassUpdateTypes();
    }

    /**
     * Array of fields that needs to be updated
     * @return array
     */
    protected function getMassUpdateFields(){
        return Mage::helper('qquoteadv/massupdate')->getMassUpdateTypes();
    }
}
