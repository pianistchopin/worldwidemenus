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
 * Class Ophirah_Qquoteadv_Block_Form_Extrafields
 */
class Ophirah_Qquoteadv_Block_Form_Extrafields extends Ophirah_Qquoteadv_Block_Qquoteaddress
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
     * Checks if extra field is visible
     * @param $fieldId
     * @return bool
     */
    public function extraFieldIsVisible($fieldId)
    {
        $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        if ($isLoggedIn) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        } else {
            $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        }

        return Mage::helper('qquoteadv/extrafield')->extraFieldIsVisible($fieldId, $customerGroupId);
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
     * Retrieves a type of a specific extra field
     * @param $fieldId
     * @return bool
     */
    public function getFieldType($fieldId)
    {
        return Mage::helper('qquoteadv/extrafield')->getFieldType($fieldId);
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
     * Retrieves the row class depending on the count
     * @param $fieldCount
     * @return string
     */
    public function getRowClass($fieldCount)
    {
        if ($fieldCount % 2 == 0) {
            return "p5";
        } else {
            return "left";
        }
    }

    /**
     * Retrieves the row class depending on the count
     * @param $fieldCount
     * @return string
     */
    public function getRowStart($fieldCount)
    {
        if ($fieldCount % 2 != 0) {
            return "<tr>";
        } else {
            return "";
        }
    }

    /**
     * Retrieves the row class depending on the count
     * @param $fieldCount
     * @return string
     */
    public function getRowEnd($fieldCount)
    {
        if ($fieldCount % 2 == 0) {
            return "</tr>";
        } else {
            return "";
        }
    }

    /**
     * Retrieves the field data of a specific extra field
     * @param $fieldId
     * @param bool $error
     * @return string
     */
    public function getFieldData($fieldId, $error = true)
    {
        $quote = $this->getParentBlock()->getQuoteData();
        return Mage::helper('qquoteadv/extrafield')->getFieldData($fieldId, $error, $quote);
    }
}
