<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_System_Config_Source_Outofstock_Options {
    public function toOptionArray() {
        $helper = Mage::helper('mageworx_customoptions');
        return array(
            array('value' => 1, 'label'=>$helper->__('Hide')),
            array('value' => 0, 'label'=>$helper->__('Disable')),
            array('value' => 4, 'label'=>$helper->__('Disable and Display "Out of Stock"')),
            array('value' => 2, 'label'=>$helper->__('Allow Backorders')),
            array('value' => 3, 'label'=>$helper->__('Allow Backorders and Notify Customer'))
        );
    }
}