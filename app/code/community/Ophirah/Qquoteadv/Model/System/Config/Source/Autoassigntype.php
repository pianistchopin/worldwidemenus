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
 * Class Ophirah_Qquoteadv_Model_System_Config_Source_Autoassigntype
 */
class Ophirah_Qquoteadv_Model_System_Config_Source_Autoassigntype
{
    const TYPE_NONE = 'none';
    const TYPE_ROTATION = 'rotation';
    const TYPE_ADMIN_LOGIN = 'admin_login';
    const TYPE_BOTH = 'both';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::TYPE_NONE, 'label' => Mage::helper('qquoteadv')->__('None')),
            array('value' => self::TYPE_ROTATION, 'label' => Mage::helper('qquoteadv')->__('Rotate admin role')),
            array('value' => self::TYPE_ADMIN_LOGIN, 'label' => Mage::helper('qquoteadv')->__('Logged in admin user')),
            array('value' => self::TYPE_BOTH, 'label' => Mage::helper('qquoteadv')->__('Logged in admin user with rotate fallback')),
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
            self::TYPE_NONE => Mage::helper('qquoteadv')->__('None'),
            self::TYPE_ROTATION => Mage::helper('qquoteadv')->__('Rotation'),
            self::TYPE_ADMIN_LOGIN => Mage::helper('qquoteadv')->__('Logged in admin user'),
            self::TYPE_BOTH => Mage::helper('qquoteadv')->__('Logged in admin user with rotation fallback'),
        );
    }
}
