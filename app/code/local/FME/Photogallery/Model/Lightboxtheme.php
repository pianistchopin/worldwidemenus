<?php

class FME_Photogallery_Model_Lightboxtheme
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'light_rounded',   'label'=>Mage::helper('adminhtml')->__('Light Rounded')),
            array('value'=>'dark_rounded',   'label'=>Mage::helper('adminhtml')->__('Dark Rounded')),
            array('value'=>'light_square',   'label'=>Mage::helper('adminhtml')->__('Light Square')),
            array('value'=>'dark_square',   'label'=>Mage::helper('adminhtml')->__('Dark Square')),
            array('value'=>'facebook',   'label'=>Mage::helper('adminhtml')->__('Facebook')),
        );
    }
    
}