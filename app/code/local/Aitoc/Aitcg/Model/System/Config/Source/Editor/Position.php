<?php
class Aitoc_Aitcg_Model_System_Config_Source_Editor_Position
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Aitoc_Aitcg_Helper_Data::INTEGRATION_POPUP,   'label'=>Mage::helper('aitcg')->__('Popup window')),
            array('value' => Aitoc_Aitcg_Helper_Data::INTEGRATION_GALLERY, 'label'=>Mage::helper('aitcg')->__('On-page Editor')),
        );
    }
}
