<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_Source_Frequency extends Varien_Object
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('ambase');

        return array(
            array('value' => 2, 'label' => $helper->__('2 days')),
            array('value' => 5, 'label' => $helper->__('5 days')),
            array('value' => 10, 'label' => $helper->__('10 days')),
            array('value' => 15, 'label' => $helper->__('15 days')),
            array('value' => 30, 'label' => $helper->__('30 days')),
        );
    }

}
