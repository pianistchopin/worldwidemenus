<?php
class Aitoc_Aitcg_Model_System_Config_Source_Editor_Toolbox
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'icons',     'label'=>Mage::helper('aitcg')->__('Icons')),
            array('value' => 'accordion', 'label'=>Mage::helper('aitcg')->__('List')),
        );
    }
}
