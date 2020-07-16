<?php

class Aitoc_Aitcg_Model_System_Config_Source_Product_Options_Input
{
    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label' => Mage::helper('adminhtml')->__('Text Field')),
            array('value' => '0', 'label' => Mage::helper('adminhtml')->__('Text Area'))
        );
    }
}
