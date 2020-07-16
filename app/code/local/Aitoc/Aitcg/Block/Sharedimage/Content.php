<?php
    class Aitoc_Aitcg_Block_Sharedimage_Content extends Aitoc_Aitcg_Block_Sharedimage_Abstract
    {
        protected function _prepareLayout()
        {
//            $id = (string) $this->getRequest()->getParam('id');
//
//            $sharedImgModel = Mage::getModel('aitcg/sharedimage');
//            $sharedImgModel->load($id);
//
//            $sharedImgFullSizeUrl = $sharedImgModel->getUrlFullSizeSharedImg();
//            $sharedImgSmallSizeUrl = $sharedImgModel->getUrlSmallSizeSharedImg();
//
//            $productId = $sharedImgModel->getProductId();
//            $product = Mage::getModel('catalog/product')->load($productId);
//            $productUrl = $product->getProductUrl();
//            $productName = $product->getName();
//            $currentUrl = $this->helper('aitcg')->getSharedImgUrl($id);

//            $this->assign('productUrl', $productUrl);
//            $this->assign('sharedImgFullSizeUrl', $sharedImgFullSizeUrl);
//            $this->assign('sharedImgSmallSizeUrl', $sharedImgSmallSizeUrl);
//            $this->assign('productName', $productName);
//            $this->assign('currentUrl', $currentUrl);
//            $this->assign('sharedImgId', $id);
//            $this->assign('product', $product);
            
            parent::_prepareLayout();
        }       
}