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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Field_Manuals
 */
class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Field_Manuals extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Render function for the manuals field in the system settings
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';
        $html .= '<li><a href="https://cart2quote.zendesk.com/hc/en-us/articles/360029619651-Quick-start-guide-Cart2Quote-for-Magento-1" target="_blank">Cart2Quote - Quick Start Manual</a></li>';
        $html .= '<li><a href="https://cart2quote.zendesk.com/hc/en-us/articles/360028912391-Installation-manual-Cart2Quote" target="_blank">Cart2Quote - Installation manual</a></li>';
        $html .= '<li><a href="https://cart2quote.zendesk.com/hc/en-us/articles/360029306432-Cart2Quote-Installation-manual-Connect-Manager-" target="_blank">Cart2Quote - Installation manual (Connect Manager)</a></li>';
        $html .= '<li><a href="https://cart2quote.zendesk.com/hc/en-us/articles/360029305192-Cart2Quote-User-manual" target="_blank">Cart2Quote - User manual</a></li>';
        $html .= '<li><a href="https://cart2quote.zendesk.com/hc/en-us/articles/360029632651-API-manual-Cart2Quote-for-Magento-1" target="_blank">Cart2Quote - API manual</a></li>';

        if (Mage::getConfig()->getNode('modules/Ophirah_Not2Order')) {
            $html .= '<li><a href="https://cart2quote.zendesk.com/hc/en-us/articles/360029346572-Not2Order-Installation-manual" target="_blank">Not2Order - Installation manual</a></li>';
        }

        return $html;
    }
}
