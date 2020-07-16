<?php
class Aitoc_Aitcg_Helper_Style extends Mage_Core_Helper_Abstract
{
    public function getStyleOptions()
    {
        $cachePath = 'aitcg_style_options';
        $options = array();
        $canUseCache = Mage::app()->useCache('config');
        if ($canUseCache && $options = Mage::app()->loadCache($cachePath)) {
            try {
                $options = unserialize($options);
            } catch (Exception $e) {
                // no need to do anything - just catch and dump
            }
        }
        
        if (!is_array($options) || empty($options)) {
            $styleOptions = array(
                'tooltips_style',
                'popups_bg',
                
                'buttons_bg',
                'buttons_bg_hover',
                'buttons_border',
                'buttons_border_hover',
                'buttons_text',
                'buttons_text_hover',
            
                'icons_style',
                'icons_bg',
                'icons_bg_hover',
                'icons_border',
                'icons_border_hover',
                'icons_text',
                'icons_text_hover',
                'icons_inactive_opacity'
            );
            
            $stylePath = 'catalog/aitcg_style/';
            
            
            foreach ($styleOptions as $optionName) {
                $options[$optionName] = Mage::getStoreConfig($stylePath.$optionName);
            }
            $options['skin_url'] = Mage::getDesign()->getSkinUrl('aitoc/aitcg/images');
            
            if ($canUseCache && !empty($options)) {
                Mage::app()->saveCache(serialize($options), $cachePath, array(Mage_Core_Model_Config::CACHE_TAG));
            }
        }
        return new Varien_Object($options);
    }
}
