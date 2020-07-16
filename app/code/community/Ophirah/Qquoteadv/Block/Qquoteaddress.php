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
 * Class Ophirah_Qquoteadv_Block_Qquoteaddress
 */
class Ophirah_Qquoteadv_Block_Qquoteaddress extends Ophirah_Qquoteadv_Block_Form_Abstract
{
    const BILLING_FORM = 'billDiv';
    const SHIPPING_FORM = 'shipDiv';
    const CUSTOMER_FORM = 'billing-new-address-form';

    protected $_phoneSettings;
    protected $_companySettings;
    protected $_vatTaxSettings;
    protected $_stateSettings;
    protected $_requiredShipping;
    protected $_requiredBilling;

    /**
     * Function that extracts the value for a given fieldname according to the given type
     *
     * @param $fieldname
     * @param $type
     * @param null $streetNumber
     * @return string
     */
    public function getValue($fieldname, $type, $streetNumber = null)
    {
        //don't fill values when customer for customer mode is enabled
        if (Mage::helper('qquoteadv')->customerForCustomerIsEnabled()) {
            return '';
        }

        $value = $this->_getRegisteredValue($type);
        if ($value) {
            // When quote data is stored
            // address data is an array
            // Create object from array
            if (is_array($value)) {
                $newValue = new Varien_Object();
                $newValue->setData($value);
                $value = $newValue;
            }

            if ($fieldname == "street") {
                $street = $value->getData('street');
                if ($street) {
                    $streets = explode("\n", $street);
                    foreach ($streets as $key => $street) {
                        if ($key == $streetNumber) {
                            return $street;
                        }
                    }
                } else {
                    return '';
                }
            }

            if ($fieldname == "email") {
                return $this->getCustomerSession()->getCustomer()->getEmail();
            }

            if ($fieldname == "country") {
                $countryCode = $value->getData("country_id");
                return $countryCode;
            }

            return $value->getData($fieldname);
        }

        return '';
    }

    /**
     * Get the registered address or the primary address
     *
     * @param string $type
     * @return mixed
     */
    protected function _getRegisteredValue($type = 'billing')
    {
        // When Quote Shipping Estimate is requested
        // use data from session
        $quoteAddresses = $this->getCustomerSession()->getData('quoteAddresses');
        if ($quoteAddresses) {
            if ($type == 'billing' && isset($quoteAddresses['billingAddress'])) {
                return $quoteAddresses['billingAddress'];
            }

            if ($type == 'shipping' && isset($quoteAddresses['shippingAddress'])) {
                return $quoteAddresses['shippingAddress'];
            }
        }
        // Default data
        if ($type == 'billing') {
            return $this->getCustomerSession()->getCustomer()->getPrimaryBillingAddress();
        }

        if ($type == 'shipping') {
            return $this->getCustomerSession()->getCustomer()->getPrimaryShippingAddress();
        }

        return null;
    }

    /**
     * Generate a login url for the email
     *
     * @return mixed
     */
    public function getLoginUrl()
    {
        if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
            return $this->getUrl('customer/account/login/', array('referer' => $this->getUrlEncoded('*/*/*', array('_current' => true))));
        }

