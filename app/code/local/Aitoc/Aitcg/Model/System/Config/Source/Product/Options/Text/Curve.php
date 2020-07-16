<?php

class Aitoc_Aitcg_Model_System_Config_Source_Product_Options_Text_Curve
{
    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label' => Mage::helper('adminhtml')->__('Yes')),
            array('value' => '0', 'label' => Mage::helper('adminhtml')->__('No'))
        );
    }
}
