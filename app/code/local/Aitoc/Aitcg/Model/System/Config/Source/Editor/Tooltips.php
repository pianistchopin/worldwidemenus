<?php
class Aitoc_Aitcg_Model_System_Config_Source_Editor_Tooltips
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'dark',  'label'=>Mage::helper('aitcg')->__('Dark')),
            array('value' => 'light', 'label'=>Mage::helper('aitcg')->__('Light')),
        );
    }
}
