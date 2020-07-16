<?php
class Aitoc_Aitcg_Block_Catalog_Product_View_Socialbuttons extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if(!Mage::getStoreConfig('catalog/aitcg/aitcg_use_social_networks_sharing'))
        {
            return '';
        }
        
        return parent::_toHtml();
    }
}