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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Quotedetails_Extrafields
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Quotedetails_Extrafields extends Ophirah_Qquoteadv_Block_Qquote_Abstract
{
    /**
     * Checks if extra fields are available
     * @return bool
     */
    public function extraFieldsAreAvailable()
    {
        return Mage::helper('qquoteadv/extrafield')->extraFieldsAreAvailable();
    }

    /**
     * Checks if a specific extra field is set
     * @param $fieldId
     * @return bool
     */
    public function isExtraFieldSet($fieldId)
    {
        return Mage::helper('qquoteadv/extrafield')->isExtraFieldSet($fieldId);
    }

    /**
     * Retrieves an label of a specific extra field
     * @param $fieldId
     * @return bool
     */
    public function getFieldLabel($fieldId)
    {
        return Mage::helper('qquoteadv/extrafield')->getFieldLabel($fieldId);
    }

    /**
     * Checks if a specific extra field is required
     * @param $fieldId
     * @return bool
     */
    public function isRequiredField($fieldId)
    {
        return Mage::helper('qquoteadv/extrafield')->isRequiredField($fieldId);
    }

    /**
     * Retrieves the field data of a specific extra field
     * @param $fieldId
     * @param bool $error
     * @return string
     */
    public function getFieldData($fieldId, $error = true)
    {
        $quote = $this->getParentBlock()->getQuoteData(false);
        return Mage::helper('qquoteadv/extrafield')->getFieldData($fieldId, $error, $quote);
    }
}
