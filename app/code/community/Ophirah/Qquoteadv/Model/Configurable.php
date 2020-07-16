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
 * Class Ophirah_Qquoteadv_Model_Configurable
 */
class Ophirah_Qquoteadv_Model_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{
    /**
     * Retrieve Selected Attributes info
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getSelectedAttributesInfoId($product = null)
    {
        $attributes = array();
        Varien_Profiler::start('CONFIGURABLE:' . __METHOD__);
        $attributesOption = $this->getProduct($product)->getCustomOption('attributes');
        if ($attributesOption) {
            $data = unserialize($attributesOption->getValue());
            $this->getUsedProductAttributeIds($product);

            $usedAttributes = $this->getProduct($product)->getData($this->_usedAttributes);

            foreach ($data as $attributeId => $attributeValue) {
                if (isset($usedAttributes[$attributeId])) {
                    $attribute = $usedAttributes[$attributeId];
                    //$label = $attribute->getLabel();
                    $label = $attribute->getAttributeId();
                    $value = $attribute->getProductAttribute();
                    if ($value->getSourceModel()) {
                        //$value = $value->getSource()->getOptionText($attributeValue);
                        $value = $value->getSource()->getOptionId($attributeValue);
                    } else {
                        $value = '';
                    }

                    //$attributes[] = array('label'=>$label, 'value'=>$value);
                    $attributes[$label] = $value;
                }
            }
        }
        Varien_Profiler::stop('CONFIGURABLE:' . __METHOD__);
        return $attributes;
    }

    /**
     * Retrieve Selected Attributes info
     *
     * @param Mage_Catalog_Model_Product $product
     * @param $attribute
     * @return array
     */
    public function getSelectedAttributesInfoText($product, $attribute)
    {
        $attributeText = array();
        if (!is_array($attribute)) {
            // changing string to array
            $attribute = unserialize($attribute);
        }
        $attributeArray = $this->getConfigurableAttributesAsArray($product);
        foreach ($attributeArray as $key => $value) {
            if (isset($attribute['super_attribute']))
                foreach ($attribute['super_attribute'] as $k => $v) {
                    if ($k == $value['attribute_id']) {
                        $attributeText[$key]['label'] = $value['label'];
                    }
                    foreach ($value['values'] as $kv => $vv) {
                        if ($v == $vv['value_index']) {
                            $attributeText[$key]['value'] = $vv['label'];
                        }
                    }
                }
        }
        return $attributeText;
    }
}