        return $this->getUrl('customer/account/login/');
    }

    /**
     * Retrieve storeConfigData from
     * config_data table
     *
     * @param $fieldPrefix
     * @param $field
     * @param string $storeId
     * @return bool|mixed
     */
    public function getStoreConfigSetting($fieldPrefix, $field, $storeId = "1")
    {
        $return = false;

        if ($fieldPrefix != null && $field != null) {
            $storeSetting = Mage::getStoreConfig($fieldPrefix . $field, $storeId);
            if ($storeSetting > 0) {
                $return = $storeSetting;
            }
        }

        return $return;
    }

    /**
     * Check is field is required in
     * the store config settings
     *
     * @param $fieldPrefix
     * @param $field
     * @param string $storeId
     * @return bool|Varien_Object
     */
    public function isRequired($fieldPrefix, $field, $storeId = "1")
    {
        $storeSetting = $this->getStoreConfigSetting($fieldPrefix, $field, $storeId);

        if (!$storeSetting) {
            return false;
        }

        $return = new Varien_Object;
        $required = '<span class="required">*</span>';
        $class = 'required-entry';
        if ((int)$storeSetting == 2) {
            $return->setData('required', $required);
            $return->setData('class', $class);
            return $return;
        }

        return $return;
    }

    /**
     * Function to check if an address box need to be checked in the interface
     *
     * @return string
     */
    public function getAddressCheckboxChecked(){
        if($this->isCustomerLoggedIn()) {
            $qquoteadv = $this->getCustomerSession()->getData('quoteCustomer');
            if($qquoteadv) {
                $billIsShip = $qquoteadv->getData('billIsShip');
                $shipIsBill = $qquoteadv->getData('shipIsBill');
                $email = $qquoteadv->getData('email');
                if (isset($billIsShip) || isset($shipIsBill)) {
                    return 'checked';
                } elseif (isset($email)) {
                    return '';
                }
            }
        }
        if($this->getBoxesStatus() == 'shipping' || $this->getBoxesStatus() == 'billing'){
            return 'checked';
        }
        return '';
    }

    /**
     * Checks whether the addresses are the same in the quote form in the front-end
     *
     * @return bool
     */
    public function checkSameAddress()
    {
        $billingSameAsShipping = Mage::helper('qquoteadv/address')->checkSameShippingBillingQuoteFormFrontend();
        return $billingSameAsShipping;
    }

    /**
     * Check whether the checkbox event should be fired on the quote form in the front-end
     *
     * @param bool $returnString
     * @return bool|string
     */
    public function getClickEventCheckboxQuoteFormFrontend($returnString = false)
    {
        $fireClickEventCheckbox = false;
        if (Mage::helper('qquoteadv/address')->checkShippingEstimatesInSession()) {
            $checkIfSameAddress = (bool)$this->checkSameAddress();
            $fireClickEventCheckbox = !$checkIfSameAddress;
        }

        if ($returnString) {
            if ($fireClickEventCheckbox) {
                return 'true';
            } else {
                return 'false';
            }
        }

        return (bool)$fireClickEventCheckbox;
    }

    /**
     * Get the name of the address checkbox
     *
     * @return string
     */
    public function getAddressCheckboxName(){
        if($this->getBoxesStatus() == 'shipping'){
            return 'billIsShip';
        }else if($this->getBoxesStatus() == 'billing'){
            return 'shipIsBill';
        }else{
            return '';
        }
    }

    /**
     * Get the text of the address checkbox
     *
     * @return string
     */
    public function getAddressCheckboxText(){
        if($this->getBoxesStatus() == 'shipping'){
            return Mage::helper('qquoteadv')->__('Billing Address is same as Shipping Address');
        }else if($this->getBoxesStatus() == 'billing'){
            return Mage::helper('qquoteadv')->__('Shipping Address is same as Billing Address');
        }else{
            return '';
        }
    }

    /**
     * Get the onclick javascript for a given address box
     *
     * @return string
     */
    public function getAddressOnClick(){
        if($this->getBoxesStatus() == 'shipping'){
            return 'toggleDefaultAddress()';
        }else if($this->getBoxesStatus() == 'billing'){
            return 'toggleDefaultAddress()';
        }else{
            return '';
        }
    }

    /**
     * Get the status of the address boxes
     *
     * @return string
     */
    public function getBoxesStatus(){
        return Mage::helper('qquoteadv/address')->getBoxesStatus();
    }

    /**
     * Return the display status based on the address box status
     *
     * @return string
     */
    public function getShowAddressCheckbox(){
        if ($this->getBoxesStatus() == 'All Boxes'){
            return 'display:none;';
        }else{
            return '';
        }
    }

    /**
     * Returns the id and the display status fot the shipping box
     *
     * @return string
     */
    public function getShippingBoxSettings()
    {
        $html = '';
        if ($this->isCustomerLoggedIn() && !empty($billingEmail)) {
            $html = 'id="qquoteadv_shipping_box"' . $this->getShowAddressCheckbox();
        }
        return $html;
    }

    /**
     * Getter for the email address error url
     *
     * @return mixed
     */
    public function getEmailErrorUrl(){
        return $this->getUrl('qquoteadv/index/useJsEmail');
    }

    /**
     * Getter for the email address error url that also checks when customer is logged-in
     *
     * @return mixed
     */
    public function getForceEmailErrorUrl(){
        return $this->getUrl('qquoteadv/index/useJsEmailForce');
    }

    /**
     * Error message for email already exists error
     *
     * @return mixed
     */
    public function getEmailErrorMessage(){
        return Mage::helper('qquoteadv')->__('Customer email already exists. You should login <a href=%s>here</a>', $this->getLoginUrl());
    }

    /**
     * Get product from registry
     *
     * @param null $productId (just to avoid declaration errors)
     * @return false|Mage_Core_Model_Abstract|mixed
     */
    public function getProduct($productId = null)
    {
        //user parrent call if an id is given
        if (isset($productId) && !empty($productId)) {
            parent::getProduct($productId);
        }

        if (!Mage::registry('product')) {
            return Mage::getModel('catalog/product');
        }
        return Mage::registry('product');
    }

    /**
     * ==============  DEPRECATED  ==============
     *
     * The following functions are deprecated.
     */

    /**
     * @deprecated since version 5.2.2
     * Returns complete address in table rows.
     * @param $addressType
     * @return string
     */
    public function getMultipleLineAddress($addressType){
        $numberOfLines = Mage::getStoreConfig('customer/address/street_lines');
        $addressRows = '';

        for($line = 0; $line < $numberOfLines; $line++){
            if($addressType == 'shipping'){
                $addressRows .= $this->_getShippingAddressLine($line);
            }else{
                $addressRows .= $this->_getBillingAddressLine($line);
            }
        }
        return $addressRows;
    }

    /**
     * @deprecated since version 5.2.2
     * Returns single billing address line
     * @param int $lineNumber
     * @return string
     */
    protected function _getBillingAddressLine($lineNumber = 0){
        $addressLineValue = Mage::helper('qquoteadv/address')->splitMultipleLineAddress($this->getValue('street', 'billing'));
        if($lineNumber == 0){
            $headerText = Mage::helper('sales')->__('Address').'<span class="required"></span><br/>';
            $required = 'required-entry';
        }else{
            $headerText = "";
            $required = '';
        }

        //check for empty address lines
        if(!isset($addressLineValue[$lineNumber])){
            return '';
        }

        $html = '
                <tr style="margin-bottom: 1px;">
                    <td class="left">'.$headerText.'
                        <input onfocus="Element.setStyle(this, {color:\'#2F2F2F\'});" type=\'text\'
                               value="'.$addressLineValue[$lineNumber].'"
                               name=\'customer[address'.$lineNumber.']\'
                               id=\'customer:address'.$lineNumber.'\' class="'.$required.' input-text addr w224"/>
                    </td>
                    <td class="p5"></td>
                </tr>';
        return $html;
    }

    /**
     * @deprecated since version 5.2.2
     * Returns single shipping address line
     * @param int $lineNumber
     * @return string
     */
    protected function _getShippingAddressLine($lineNumber = 0){
        $addressLineValue = Mage::helper('qquoteadv/address')->splitMultipleLineAddress($this->getValue('street', 'shipping'));
        if($lineNumber == 0){
            $headerText = Mage::helper('sales')->__('Address').'<span class="required"></span><br/>';
            $required = 'required-entry';
        }else{
            $headerText = "";
            $required = '';
        }

        //check for empty address lines
        if(!isset($addressLineValue[$lineNumber])){
            return '';
        }

        $html = '
                <tr style="margin-bottom: 1px;">
                    <td class="left">'.$headerText.'
                        <input onfocus="Element.setStyle(this, {color:\'#2F2F2F\'});" type=\'text\'
                               value="'.$addressLineValue[$lineNumber].'"
                               name=\'customer[shipping_address'.$lineNumber.']\'
                               id=\'customer:shipping_address'.$lineNumber.'\' class="'.$required.' input-text addr w224"/>
                    </td>
                    <td class="p5"></td>
                </tr>';
        return $html;
    }
    /**
     * Getter for the setting 'qquoteadv_quote_frontend/shoppingcart_quotelist/disable_exist_account_check'
     *
     * @return mixed
     */
    public function getDisableEmailCheck(){
        return Mage::getStoreConfig('qquoteadv_quote_frontend/shoppingcart_quotelist/disable_exist_account_check');
    }

    /**
     * Getter for the setting 'qquoteadv_quote_frontend/shoppingcart_quotelist/enable_quote_direct_print'
     *
     * @return mixed
     */
    public function getEnableQuoteDirectPrint(){
        return Mage::getStoreConfig('qquoteadv_quote_frontend/shoppingcart_quotelist/enable_quote_direct_print');
    }

    /**
     * Getter for the required shipping settings
     *
     * @return mixed
     */
    public function getRequiredShipping(){
        return Mage::helper('qquoteadv/address')->getRequiredShipping();
    }

    /**
     * Getter for the required billing settings
     *
     * @return mixed
     */
    public function getRequiredBilling(){
        return Mage::helper('qquoteadv/address')->getRequiredBilling();
    }
}
