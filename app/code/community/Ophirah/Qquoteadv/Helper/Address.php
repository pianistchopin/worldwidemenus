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
 * Class Ophirah_Qquoteadv_Helper_Address
 */
class Ophirah_Qquoteadv_Helper_Address extends Mage_Core_Helper_Abstract
{
    CONST ADDRESS_TYPE_BILLING = 'billing';
    CONST ADDRESS_TYPE_SHIPPING = 'shipping';
    CONST ADDRESS_PROCESS_MODE_COPY_BILLING = 'MODE_COPY_BILLING';
    CONST ADDRESS_PROCESS_MODE_COPY_SHIPPING = 'MODE_COPY_SHIPPING';
    CONST ADDRESS_PROCESS_MODE_SEPARATED = 'MODE_SEPARATED';
    CONST SETTING_NO = 0;
    CONST SETTING_YES = 1;
    CONST SETTING_YES_AND_REQUIRED = 2;

    /**
     * Array with address fields that can
     * be filled out and stored with the quote
     *
     * @return array
     */
    public function addressFieldsArray()
    {
        return array(
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'company',
            'country_id',
            'region',
            'region_id',
            'city',
            'address',
            'street',
            'postcode',
            'telephone',
            'fax',
            'vat_id',
            'vat_is_valid',
            'vat_request_id',
            'vat_request_date',
            'vat_request_success',
            'customer_group_id',
            'customer_address_id'
        );
    }

    /**
     * Addresstypes
     *
     * @return array
     */
    public function getAddressTypes()
    {
        return array(self::ADDRESS_TYPE_BILLING, self::ADDRESS_TYPE_SHIPPING);
    }

    /**
     * Adding Quote address to customer
     *
     * @param int $customerId
     * @param array $addressData
     * @param null|array $vars
     * @return Mage_Customer_Model_Address
     */
    public function addCustomerAddress($customerId, $addressData, $vars = null)
    {
        if ($vars == null) {
            $vars['saveAddressBook'] = 1;
            $vars['defaultShipping'] = 0;
            $vars['defaultBilling'] = 0;
        }

        $customAddress = Mage::getModel('customer/address');
        $customAddress->setData($addressData)
            ->setCustomerId($customerId)
            ->setSaveInAddressBook($vars['saveAddressBook'])
            ->setIsDefaultShipping($vars['defaultShipping'])
            ->setIsDefaultBilling($vars['defaultBilling']);

        try {
            $customAddress->save();

            return $customAddress;
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }
    }


    /**
     * Update customer address
     *
     * @param int $customerId
     * @param int $customerAddressId
     * @param array $addressData
     */
    public function updateCustomerAddress($customerId, $customerAddressId, $addressData)
    {
        $customAddress = Mage::getModel('customer/address');
        $customAddress->setData($addressData)
            ->setCustomerId($customerId)
            ->setId($customerAddressId);
        try {
            $customAddress->save();
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }
    }

    /** Adding Quote address to customer
     *
     * @param Mage_Customer_Model_Address $address
     * @internal param $int /Mage_Customer_Model_Customer
     * @internal param $array // Array with address information
     * @internal param $array // Variables for default settings
     */
    public function createCustomerAddress(Mage_Customer_Model_Address $address)
    {
        try {
            $address->save();
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }
    }

    /**
     * Add new address in database
     * table: 'quoteadv_quote_address'
     *
     * @param integer $quoteId
     * @param array $addressData
     * @param null $check
     * @return bool
     */
    public function addAddress($quoteId, $addressData, $check = null)
    {
        if (!(int)$quoteId) {
            return false;
        }

        $addressTypes = $this->getAddressTypes();
        $sameAsBillling = '0';
        $prevData = null;
        foreach ($addressTypes as $type) {
            if (isset($addressData[$type])) {
                $typeData = $addressData[$type];
                if (is_array($typeData)) {
                    $addData = $typeData;
                } elseif (is_object($typeData)) {
                    $addData = $typeData->getData();
                }
            }

            // add Billing before Shipping
            if ($prevData == $addData && $addData != null) {
                $sameAsBillling = '1';
            }

            $newAddress = Mage::getModel('qquoteadv/quoteaddress');
            if (isset($addData)) {
                $newAddress->addData($addData);
                unset($addData);
            }

            if ($type == self::ADDRESS_TYPE_SHIPPING && $sameAsBillling == '1') {
                $newAddress->setData('same_as_billing', $sameAsBillling);
            }

            $newAddress->setData('quote_id', $quoteId);
            $newAddress->setData('address_type', $type);

            try {
                $newAddress->save();
            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }

            $prevData = $addData;
        }

        return null;
    }

