<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_System_Config_Source_Assigned_Attributes {
    public function toOptionArray() {
        $helper = Mage::helper('mageworx_customoptions');
        $arr = array(
            array('value' => 0, 'label' => $helper->__('None')),
            array('value' => 1, 'label' => $helper->__('Price')),
            array('value' => 2, 'label' => $helper->__('Name'))
        );
        if ($helper->isCostEnabled()) $arr[] = array('value' => 3, 'label'=>$helper->__('Cost'));
        if ($helper->isWeightEnabled()) $arr[] = array('value' => 4, 'label'=>$helper->__('Weight'));
        if ($helper->isInventoryEnabled()) $arr[] = array('value' => 5, 'label'=>$helper->__('Qty'));
        
        return $arr;
    }
}