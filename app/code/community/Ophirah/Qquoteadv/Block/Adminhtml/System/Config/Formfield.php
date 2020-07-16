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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Formfield
 */
class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Formfield extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Checks if this functionality is supported in this version of Magento
     * (not available for magento CE <1.7 and <EE 1.13)
     *
     * @return bool|null
     */
    protected function versionCheck()
    {
        if(method_exists('Mage', 'getEdition')){
            $edition = Mage::getEdition();
            switch ($edition) {
                case "Community":
                    return version_compare(Mage::getVersion(), '1.7.0.0') <= 0 ? false : true;
                case "Enterprise":
                    return version_compare(Mage::getVersion(), '1.13.0.0') <= 0 ? false : true;
            }
        } else {
            //version is below v1.7.0.0
            return false;
        }

        return null;
    }
}
