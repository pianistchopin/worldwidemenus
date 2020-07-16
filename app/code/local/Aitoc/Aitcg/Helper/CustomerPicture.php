<?php

class Aitoc_Aitcg_Helper_CustomerPicture extends Mage_Core_Helper_Abstract
{
    
    public function getVersion()
    {
        return (string)Mage::getConfig()->getNode('aitcg/format_version');
    }
    
}