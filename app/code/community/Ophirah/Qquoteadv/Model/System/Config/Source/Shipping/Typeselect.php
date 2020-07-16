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
 * Class Ophirah_Qquoteadv_Model_System_Config_Source_Shipping_Typeselect
 */
class Ophirah_Qquoteadv_Model_System_Config_Source_Shipping_Typeselect
{
    /**
     * @var
     */
    protected $_options;

    /**
     * Renderer for the options of the type select for the shipping setting
     *
     * @param bool|false $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $options = array(
            '' => 'No default shipping method',
            'I' => 'Price per Item',
            'O' => 'Fixed Price per Order'
        );

        foreach ($methods as $_ccode => $_carrier) {
            $_methodOptions = array();
            $_methods = $this->getShippingCodes($_carrier);
            if ($_methods) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array('value' => $_code, 'label' => $_method);
                }

                if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                    $_title = $_ccode;

                $options[] = array('value' => $_methodOptions, 'label' => $_title);
            }
        }

        return $options;
    }

    /**
     * Function that only returns a shipping method codes if the give carrier implements Mage_Shipping_Model_Carrier_Interface
     *
     * @param $shippingCarrier
     * @return bool|array
     */
    private function getShippingCodes($shippingCarrier)
    {
        if ($shippingCarrier instanceof Mage_Shipping_Model_Carrier_Interface) {
            return $shippingCarrier->getAllowedMethods();
        } else {
            if (is_object($shippingCarrier)) {
                Mage::log(
                    'Message: Shipping method "' . get_class($shippingCarrier)
                    . '" does not implement "Mage_Shipping_Model_Carrier_Interface"',
                    null,
                    'c2q.log',
                    true
                );
            } else {
                Mage::log(
                    'Message: Shipping method is not an object',
                    null,
                    'c2q.log',
                    true
                );
            }
        }

        return false;
    }
}
