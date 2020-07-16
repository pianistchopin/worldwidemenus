<?php

class Aitoc_Aitcg_Model_System_Config_Backend_Enablehomepagepromo extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {

        return $this->_getPromoBlock();
        
        
    }
    /*
    protected function _getArrayStoresOnDefault()
    {
        $arrayStores = array();
        $websites = Mage::getModel('core/website')->getCollection();
        foreach ($websites as $website)
        {
            
            $asd = Mage::getModel('core/config_data')->getCollection()->addFieldToFilter('scope', 'website')->addFieldToFilter('scope_id', $website->getId())->addFieldToFilter('path', 'catalog/aitcg/aitcg_enable_homepage_promo');
            if ($asd)
            $arrayStores += $this->_getArrayStoresOnWebsite($website);
        }
        return $arrayStores;
    }
    
    
    protected function _getArrayStoresOnWebsite($website)
    {
        //if()
        $arrayStores = array();
        //Mage::getStoreConfig('design/header/logo_src', $id)
        $stories = $website->getStoreCollection();        
        foreach ($stories as $store)
        {
            //print_r($website);
            $asd = $store->getConfig('catalog/aitcg/aitcg_enable_homepage_promo');
            if ($store->getConfig('catalog/aitcg/aitcg_enable_homepage_promo') == 1)
                $arrayStores[]= $store->getId();
        }
        return $arrayStores;
    }
    public function _afterDelete()
    {
        parent::_afterDelete();

        $block = $this->_getPromoBlock();
        //$test = 
        if($block)
        {
            //$block->setIsActive($this->getValue());
            if($this->getScopeId()==0)
            {
                
            }
            $arrayStores = $block->getStores();
            if($this->getValue() == 1)
            {
                $arrayStores[] = $this->getScopeId();
                
            }
            else
            {
                $arrayStores = array_diff($arrayStores, array($this->getScopeId()));
            }
            
//            var_dump($arrayStores);die();
            $block->setStores($arrayStores) ;
            $block->save();
        }
        
    }
    */
    /**
     * Get aitcg-homepage-promo-block. If it does not exist, create block.
     * 
     * @return Mage_Cms_Model_Block
     */
    protected function _getPromoBlock()
    {
        //Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $block = Mage::getModel('cms/block')->load('aitcg-homepage-promo-block');
        $data=$block->getData();
        if(empty($data))
        {    
            $block = $this->_createNewBlock();
        }
        return $block;

    }
            
    /**
     * Create aitcg-homepage-promo-block
     * 
     * @return Mage_Cms_Model_Block
     */
    protected function _createNewBlock()
    {
        $staticBlock = array(
            'title' => 'Aitcg Homepage Promo Block',
            'identifier' => 'aitcg-homepage-promo-block',                   
            'content' => '<div style="background-color:red;font-color:white;color:white;width: 950px; display: inline-block">Sample Test Block</div>',
            'is_active' => 1,                   
            'stores' => array(0)
            );

        Mage::getModel('cms/block')->setData($staticBlock)->save();
        
        return Mage::getModel('cms/block')->load('aitcg-homepage-promo-block');
        
    }
}
