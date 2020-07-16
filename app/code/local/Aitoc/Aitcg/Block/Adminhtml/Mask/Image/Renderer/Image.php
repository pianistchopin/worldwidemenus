<?php
class Aitoc_Aitcg_Block_Adminhtml_Mask_Image_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
 
    public function render(Varien_Object $row)
    {   $file = $row->getData($this->getColumn()->getIndex());
        if(!empty($file))
        {
            $html = '<img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). 'custom_product_preview/mask/preview/' . $file . '"';
            $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '"/>';
        
        }
        else
        {
            $html = Mage::helper('aitcg')->__('Image is not uploaded.');
        }
        
        return $html;
    }
}