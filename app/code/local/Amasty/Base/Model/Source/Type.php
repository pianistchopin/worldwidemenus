<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_Source_Type extends Varien_Object
{
    const GENERAL = 'INFO';

    const SPECIAL_DEALS = 'PROMO';

    const AVAILABLE_UPDATE = 'INSTALLED_UPDATE';

    const UNSUBSCRIBE_ALL = 'UNSUBSCRIBE_ALL';

    const TIPS_TRICKS = 'TIPS_TRICKS';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('ambase');

        return array(
            array('value' => self::GENERAL, 'label' => $helper->__('General Info')),
            array('value' => self::SPECIAL_DEALS, 'label' => $helper->__('Special Deals')),
            array('value' => self::AVAILABLE_UPDATE, 'label' => $helper->__('Available Updates')),
            array('value' => self::TIPS_TRICKS, 'label' => $helper->__('Magento Tips & Tricks')),
            array('value' => self::UNSUBSCRIBE_ALL, 'label' => $helper->__('Unsubscribe from all'))
        );
    }
}
