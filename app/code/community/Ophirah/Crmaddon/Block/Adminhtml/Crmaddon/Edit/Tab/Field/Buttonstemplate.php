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
 * Class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tab_Field_Buttonstemplate
 */
class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tab_Field_Buttonstemplate extends Varien_Data_Form_Element_Abstract
{
    /**
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * Function that renders the manage template section
     *
     * @return string
     */
    public function getElementHtml()
    {
        $class1 = array("scalable disabled", "scalable save");
        $class2 = array("scalable disabled", "scalable delete");

        $isActive = Mage::helper('crmaddon')->tabIsActive();
        $state = ($isActive === true) ? 1 : 0;

        $button1 = '<button id="crm_savetemplate" class="' . $class1[$state] . '" style="" onclick="saveCrmTemplate();" type="adminhtml/widget_button" title="Save Template">';
        $label1 = "<span>" . Mage::helper('crmaddon')->__('Save Template') . "</span></button>";
        $button2 = '<button id="crm_newtemplate" class="' . $class1[1] . '" style="" onclick="newCrmTemplate();" type="adminhtml/widget_button" title="New Template">';
        $label2 = "<span>" . Mage::helper('crmaddon')->__('Save as New Template') . "</span></button>";
        $button3 = '<button id="crm_deletetemplate" class="' . $class2[$state] . '" style="" onclick="deleteCrmTemplate();" type="adminhtml/widget_button" title="Delete Template">';
        $label3 = "<span>" . Mage::helper('crmaddon')->__('Delete Template') . "</span></button>";

        $html = $button2 . $label2;
        $html .= $button1 . $label1;
        $html .= $button3 . $label3;
        $html .= $this->getAfterElementHtml();
        return $html;
    }

}
