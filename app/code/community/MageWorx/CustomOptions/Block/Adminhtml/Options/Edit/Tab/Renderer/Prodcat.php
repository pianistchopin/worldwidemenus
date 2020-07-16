<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Block_Adminhtml_Options_Edit_Tab_Renderer_Prodcat extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $text = array();
        $categoryIds = $row->getCategoryIds();
        $allCats = Mage::helper('mageworx_customoptions')->getCategories();
        if ($categoryIds && is_array($categoryIds)) {
            foreach ($categoryIds as $id) {
                if (isset($allCats[$id])) {
                    $text[] = str_replace('&nbsp;', '', $allCats[$id]);
                }
            }
        }
        return implode(', ', $text);
    }
}