<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_Customoptions_Model_System_Config_Source_Sku_Apply {
    public function toOptionArray() {
        $helper = Mage::helper('mageworx_customoptions');
        $options = array(            
            array('value' => 1, 'label'=>$helper->__('Order Only')),
            array('value' => 2, 'label'=>$helper->__('Cart and Order')),
        );        
        return $options;
    }

}