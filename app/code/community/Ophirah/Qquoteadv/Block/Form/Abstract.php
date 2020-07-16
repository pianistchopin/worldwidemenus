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
 * Class Ophirah_Qquoteadv_Block_Form_Abstract
 */
abstract class Ophirah_Qquoteadv_Block_Form_Abstract extends Ophirah_Qquoteadv_Block_Qquote_Abstract
{
    /**
     * Function that (re-)sets the customer data on the customer (from session) object
     *
     * @return mixed
     */
    public function setCustomer(){
        // get customer from session
        $customer = $this->getCustomerSession()->getCustomer();

        // Retrieve Customer Request data from session
//        $client_request = "";
        $quoteData = $this->getCustomerSession()->getQuoteData();
        if($quoteData){
//            if(isset($quoteData['customer']['client_request'])){
//                $client_request = $quoteData['customer']['client_request'];
//            }
            $customer->addData($quoteData['customer']);
        }

        // Add customer information after shipping estimate request
        $quoteCustomer = Mage::getSingleton('customer/session')->getData('quoteCustomer');
        if ($quoteCustomer) {
            $customer->addData($quoteCustomer->getData());
        }

        return $customer;
    }

    /**
     * Get customer email from the customer on the customer session
     *
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->getCustomerSession()->getCustomer()->getEmail();
    }

    /**
     * Check if customer is logged in
     *
     * @return mixed
     */
    public function isCustomerLoggedIn()
    {
        return $this->getCustomerSession()->isLoggedIn();
    }

    /**
     * Getter for the state settings
     *
     * @return mixed
     */
    public function getStateSettings(){
        if(!isset($this->_stateSettings)){
            return $this->_stateSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/address_details/state');
        }else{
            return $this->_stateSettings;
        }
    }

    /**
     * Getter for the phone settings
     *
     * @return mixed
     */
    public function getPhoneSettings(){
        if(!isset($this->_phoneSettings)){
            return $this->_phoneSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/telephone');
        }else{
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
            return $this->_middlenameSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/middlename');
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
     * Getter for the company settings
     *
     * @return mixed
     */
    public function getCompanySettings(){
        if(!isset($this->_companySettings)){
            return $this->_companySettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/company');
        }else{
            return $this->_companySettings;
        }
    }

    /**
     * Getter for the vattax settings
     *
     * @return mixed
     */
    public function getVatTaxSettings(){
        if(!isset($this->_vatTaxSettings)){
            return $this->_vatTaxSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/vattaxid');
        }else{
            return $this->_vatTaxSettings;
        }
    }
}
