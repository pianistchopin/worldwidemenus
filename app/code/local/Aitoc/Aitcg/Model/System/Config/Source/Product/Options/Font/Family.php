<?php

class Aitoc_Aitcg_Model_System_Config_Source_Product_Options_Font_Family
{
    public function toOptionArray()
    {
        $params = array();

        $params[] = array('value' => 0, 'label' => Mage::helper('adminhtml')->__('New font family'));

        $data = Mage::getModel('aitcg/font_family')->getCollection();

        foreach ($data as $item) {
            $params[] = array(
                'value' => $item->getFontFamilyId(),
                'label' => Mage::helper('adminhtml')->__($item->getTitle())
            );
        }

        return $params;
    }
}
