<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Model_System_Config_Source_Followup
 * Used in creating options for Yes|No config value selection
 */
class Ophirah_Qquoteadv_Model_System_Config_Source_Followup
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 2, 'label'=>Mage::helper('qquoteadv')->__('Message Action')),
            array('value' => 1, 'label'=>Mage::helper('qquoteadv')->__('Today only')),
            array('value' => 0, 'label'=>Mage::helper('qquoteadv')->__('Today and Future')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('qquoteadv')->__('Today and Future'),
            1 => Mage::helper('qquoteadv')->__('Today only'),
            2 => Mage::helper('qquoteadv')->__('Message Action'),
        );
    }
}
