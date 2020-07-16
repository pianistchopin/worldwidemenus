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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tab_Field_Loadtemplate
 */
class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tab_Field_Loadtemplate extends Varien_Data_Form_Element_Abstract
{
    /**
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * Function that renders the load template section
     *
     * @return string
     */
    public function getElementHtml()
    {
        $button = '<button id="crm_loadtemplate" class="scalable save" style="" onclick="loadCrmTemplate();" type="adminhtml/widget_button" title="Load Template">';
        $label = "<span>" . Mage::helper('crmaddon')->__('Load Template') . "</span></button>";

        $html = $button;
        $html .= $label;
        $html .= $this->getAfterElementHtml();
        return $html;
    }

}
