<?php

class FME_Photogallery_Block_Adminhtml_Photogallery_Grid_Renderer_Stores extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    
     public function render(Varien_Object $row) 
     {
    $store=explode(',', $row->getStore());
      
    $photogallery_id= $row->getData('photogallery_id'); 
    $stores= Mage::getResourceModel('photogallery/photogallery')->lookupStoreIds($photogallery_id);
            $data="";
         if($stores!="" and in_array(0, $stores))
         {
                $data.='All Store Views';
         } else {
                $data="";
                $a=0;
                foreach ($stores as $sto)
                {
                     $data= $data.Mage::getModel('core/store')->load($sto[$a])->getName().'<br>';
                 $a+1;
                }
         }

        return $data;
     }
} 