    /**
     * Update address associated with the quote
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     */
    public function updateAddress(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote)
    {
        $quoteAddresses = $this->getAddresses($quote);
        $addressCollection = $this->getAddressCollection($quote->getData('quote_id'));

        if ($addressCollection){
            foreach ($addressCollection as $address){
                $type = 'shippingAddress';
                $addressType = self::ADDRESS_TYPE_SHIPPING;
                if ($address->getData('address_type') == self::ADDRESS_TYPE_BILLING) {
                    $type = 'billingAddress';
                    $addressType = self::ADDRESS_TYPE_BILLING;
                }
                if (isset($quoteAddresses[$type])) {
                    $address->addData($quote->getData());

                    //fix shipping code in method
                    $shippingCode = $quote->getShippingCode();
                    if($shippingCode){
                        $address->setShippingMethod($shippingCode);
                    }

                    if($address->getShippingMethod()){
                        $address->setData('collect_shipping_rates', '1');
                    }

                    $address->addData($quoteAddresses[$type]);

                    // Make sure the address_type remains
                    $address->setData('address_type', $addressType);
                    if (!$address->getData('same_as_billing')) {
                        $address->setData('same_as_billing', '0');
                    }

                    $address->save();
                }
            }
        }
    }

    /**
     * Get addresses associated with the
     * quote in an array
     *
     * @param integer $quoteId
     * @return boolean / array
     */
    public function getAddressCollectionArray($quoteId)
    {
        $return = false;
        if (!(int)$quoteId) {
            return $return;
        }

        // collect addresses from table
        $DBaddresses = $this->getAddressCollection($quoteId);
        if ($DBaddresses) {
            foreach ($DBaddresses as $DBaddress) {
                if ($DBaddress) {
                    $return[$DBaddress->getData('address_type')] = $DBaddress;
                }
            }
        }

        return $return;
    }

    /**
     * Retrieve address collection
     * from database
     *
     * @param integer $quoteId
     * @return boolean / Ophirah_Qquoteadv_Model_Mysql4_Quoteaddress_Collection
     */
    public function getAddressCollection($quoteId)
    {
        if ((int)$quoteId) {
            $return = Mage::getModel('qquoteadv/quoteaddress')
                ->getCollection()
                ->addFieldToFilter('quote_id', array('eq' => $quoteId))
                ->load();

            if (count($return) > 0) {
                return $return;
            } else {
                // For older quotes try building address first
                $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                $this->buildQuoteAdresses($quote, false);

                $return = Mage::getModel('qquoteadv/quoteaddress')
                    ->getCollection()
                    ->addFieldToFilter('quote_id', array('eq' => $quoteId))
                    ->load();

                if ($return) {
                    return $return;
                }
            }
        }

        return false;
    }

    /**
     * Collect Mage_Sales_Model_Quote_Address from
     * Ophirah_Qquoteadv_Model_Qqadvcustomer quote addresses
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @param bool $collect
     * @param bool $saveAdress
     * @return array
     * @throws \Exception
     */
    public function buildQuoteAdresses(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote, $collect = true, $saveAdress = true)
    {
        $customerId = $quote->getData('customer_id');
        $storeId = $quote->getData('store_id');
        $quoteCollection = array();
        $return = array();
        // extract address info
        $quoteAddresses = $this->getAddresses($quote);

        //only load collection when we need it
        if ($collect === true) {
            $quoteCollection = $this->getAddressCollectionArray($quote->getData('quote_id'));
        }

        if (isset($quoteCollection[self::ADDRESS_TYPE_BILLING])) {
            $billingAddress = $quoteCollection[self::ADDRESS_TYPE_BILLING];
            // update 'updated at'
            $billingAddress->setData('updated_at', $quote->getData('updated_at'));
            // set 'address' same as 'street'
            // TODO: remove all 'address' from code, we should name it 'street'
            $billingAddress->setData('address', $billingAddress->getData('street'));
        } else {
            // build billingaddres
            /** @var Ophirah_Qquoteadv_Model_Quoteaddress */
            $billingAddress = Mage::getModel('qquoteadv/quoteaddress');
            $billingAddress->setData($quote->getData());
            $addressData = $this->getQuoteAddress($customerId, $quoteAddresses['billingAddress'], $storeId, self::ADDRESS_TYPE_BILLING);
            $billingAddress->addData($addressData->getData());
        }

        if ($saveAdress) {
            $billingAddress->save();
        }

        $return['billingAddress'] = $billingAddress;

        if (isset($quoteCollection[self::ADDRESS_TYPE_SHIPPING])) {
            $shippingAddress = $quoteCollection[self::ADDRESS_TYPE_SHIPPING];
            // update 'updated at'
            $shippingAddress->setData('updated_at', $quote->getData('updated_at'));
            // set 'address' same as 'street'
            // TODO: remove all 'address' from code, we should name it 'street'
            $shippingAddress->setData('address', $shippingAddress->getData('street'));
            // Create old quote 'shipping_data'
            $shippingAddress = $this->convertToShipping($shippingAddress);
        } else {
            // build shippingaddres
            /** @var Ophirah_Qquoteadv_Model_Quoteaddress */
            $shippingAddress = Mage::getModel('qquoteadv/quoteaddress');
            $shippingAddress->setData($quote->getData());
            $addressData = $this->getQuoteAddress($customerId, $quoteAddresses['shippingAddress'], $storeId, self::ADDRESS_TYPE_SHIPPING);
            $shippingAddress->addData($addressData->getData());
        }

        if ($saveAdress) {
            $shippingAddress->save();
        }

        $return['shippingAddress'] = $shippingAddress;

        return $return;

    }

