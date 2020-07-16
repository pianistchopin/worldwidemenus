<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_System_Config_Source_Inventory_Options {
    public function toOptionArray() {
        $helper = Mage::helper('mageworx_customoptions');
        return array(
            array('value' => 0, 'label'=>$helper->__('Hide')),
            array('value' => 1, 'label'=>$helper->__('Display for in Stock Only')),
            array('value' => 2, 'label'=>$helper->__('Display Including Low Stock')),
            array('value' => 3, 'label'=>$helper->__('Display for Low Stock Only'))
        );
    }
}