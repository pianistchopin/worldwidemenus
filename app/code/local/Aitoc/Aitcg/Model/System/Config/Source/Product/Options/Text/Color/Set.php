<?php

class Aitoc_Aitcg_Model_System_Config_Source_Product_Options_Text_Color_Set
{
    public function toOptionArray()
    {
        $aColorset = array();
        $colorsetModel = Mage::getModel('aitcg/font_color_set');
        $colorsetIds = $colorsetModel->getCollection()->getAllIds();
        foreach($colorsetIds as $colorsetId)
        {
            $colorset = $colorsetModel->load($colorsetId);
            $status = $colorset->getStatus();
            if($status)
            {
                $aColorset[$colorset->getId()] = array('value' => $colorset->getId(), 'label' => $colorset->getName() );
            }    
        }
        return $aColorset;
    }
}