    /**
     * Creates an quoteadv_quote_address by using Mage_Customer_Model_Address.
     *
     * @param $quote
     * @param \Mage_Customer_Model_Address $address
     * @param $type
     * @return \Mage_Customer_Model_Address|null
     */
    public function createQuoteAddress($quote, Mage_Customer_Model_Address $address, $type)
    {
        if ($type == self::ADDRESS_TYPE_SHIPPING || $type == self::ADDRESS_TYPE_BILLING) {
            $quoteAddress = Mage::getModel('qquoteadv/address')->importCustomerAddress($address);

            if (!$quoteAddress->getCustomerId()) {
                $quoteAddress->setCustomerId($address->getCustomerId());
            }

            $quoteAddress
                ->setQuoteId($quote->getId())
                ->setAddressType($type)
                ->setUpdatedAt($quote->getUpdatedAt())
                ->setCreatedAt($quote->getCreatedAt())
                ->save();

            return $quoteAddress;
        }

        return null;
    }

    /**
     * Delete existing quote address
     *
     * @param  Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     */
    public function deletePreviousQuoteAddress($quote)
    {
        $addressCollection = $this->getAddressCollection($quote->getData('quote_id'));

        if ($addressCollection) {
            foreach ($addressCollection as $address) {
                $address->delete();
            }
        }
    }

    /**
     * Update 'shipping_*' data from
     * quote with shipping data from database
     * Needed when converting back.
     * 'shipping_' data gets converted to default address data.
     *
     * @param Ophirah_Qquoteadv_Helper_Address
     * @return Ophirah_Qquoteadv_Helper_Address
     */
    public function convertToShipping($shippingAddress)
    {
        $addressData = $this->addressFieldsArray();

        foreach ($addressData as $field) {
            $shippingAddress->setData('shipping_' . $field, $shippingAddress->getData($field));
        }

        return $shippingAddress;
    }

    /**
     * Builds array with seperated
     * shipping and billing address
     *
     * @param   Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @return  array
     */
    public function getAddresses(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote)
    {
        $returnData = ($quote->getData('address_type')) ? $quote->getData('address_type') : 'all';
        $addressData = $this->addressFieldsArray();

        foreach ($addressData as $data) {
            $shippingData[$data] = $quote->getData('shipping_' . $data);
            $billingData[$data] = $quote->getData($data);
        }

        // set address types
        $billingData['address_type'] = self::ADDRESS_TYPE_BILLING;
        $shippingData['address_type'] = self::ADDRESS_TYPE_SHIPPING;

        // Fix naming issue
        // set street data
        if (isset($billingData['address'])) {
            $billingData['street'] = $billingData['address'];
        }
        if (isset($shippingData['address'])) {
            $shippingData['street'] = $shippingData['address'];
        }

        if ($returnData == self::ADDRESS_TYPE_SHIPPING || $returnData == 'all') {
            $return['shippingAddress'] = $shippingData;
        }
        if ($returnData == self::ADDRESS_TYPE_BILLING || $returnData == 'all') {
            $return['billingAddress'] = $billingData;
        }

        return $return;
    }

    /**
     * Creates a Mage_Sales_Model_Quote_Address object
     * from the address array
     *
     * @param   Object /int/string       $customer        // instanceof Mage_Customer_Model_Customer
     * @param   array $quoteAddress
     * @param   int $storeId
     * @param   string $addressType
     *
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function getQuoteAddress($customer, $quoteAddress, $storeId, $addressType)
    {
        try {
            if (!is_object($customer)) {
                if (!is_array($customer)) {
                    $customerId = (int)$customer;
                }
            } else {
                $customerId = $customer->getId();
            }
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }

        $addressArray = $this->addressFieldsArray();
        /* @var Mage_Sales_Model_Quote_Address */
        $returnAddress = Mage::getModel('sales/quote_address')
            ->setStoreId($storeId)
            ->setAddressType($addressType)
            ->setCustomerId($customerId)
            ->setStreet($quoteAddress['address']);

        //Add other data
        foreach ($addressArray as $field) {
            if ($field != 'address') {
                $returnAddress->setData($field, $quoteAddress[$field]);
            }
        }

