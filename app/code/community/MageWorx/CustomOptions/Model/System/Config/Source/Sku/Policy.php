<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_Customoptions_Model_System_Config_Source_Sku_Policy {
    // $mode = 0 - with no Use Config, 1 - all, 2 - with no Grouped, 3 - only Use Config
    public function toOptionArray($mode = 0) {
        $helper = Mage::helper('mageworx_customoptions');
        $options = array(
            array('value' => 0, 'label' => $helper->__('Use Config')),
            array('value' => 1, 'label' => $helper->__('Standard')),
            array('value' => 2, 'label' => $helper->__('Independent')),
            array('value' => 3, 'label' => $helper->__('Grouped')),
            array('value' => 4, 'label' => $helper->__('Replacement')),
        );        
        if ($mode==0) unset($options[0]); // remove Use Config
        if ($mode==2) unset($options[count($options)-2]); // remove Grouped
        if ($mode==3) $options = array($options[0]);
        return $options;
    }

}