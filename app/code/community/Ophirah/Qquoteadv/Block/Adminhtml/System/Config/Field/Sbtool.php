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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Field_Sbtool
 */
class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Field_Sbtool extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Render function for the supplierbiddingtool header in system>configuration>Cart2Quote>SuplierBidding
     * This header is shown when the supplierbiddingtool is not sintalled
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        //show different message when SBT is enabled
        if(Mage::helper('core')->isModuleEnabled('Ophirah_SupplierBiddingTool') || Mage::helper('core')->isModuleEnabled('Cart2quote_SupplierBiddingTool')){
            //sbt installed load html from installed module
            $layout = Mage::app()->getLayout();
            $html = $layout->getBlockSingleton('supplierbiddingtool/adminhtml_system_config_field_header')->render($element);
        } else {
            //no sbt installed
            $html = '<div class="box">
                        <p><b>Supplier Bidding Tool for Cart2Quote Provided by <a target="_blank" href="https://www.ophirah.nl">Ophirah | E-commerce Projects</a></b></p>
                        Automates the requesting of prices from suppliers. Provided by Cart2Quote
                        <br/>
                        If the Supplier Bidding Tool is enabled, you can send requests for quotation (RFQs) to suppliers. You can send requests for quotation for catalog products to suppliers based on predefined rules.
                        <br/>
                        After enabling this feature the button "Request Supplier Prices" is displayed on the Create Quote admin panel page. If you click this button, the suppliers who have the items in stock receive an invitation to bid.
                        <br/>
                        You can assign multiple products to multiple suppliers and multiple suppliers to a single product. When the supplier replies to your request for quotation, you receive a message by e-mail.
                        <br/>
                        <a target="_blank" href="https://www.cart2quote.com/supplier-bidding">Learn more and order the Supplier Bidding Tool!</a><br/>
                    </div>';
        }

        return $html;
    }
}
