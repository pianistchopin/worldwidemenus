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
 * Class Ophirah_Qquoteadv_Helper_Legacy
 */
final class Ophirah_Qquoteadv_Helper_Legacy extends Mage_Core_Helper_Abstract
{
    const NEW_FIRST_NAME    = 'firstname';
    const NEW_LAST_NAME     = 'lastname';
    const NEW_STREET        = 'street';
    const NEW_COUNTRY_ID    = 'country_id';
    const NEW_POSTCODE      = 'postcode';
    const NEW_REGION_ID     = 'region_id';
    const NEW_REGION        = 'region';
    const NEW_CITY          = 'city';
    const NEW_TELEPHONE     = 'telephone';
    const NEW_FAX           = 'fax';
    const NEW_VAT           = 'vat_id';
    const NEW_EMAIL         = 'email';
    const NEW_COMPANY       = 'company';

    /**
     * Moves outdated post data to the address arrays
     *
     * @param $postData
     * @return array
     *
     * @deprecated since version 5.2.2
     */
    public function prepareOutdatedPostData($postData)
    {
        $newPostData = array();
        if (is_array($postData)) {
            $addressTypes = array(
                Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING,
                Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING
            );
            foreach ($addressTypes as $addressType) {
                $newPostData[$addressType] = array();
                foreach ($this->_getOldAddressMapping($addressType) as $newKey => $oldKey) {
                    $newPostData[$addressType] = $this->_setPostField($newKey, $oldKey, $newPostData[$addressType], $postData);
                }
            }
        }

        return $newPostData;
    }

    /**
     * Checks if the data has depricated array keys.
     * @param $customerData
     * @return bool
     */
    public function hasDeprecatedPostData($customerData)
    {
        $isDeprecated = false;

        // old form data
        $invalidArrayKeys = array(
            'shipping_postcode',
            'postcode',
            'telephone'
        );

        foreach ($invalidArrayKeys as $key) {
            if (array_key_exists($key, $customerData)) {
                $isDeprecated = true;
            }
        }

        return $isDeprecated;
    }

    /**
     * Sets old data to new array
     * @param $newField
     * @param $oldField
     * @param $newData
     * @param $oldData
     * @return mixed
     */
    protected function _setPostField($newField, $oldField, $newData, $oldData)
    {
        if (array_key_exists($oldField, $oldData)) {
            $newData[$newField] = $oldData[$oldField];
        }

        return $newData;
    }

    /**
     * Returns the key mapping, depending on type
     * @param $type
     * @return array
     */
    protected function _getOldAddressMapping($type)
    {
        switch ($type) {
            case Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING:
                return $this->_getOldBillingAddressMapping();
            case Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING:
                return $this->_getOldShippingAddressMapping();
            default:
                return array();
        }
    }

    /**
     * retrieves old address type mapping
     * @return array
     */
    protected function _getOldBillingAddressMapping()
    {
        return array(
            self::NEW_FIRST_NAME    => 'firstname',
            self::NEW_LAST_NAME     => 'lastname',
            self::NEW_STREET        => 'address0',
            self::NEW_POSTCODE      => 'postcode',
            self::NEW_CITY          => 'city',
            self::NEW_COUNTRY_ID    => 'country_id',
            self::NEW_REGION        => 'region',
            self::NEW_REGION_ID     => 'region_id',
            self::NEW_TELEPHONE     => 'telephone',
            self::NEW_FAX           => 'fax',
            self::NEW_VAT           => 'vat_id',
            self::NEW_EMAIL         => 'email',
            self::NEW_COMPANY       => 'company'
        );
    }

    /**
     * retrieves old address type mapping
     * @return array
     */
    protected function _getOldShippingAddressMapping()
    {
        return array(
            self::NEW_FIRST_NAME    => 'firstname',
            self::NEW_LAST_NAME     => 'lastname',
            self::NEW_STREET        => 'shipping_address0',
            self::NEW_POSTCODE      => 'shipping_postcode',
            self::NEW_CITY          => 'shipping_city',
            self::NEW_COUNTRY_ID    => 'shipping_country_id',
            self::NEW_REGION        => 'shipping_region',
            self::NEW_REGION_ID     => 'shipping_region_id',
            self::NEW_TELEPHONE     => 'telephone',
            self::NEW_FAX           => 'fax',
            self::NEW_VAT           => 'vat_id',
            self::NEW_EMAIL         => 'email',
            self::NEW_COMPANY       => 'company'
        );
    }
}
