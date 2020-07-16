<?php

class MageWorx_CustomOptions_Model_Adminhtml_System_Config_Source_Product_Options_Price extends Mage_Adminhtml_Model_System_Config_Source_Product_Options_Price
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'fixed', 'label' => Mage::helper('adminhtml')->__('Fixed')),
            array('value' => 'percent', 'label' => Mage::helper('adminhtml')->__('Percent')),
            array('value' => MageWorx_CustomOptions_Model_Catalog_Product_Option::PRICE_TYPE_OPTIONS_PERCENT, 'label' => Mage::helper('adminhtml')->__('Percent based on options')),
            array('value' => MageWorx_CustomOptions_Model_Catalog_Product_Option::PRICE_TYPE_FIXED_PER_CHARACTER, 'label' => Mage::helper('adminhtml')->__('Fixed per сharacter')),
        );
    }
}