        return $returnAddress;
    }

    /**
     * Addres params to fill out
     *
     * @return  array       // Address parameters
     */
    public function getAddressParams()
    {
        // Address information
        $addressParams['addressFields'] = array(
            'address',
            'postcode',
            'city',
            'country_id',
            'region_id',
            'region'
        );

        // Customer information
        $addressParams['customerFields'] = array(
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'telephone',
            'company',
            'email',
            'fax',
            'vat_id'
        );

        return $addressParams;
    }

    /**
     * Retrieves the required form fields for the address.
     *
     * @return array
     */
    public function getRequiredAddressFields()
    {
        $requiredFields = array(
            'firstname',
            'lastname',
            'street',
            'postcode',
            'city',
            'country_id'
        );

        if ($this->getStateSettings() == self::SETTING_YES_AND_REQUIRED) {
            $requiredFields[] = array(
                'region_id',
                'region'
            );
        }

        if ($this->getCompanySettings() == self::SETTING_YES_AND_REQUIRED) {
            $requiredFields[] = 'company';
        }

        if ($this->getPhoneSettings() == self::SETTING_YES_AND_REQUIRED) {
            $requiredFields[] = 'telephone';
        }

        if ($this->getVatTaxSettings() == self::SETTING_YES_AND_REQUIRED) {
            $requiredFields[] = 'vat_id';
        }

        if ($this->getGenderSettings() == self::SETTING_YES_AND_REQUIRED) {
            $requiredFields[] = 'gender';
        }

        if ($this->getPrefixSettings() == self::SETTING_YES_AND_REQUIRED) {
            $requiredFields[] = 'prefix';
        }

        if ($this->getSuffixSettings() == self::SETTING_YES_AND_REQUIRED) {
            $requiredFields[] = 'suffix';
        }

        if ($this->getMiddlenameSettings() == self::SETTING_YES_AND_REQUIRED) {
            $requiredFields[] = 'middlename';
        }

        return $requiredFields;
    }

    /**
     * Copy address information between
     *  billing and shipping if "are the same"
     *  is selected
     *
     * @param $paramsAddress array  // Addres Params from post
     * @return array                // complete address info
     */
    public function buildAddress($paramsAddress)
    {
        $addressParams = $this->getAddressParams();

        $emptyBillField = false;
        $emptyShipField = false;
        $regionIsSet = false;
        $regionShipIsSet = false;
        $regionBillIsSet = false;

        // Shipping is Billing
        if (isset($paramsAddress['shipIsBill'])) {
            foreach ($addressParams['customerFields'] as $field) {
                $value = (isset($paramsAddress[$field])) ? $paramsAddress[$field] : '';
                $paramsAddress['shipping_' . $field] = $value;
                $paramsAddress['shipping'][$field] = $value;
            }
            foreach ($addressParams['addressFields'] as $field) {
                $value = (isset($paramsAddress[$field])) ? $paramsAddress[$field] : '';
                if ($field == 'region' || $field == 'region_id') {
                    if ($value != '') {
                        $regionIsSet = true;
                    }
                } elseif ($value == '') {
                    $emptyBillField = true;
                    $emptyShipField = true;
                }
                $fieldAlt = ($field == 'address') ? 'street' : $field;
                $paramsAddress['shipping_' . $field] = $value;
                $paramsAddress['shipping'][$fieldAlt] = $value;
            }
            $paramsAddress['billing'] = $paramsAddress['shipping'];

            // Billing is Shipping
        } elseif (isset($paramsAddress['billIsShip'])) {

            foreach ($addressParams['customerFields'] as $field) {
                $value = (isset($paramsAddress[$field])) ? $paramsAddress[$field] : '';
                $paramsAddress['billing'][$field] = $value;
                $paramsAddress['shipping_' . $field] = $value;
            }
            foreach ($addressParams['addressFields'] as $field) {
                $value = (isset($paramsAddress['shipping_' . $field])) ? $paramsAddress['shipping_' . $field] : '';
                if ($field == 'region' || $field == 'region_id') {
                    if ($value != '') {
                        $regionIsSet = true;
                    }
                } elseif ($value == '') {
                    $emptyBillField = true;
                    $emptyShipField = true;
                }
                $fieldAlt = ($field == 'address') ? 'street' : $field;
                $paramsAddress[$field] = $value;
                $paramsAddress['billing'][$fieldAlt] = $paramsAddress[$field];
            }
            $paramsAddress['shipping'] = $paramsAddress['billing'];

            // Both addresses are given or are empty
        } else {

            foreach ($addressParams['customerFields'] as $field) {
                $value = (isset($paramsAddress[$field])) ? $paramsAddress[$field] : '';
                $paramsAddress['shipping_' . $field] = $value;
                $paramsAddress['billing'][$field] = $value;
                $paramsAddress['shipping'][$field] = $value;
            }
            foreach ($addressParams['addressFields'] as $field) {
                $valueBill = (isset($paramsAddress[$field])) ? $paramsAddress[$field] : '';
                $valueShip = (isset($paramsAddress['shipping_' . $field])) ? $paramsAddress['shipping_' . $field] : '';
                if ($field == 'region' || $field == 'region_id') {
                    if ($valueBill != '') {
                        $regionBillIsSet = true;
                    }
                    if ($valueShip != '') {
                        $regionShipIsSet = true;
                    }
                } else {
                    if ($valueBill == '') {
                        $emptyBillField = true;
                    }
                    if ($valueShip == '') {
                        $emptyShipField = true;
                    }
                }
                $fieldAlt = ($field == 'address') ? 'street' : $field;
                $paramsAddress['billing'][$fieldAlt] = $valueBill;
                $paramsAddress['shipping'][$fieldAlt] = $valueShip;
            }

            if ($regionBillIsSet === true && $regionShipIsSet === true) {
                $regionIsSet = true;
            }

        }

        // remove invalid adresses
        if ($emptyBillField === true || $regionIsSet === false) {
            $paramsAddress['billing'] = array();
        }
        if ($emptyShipField === true || $regionIsSet === false) {
            $paramsAddress['shipping'] = array();
        }

        return $paramsAddress;
    }

    /**  Fill address with provided information
     *
     * @param $addressInfo
     * @param $paramsAddress
     * @param      array // address info to fill out
     * @return array // with address info
     *
     */
    public function fillAddress($addressInfo, $paramsAddress, $prefix = null)
    {
        $addressParams = $this->getAddressParams();

        foreach ($addressParams as $addressParam) {
            foreach ($addressParam as $field) {
                if ($field != "email") {
                    $fieldAlt = ($field == 'address') ? 'street' : $field;
                    if (isset($addressInfo[$fieldAlt])) {
                        $paramsAddress[$prefix . $field] = $addressInfo[$fieldAlt];
                    }
                }
            }
        }

        return $paramsAddress;
    }

    /**
     * Retrieve quote address info by
     * provided address type
     *
     * @param integer $quoteId
     * @param string $type
     * @return boolean | Ophirah_Qquoteadv_Model_Quoteaddress
     */
    public function getAddressInfoByType($quoteId, $type)
    {
        $collection = $this->getAddressCollection($quoteId);
        if ($collection) {
            foreach ($collection as $address) {
                if ($address->getData('address_type') == $type) {
                    return $address;
                }
            }
        }

        return false;
    }

    /**
     * Combines multiple lines of addresses to one address and dividing the lines with a new line: /n
     * @param $paramsAddress
     * @return array
     */
    public function combineMultipleLineAddress($paramsAddress){
        $numberOfLines = Mage::getStoreConfig('customer/address/street_lines');
        $address = '';
        $shipping_address = '';

        for($line = 0; $line < $numberOfLines; $line++){
            if(array_key_exists('address'.$line, $paramsAddress)){
                $address .= $paramsAddress['address'.$line].PHP_EOL;
            }
            if(array_key_exists('shipping_address'.$line, $paramsAddress)){
                $shipping_address .= $paramsAddress['shipping_address'.$line].PHP_EOL;
            }
        }

        $paramsAddress['address'] = $address;
        $paramsAddress['shipping_address'] = $shipping_address;

        return $paramsAddress;
    }

    /**
     * Combines multiple lines of addresses to one address and dividing the lines with a new line: /n
     * Todo: use magento street combine function
     * @param $address
     * @return array
     */
    public function combineStreetByAddress(Mage_Customer_Model_Address $address)
    {
        $numberOfLines = Mage::getStoreConfig('customer/address/street_lines');
        $street = '';
        for ($line = 0; $line < $numberOfLines; $line++) {
            if ($address->hasData('street' . $line)) {
                $street .= $address->getData('street' . $line) . PHP_EOL;
            } else {
                if ($address->hasData('street')) {
                    $streetArray = $address->getData('street');
                    if (is_array($streetArray) && isset($streetArray[$line]) && !empty($streetArray[$line])) {
                        $street .= $streetArray[$line] . PHP_EOL;
                    }
                }
            }
        }

        if (!empty($street)) {
            $address->setStreet($street);
        }

        return $address;
    }

    /**
     * Splits the address based on the new line: /n
     * @param $address
     * @return array
     */
    public function splitMultipleLineAddress($address){
        $arrayOfAddressLines = explode(PHP_EOL, $address);
        return $arrayOfAddressLines;
    }

    /**
     * Function that checks if the quoteadv address and magento address are the same
     *
     * @param $mageAddress
     * @param $quoteadvAddress
     * @return bool
     */
    public function isMageQuoteAddressTheSame($mageAddress, $quoteadvAddress)
    {
        $fields = array(
            'company',
            'street',
            'city',
            'postcode',
            'country_id'
        );
        $fieldsCount = count($fields);

        $found = 0;
        foreach ($fields as $field) {
            if ($mageAddress->getData($field) != $quoteadvAddress->getData($field)) {
                break;
            } else {
                $found++;
            }

            if ($found == $fieldsCount) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates a Mage_Sales_Model_Quote_Address object of the address.
     * @param array $address
     * @return Mage_Sales_Model_Quote_Address
     */
    public function toCustomerAddressObject(array $address){
        $newAddress = Mage::getModel('customer/address');

        //check if customer already exists based on give email
        if(isset($address['email'])){
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            $customer->loadByEmail($address['email']);
            if($customer->getId()){
                $newAddress->setCustomerId($customer->getId());
            }
        }

        $newAddress->addData($address);
        return $newAddress;
    }

    /**
     * Sets region by region id
     * @param Mage_Customer_Model_Address|Varien_Object $address
     * @return Varien_Object
     */
    public function setRegion(Mage_Customer_Model_Address $address){
        if ($address->hasData('region_id') && $address->getData('region_id') != "") {
            $_regionCollection = Mage::getModel('directory/region')->load($address->getRegionId());
            if ($_regionCollection) {
                $address->setRegion($_regionCollection->getName());
            }
        }

        return $address;
    }

    /**
     * Get the process mode based on the post values:
     * - specifyShippingAddress
     * - billIsShip
     * - shipIsBill
     *
     * @param array|Varien_Object $customerData
     * @return string
     */
    public function getAddressProcessMode(Varien_Object $customerData)
    {
        //new design and theme
        if ($customerData->hasData('specifyShippingAddress') || $customerData->hasData('specifyBillingAddress')){
            //assume new design
            if ($this->getBoxesStatus() == 'All Boxes'){
                if ($customerData->getData('specifyShippingAddress') == "0") {
                    //when shipping address is not required, asume that billing is
                    return self::ADDRESS_PROCESS_MODE_COPY_BILLING;
                }

                if ($customerData->getData('specifyBillingAddress') == "0") {
                    //when billing address is not required, asume that shipping is
                    return self::ADDRESS_PROCESS_MODE_COPY_SHIPPING;
                }

                if ($customerData->getData('specifyShippingAddress') == "1") {
                    //when shipping address is required
                    return self::ADDRESS_PROCESS_MODE_COPY_SHIPPING;
                }

                return self::ADDRESS_PROCESS_MODE_SEPARATED;
            } else {
                if($this->getBoxesStatus() == 'shipping'){
                    if ($customerData->getData('specifyBillingAddress') == "1") {
                        return self::ADDRESS_PROCESS_MODE_SEPARATED;
                    }
                    return self::ADDRESS_PROCESS_MODE_COPY_SHIPPING;
                }

                if($this->getBoxesStatus() == 'billing'){
                    if ($customerData->getData('specifyShippingAddress') == "1") {
                        return self::ADDRESS_PROCESS_MODE_SEPARATED;
                    }
                    return self::ADDRESS_PROCESS_MODE_COPY_BILLING;
                }
            }
        }

        //fallback for theme integration and older themes
        if ($customerData->hasData('specifyShippingAddress') || $customerData->hasData('billIsShip')) {
            if ($customerData->getData('specifyShippingAddress') == "1" || $customerData->getData('billIsShip') == "on") {
                return self::ADDRESS_PROCESS_MODE_COPY_SHIPPING;
            }
        } elseif ($customerData->hasData('specifyBillingAddress') || $customerData->hasData('shipIsBill')) {
            if ($customerData->getData('specifyBillingAddress') == "1" || $customerData->getData('shipIsBill') == "on") {
                return self::ADDRESS_PROCESS_MODE_COPY_BILLING;
            }
        }

        //always fall back to separated mode
        return self::ADDRESS_PROCESS_MODE_SEPARATED;
    }

    /**
     * @param $mode
     * @param array $addresses
     * @return bool
     */
    public function processAddress($mode, array $addresses){
        if(array_key_exists(self::ADDRESS_TYPE_BILLING, $addresses) && array_key_exists(self::ADDRESS_TYPE_SHIPPING, $addresses)) {
            switch ($mode) {
                case self::ADDRESS_PROCESS_MODE_COPY_SHIPPING:
                    $addresses[self::ADDRESS_TYPE_BILLING] = $addresses[self::ADDRESS_TYPE_SHIPPING];
                    $addresses[self::ADDRESS_TYPE_BILLING]->setAddressType(self::ADDRESS_TYPE_BILLING);
                    break;
                case self::ADDRESS_PROCESS_MODE_COPY_BILLING:
                    $addresses[self::ADDRESS_TYPE_SHIPPING] = $addresses[self::ADDRESS_TYPE_BILLING];
                    $addresses[self::ADDRESS_TYPE_SHIPPING]->setAddressType(self::ADDRESS_TYPE_SHIPPING);
                    break;
                case self::ADDRESS_PROCESS_MODE_SEPARATED:
                    // Do nothing
                    break;
                default:
                    // Mode undefined
                    break;
            }
        }

        return $addresses;
    }

    /**
     * Checks if the billing and shipping address is valid.
     * @param array $addresses
     * @return bool
     */
    public function validateAddress(array $addresses)
    {
        $success = true;
        $errorMessage = '';
        if (array_key_exists(self::ADDRESS_TYPE_BILLING, $addresses)
            && array_key_exists(self::ADDRESS_TYPE_SHIPPING, $addresses)
        ) {
            $requiredFormFields = $this->getRequiredAddressFields();

            foreach ($addresses as $address) {
                if ($address instanceof Mage_Customer_Model_Address) {
                    foreach ($requiredFormFields as $requiredField) {
                        if (is_array($requiredField)) {
                            $found = false;
                            foreach ($requiredField as $requiredFieldOption) {
                                if (Zend_Validate::is($address->getData($requiredFieldOption), 'NotEmpty')) {
                                    $found = true;
                                    break;
                                }
                            }

                            if (!$found) {
                                $success = false;
                                $errorMessage = sprintf("Address field %s is empty, address cannot be validated", $requiredFieldOption);
                                break 2;
                            }
                        } else {
                            if (!Zend_Validate::is($address->getData($requiredField), 'NotEmpty')) {
                                $success = false;
                                $errorMessage = sprintf("Address field %s is empty, address cannot be validated", $requiredField);
                                break 2;
                            }
                        }
                    }
                } else {
                    $success = false;
                    $errorMessage = "Address is no instance of Mage_Customer_Model_Address, cannot be validated";
                    break;
                }
            }
        } else {
            $errorMessage = "Array key \'billing\' or \'shipping\' is missing, address cannot be validated";
            $success = false;
        }

        Mage::log($errorMessage, null, 'c2q_exception.log', true);
        return $success;
    }

    /**
     * Checks if an address exists
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Customer_Model_Address $address
     * @return bool
     */
    public function addressExists(Mage_Customer_Model_Customer $customer, Mage_Customer_Model_Address $address)
    {
        $same = false;
        foreach($customer->getAddresses() as $existingAddress){
            if(strcmp($this->serializeAddress($address), $this->serializeAddress($existingAddress)) == 0){
                $same = true;
                $address->setId($existingAddress->getId());
                break;
            }
        }

        return $same;
    }

    /**
     * Sets the array of address attributes that should be checked for the address comparison
     *
     * @param $address
     * @return string
     *
     */
    private function serializeAddress($address)  {
        return serialize(
            array(
                'firstname' => $address->getFirstname(),
                'lastname'  => $address->getLastname(),
                'street'    => $address->getStreet(),
                'city'      => $address->getCity(),
                'postcode'  => $address->getPostcode(),
                'country'   => $address->getCountry()
            )
        );
    }

    /**
     * Check if customer changed address in the checkout from quoted address
     *
     * @param $shippingAddress
     * @return bool
     */
    public function sameShippingAddress($shippingAddress)
    {
        $same = false;
        $quoteId = Mage::getSingleton('core/session')->proposal_quote_id;
        if ($quoteId && $shippingAddress) {
            $quoteCustomer = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
            $shippingAddressQuote = $quoteCustomer->getShippingAddress();
            if (strcmp($this->serializeAddress($shippingAddress), $this->serializeAddress($shippingAddressQuote)) == 0) {
                $same = true;
            }
        }

        return $same;
    }

    /**
     * @param $addressProcessMode
     * @param $address
     * @param $customer
     * @return Mage_Customer_Model_Address
     */
    public function checkDefaultAddress(
        $addressProcessMode,
        Mage_Customer_Model_Address $address,
        Mage_Customer_Model_Customer $customer
    ) {
        if ($addressProcessMode == self::ADDRESS_PROCESS_MODE_SEPARATED) {
            switch ($address->getAddressType()) {
                case self::ADDRESS_TYPE_BILLING:
                    if (!$customer->getDefaultBillingAddress()) {
                        $address->setIsDefaultBilling(true);
                    }
                    break;
                case self::ADDRESS_TYPE_SHIPPING:
                    if (!$customer->getDefaultShippingAddress()) {
                        $address->setIsDefaultShipping(true);
                    }
                    break;
                default; // Address type not defined.
            }
        } elseif ($addressProcessMode == self::ADDRESS_PROCESS_MODE_COPY_BILLING
            || $addressProcessMode == self::ADDRESS_PROCESS_MODE_COPY_SHIPPING
        ) {
            if (!$customer->getDefaultBillingAddress()) {
                $address->setIsDefaultBilling(true);
            }
            if (!$customer->getDefaultShippingAddress()) {
                $address->setIsDefaultShipping(true);
            }
        }

        return $address;
    }

    /**
     * @return int
     */
    public function getStateSettings()
    {
        if (!isset($this->_stateSettings)) {
            return $this->_stateSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/address_details/state');
        } else {
            return $this->_stateSettings;
        }
    }

    /**
     * @return int
     */
    public function getPhoneSettings()
    {
        if (!isset($this->_phoneSettings)) {
            return $this->_phoneSettings
                = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/telephone');
        } else {
            return $this->_phoneSettings;
        }
    }

    /**
     * Getter for the gender settings
     *
     * @return mixed
     */
    public function getGenderSettings()
    {
        if (!isset($this->_genderSettings)) {
            return $this->_genderSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/gender');
        } else {
            return $this->_genderSettings;
        }
    }

    /**
     * Getter for the prefix settings
     *
     * @return mixed
     */
    public function getPrefixSettings()
    {
        if (!isset($this->_prefixSettings)) {
            return $this->_prefixSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/prefix');
        } else {
            return $this->_prefixSettings;
        }
    }

    /**
     * Getter for the middlename settings
     *
     * @return mixed
     */
    public function getMiddlenameSettings()
    {
        if (!isset($this->_middlenameSettings)) {
            return $this->_middlenameSettings
                = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/middlename');
        } else {
            return $this->_middlenameSettings;
        }
    }

    /**
     * Getter for the suffix settings
     *
     * @return mixed
     */
    public function getSuffixSettings()
    {
        if (!isset($this->_suffixSettings)) {
            return $this->_suffixSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/suffix');
        } else {
            return $this->_suffixSettings;
        }
    }

    /**
     * @return int
     */
    public function getCompanySettings()
    {
        if (!isset($this->_companySettings)) {
            return $this->_companySettings
                = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/company');
        } else {
            return $this->_companySettings;
        }
    }

    /**
     * @return int
     */
    public function getVatTaxSettings()
    {
        if (!isset($this->_vatTaxSettings)) {
            return $this->_vatTaxSettings
                = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/vattaxid');
        } else {
            return $this->_vatTaxSettings;
        }
    }

    /**
     * @return int
     */
    public function getRequiredShipping()
    {
        if (!isset($this->_requiredShipping)) {
            return $this->getIsAddressRequiredConfig('qquoteadv_quote_form_builder/options/require_shipping');
        } else {
            return $this->_requiredShipping;
        }
    }

    /**
     * @return int
     */
    public function getRequiredBilling()
    {
        if (!isset($this->_requiredBilling)) {
            return $this->getIsAddressRequiredConfig('qquoteadv_quote_form_builder/options/require_billing');
        } else {
            return $this->_requiredBilling;
        }
    }

    /**
     * Helper function that processes the group exception to a boolean setting
     *
     * @param $config
     * @return bool
     */
    private function getIsAddressRequiredConfig($config)
    {
        $setting = Mage::getStoreConfig($config);
        $bool = false;

        if ($setting == 2) {
            $groupsCommaSeperated = Mage::getStoreConfig($config . '_groups');
            $groups = explode(',', $groupsCommaSeperated);
            $group = Mage::getSingleton('customer/session')->getCustomerGroupId();
            if (in_array($group, $groups)) {
                $bool = true;
            }
        } elseif ($setting == 1) {
            $bool = true;
        }

        return $bool;
    }

    /**
     * Get the status of the address boxes
     *
     * @return string
     */
    public function getBoxesStatus()
    {
        $boxes = 'All Boxes';
        if ($this->getRequiredBilling() && !$this->getRequiredShipping()) {
            $boxes = 'billing';
        } else {
            if (!$this->getRequiredBilling() && $this->getRequiredShipping()) {
                $boxes = 'shipping';
            }
        }
        return $boxes;
    }

    /**
     * Check if customer requested shipping estimate on quote in frontend
     *
     * @return bool
     */
    public function checkShippingEstimatesInSession()
    {
        $quoteRatesList = Mage::getSingleton('customer/session')->getData('quoteRatesList');
        if ((is_array($quoteRatesList) && count($quoteRatesList) > 0) || $quoteRatesList == true) {
            return true;
        }

        return false;
    }

    /**
     * Check if the addresses differ from the quote form
     *
     * @return bool
     */
    public function checkSameShippingBillingQuoteFormFrontend()
    {
        $addresses = Mage::getSingleton('customer/session')->getData('quoteAddresses');
        return ($addresses['shippingAddress']->getCustomerAddressId() ==
            $addresses['billingAddress']->getCustomerAddressId());
    }

    /**
     * Function that sets selected customer data fields on both address type arrays
     *
     * @param $customerData
     * @return array
     */
    public function prepareGuestAddress($customerData)
    {
        $newPostData = array();
        if (is_array($customerData)) {
            $customerData = $this->parseCustomerData($customerData);
            $addressTypes = array(
                self::ADDRESS_TYPE_SHIPPING,
                self::ADDRESS_TYPE_BILLING
            );

            foreach ($addressTypes as $addressType) {
                $newPostData[$addressType] = $customerData;
            }
        }

        return $newPostData;
    }

    /**
     * Remove not needed information
     *
     * @param $customerData
     * @return array
     */
    public function parseCustomerData($customerData)
    {
        $parsedCustomerData = array();
        $requiredValues = array(
            'firstname',
            'lastname',
            'email'
        );

        foreach ($requiredValues as $requiredValue) {
            if ($customerData[$requiredValue]) {
                $parsedCustomerData[$requiredValue] = $customerData[$requiredValue];
            }
        }

        return $parsedCustomerData;
    }
}
