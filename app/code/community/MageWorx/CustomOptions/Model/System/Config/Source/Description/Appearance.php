<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_System_Config_Source_Description_Appearance {
    public function toOptionArray() {
        $helper = Mage::helper('mageworx_customoptions');
        return array(
            array('value' => 1, 'label'=>$helper->__('Plain Text')),
            array('value' => 2, 'label'=>$helper->__('Tooltip'))
        );
    }

}