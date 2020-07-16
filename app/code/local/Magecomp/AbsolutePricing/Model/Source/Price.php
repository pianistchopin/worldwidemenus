<?php
class Magecomp_AbsolutePricing_Model_Source_Price extends Mage_Adminhtml_Model_System_Config_Source_Product_Options_Price
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'fixed', 'label' => Mage::helper('adminhtml')->__('Fixed')),
            array('value' => 'percent', 'label' => Mage::helper('adminhtml')->__('Percent')),
            array('value' => 'absolute', 'label' => Mage::helper('absolutepricing')->__('Absolute')),
			array('value' => 'absoluteonce', 'label' => Mage::helper('absolutepricing')->__('Absolute-Once')),
        );
    }
}