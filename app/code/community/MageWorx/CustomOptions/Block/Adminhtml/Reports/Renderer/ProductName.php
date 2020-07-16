<?php


/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Block_Adminhtml_Reports_Renderer_ProductName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $productName = $this->_getValue($row);
        $productId = $row->getProductId();
        $productLink = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $productId));
        return '<a href="'.$productLink.'" target="_blank">'.$productName."</a>";
    }

}
