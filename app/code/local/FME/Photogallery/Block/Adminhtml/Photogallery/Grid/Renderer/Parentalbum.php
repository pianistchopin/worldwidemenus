<?php

class FME_Photogallery_Block_Adminhtml_Photogallery_Grid_Renderer_Parentalbum extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        // echo "<pre>"; print_r($row->getData('parent_gallery_id')); exit();
        if($row->getData($this->getColumn()->getIndex())==""){
            return "";
        } else {
            // $photogalleryId = $row->getData($this->getColumn()->getIndex()); 
            // $collection = Mage::getModel('photogallery/photogallery')->load($photogalleryId);
            $parentId = $row->getData('parent_gallery_id');
            $parentAlbum= Mage::getModel('photogallery/gallery')->load($parentId)->getGalleryTitle();
            return  $parentAlbum;
        }
    }
} 