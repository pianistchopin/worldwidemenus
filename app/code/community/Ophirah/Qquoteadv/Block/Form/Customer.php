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
 * Class Ophirah_Qquoteadv_Block_Form_Customer
 */
class Ophirah_Qquoteadv_Block_Form_Customer extends Ophirah_Qquoteadv_Block_Qquoteaddress
{
    /**
     * Construct
     *
     * @return mixed
     */
    public function _construct()
    {
        $this->setCustomer();
        parent::_construct();
    }

    /**
     * Returns the HTML that is required for a the given setting
     *
     * @param $setting
     * @return string
     */
    public function getFieldRequiredSpan($setting)
    {
        if ($setting == 2) {
            //return '<span class="required">*</span>';
            return '<span class="required"></span>';
        }

        return '';
    }

    /**
     * Returns the css class that is required for a the given setting
     *
     * @param $setting
     * @return string
     */
    public function getFieldRequiredClass($setting)
    {
        if ($setting == 2) {
            return 'required-entry';
        }

        return '';
    }

    /**
     * Check if quote reference is allowed
     *
     * @return bool
     */
    public function getAllowQuoteReference()
    {
        if ($this->getQuoteReference() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Getter for the setting 'qquoteadv_quote_form_builder/quote_details/quote_reference'
     *
     * @return mixed
     */
    public function getQuoteReference()
    {
        $setting = Mage::getStoreConfig('qquoteadv_quote_form_builder/quote_details/quote_reference');
        return $setting;
    }

    /**
     * Returns true if allowed and false if not.
     *
     * @return bool
     */
    public function getAllowedGlobalRemarkField()
    {
        $setting = Mage::getStoreConfig('qquoteadv_quote_form_builder/quote_form_remarks/disable_general_remark');
        return !$setting;
    }
}
