<?php
    class Aitoc_Aitcg_Block_Sharedimage_Head extends Aitoc_Aitcg_Block_Sharedimage_Abstract
    {
        protected function _prepareLayout()
        {
            $product = $this->_getProductModel();

            if ($headBlock = $this->getLayout()->getBlock('head')) 
            {
                if ($description = $product->getMetaDescription()) 
                {
                    $headBlock->setDescription( ($description) );
                } 
                else
                {
                    $headBlock->setDescription($product->getDescription());
                }
            }

            
            parent::_prepareLayout();
        } 
      
}