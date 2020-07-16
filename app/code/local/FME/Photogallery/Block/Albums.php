<?php
/**
* 
*/
class FME_Photogallery_Block_Albums extends Mage_Core_Block_Template
{
    
    public function __construct()
    {
        parent::__construct();
        $collection = Mage::getModel('photogallery/gallery')->getCollection()
       // ->addStoreFilter(Mage::app()->getStore(true)->getId())
        ->addFieldToFilter('status', 1);
        $this->setCollection($collection);
    }
 
    protected function _prepareLayout()
    {
        if(!Mage::helper('photogallery')->getEnable()) {
         return false;
        }
        parent::_prepareLayout();
 
        $pager = $this->getLayout()->createBlock('page/html_pager', 'album.pager');
        $pager->setAvailableLimit(array('all'=>'all'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle(Mage::helper('photogallery')->getListPageTitle());
            $head->setDescription(Mage::helper('photogallery')->getListMetaDescription());
            $head->setKeywords(Mage::helper('photogallery')->getListMetaKeywords());
        }

        return $this;
    }
 
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}