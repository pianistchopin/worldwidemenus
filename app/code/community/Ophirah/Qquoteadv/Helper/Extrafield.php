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
 * Class Ophirah_Qquoteadv_Helper_Extrafield
 */
class Ophirah_Qquoteadv_Helper_Extrafield extends Mage_Core_Helper_Abstract
{
    const PATH_TO_EXTRA_FIELD_CONFIG = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_";
    const LABEL_TAG     = "_label";     //extrafield_1_label
    const TYPE_TAG      = "_type";      //extrafield_1_type
    const GROUP_TAG     = "_group";     //extrafield_1_group
    const DEFAULT_TYPE  = 'text';
    const REQUIRED      = 2;

    /**
     * Checks if extra fields are available
     * @return bool
     */
    public function extraFieldsAreAvailable()
    {
        $extraFields = (int)Mage::helper('qquoteadv')->getNumberOfExtraFields();
        for ($fieldNumber = 1; $fieldNumber <= $extraFields; $fieldNumber++) {
            if ($this->isExtraFieldSet($fieldNumber)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a specific extra field is set
     * @param $fieldId
     * @return bool
     */
    public function isExtraFieldSet($fieldId)
    {
        $config = Mage::getStoreConfig(self::PATH_TO_EXTRA_FIELD_CONFIG . $fieldId);
        if ($config && !is_array($config)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieves an label of a specific extra field
     * @param $fieldId
     * @return bool
     */
    public function getFieldLabel($fieldId)
    {
        if (Mage::getStoreConfig(self::PATH_TO_EXTRA_FIELD_CONFIG . $fieldId . self::LABEL_TAG)) {
            return Mage::getStoreConfig(self::PATH_TO_EXTRA_FIELD_CONFIG . $fieldId . self::LABEL_TAG);
        } else {
            return false;
        }
    }

    /**
     * Retrieves the customer groups for a specific extra field
     * @param $fieldId
     * @return bool
     */
    public function getFieldGroups($fieldId)
    {
        $groups = Mage::getStoreConfig(self::PATH_TO_EXTRA_FIELD_CONFIG . $fieldId . self::GROUP_TAG);
        if (isset($groups) && (!empty($groups) || $groups === "0")) {
            return $groups;
        } else {
            return false;
        }
    }

    /**
     * Retrieves a type of a specific extra field
     * @param $fieldId
     * @return bool
     */
    public function getFieldType($fieldId)
    {
        if (Mage::getStoreConfig(self::PATH_TO_EXTRA_FIELD_CONFIG . $fieldId . self::TYPE_TAG)) {
            return Mage::getStoreConfig(self::PATH_TO_EXTRA_FIELD_CONFIG . $fieldId . self::TYPE_TAG);
        } else {
            return self::DEFAULT_TYPE;
        }
    }

    /**
     * Checks if extra field is visible
     *
     * @param $fieldId
     * @param int $customerGroupId
     * @return bool
     */
    public function extraFieldIsVisible($fieldId, $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
    {
        $groups = $this->getFieldGroups($fieldId);
        if (isset($groups) && (!empty($groups) || $groups === "0")) {
            $groups = explode(",", $groups);
            if (!in_array($customerGroupId, $groups)) {
                return false;
            }
        }

        //default visible
        return true;
    }

    /**
     * Checks if a specific extra field is required
     * @param $fieldId
     * @return bool
     */
    public function isRequiredField($fieldId)
    {
        if (Mage::getStoreConfig(self::PATH_TO_EXTRA_FIELD_CONFIG . $fieldId) == self::REQUIRED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieves the field data of a specific extra field
     *
     * @param $fieldId
     * @param bool $error
     * @param $quote
     * @return string
     */
    public function getFieldData($fieldId, $error = true, $quote)
    {
        $fieldData = $quote->getData('extra_field_' . $fieldId);
        if ($error && is_null($fieldData)) {
            $fieldData = Mage::helper('qquoteadv')->__("None");
        } elseif (is_string($fieldData)) {
            $fieldData = Mage::helper('qquoteadv')->__(ucfirst($fieldData));
        }
        return $fieldData;
    }
}
