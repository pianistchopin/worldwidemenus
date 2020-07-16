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
 * Class Ophirah_Qquoteadv_Model_Qqadvcustomer
 */
class Ophirah_Qquoteadv_Model_Qqadvcustomer extends Mage_Sales_Model_Quote
{
    CONST XML_PATH_QQUOTEADV_PROPOSAL_EXPIRE_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal_expire';
    CONST XML_PATH_QQUOTEADV_PROPOSAL_REMINDER_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal_reminder';
    CONST XML_PATH_QQUOTEADV_PROPOSAL_ACCEPTED_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal_accepted';
    CONST MAXIMUM_AVAILABLE_NUMBER = 99999999;

    /**
     * @var array
     */
    protected $_quoteTotal = array();

    /**
     * @var null
     */
    protected $_quoteCurrency = null;

    /**
     * @var null
     */
    protected $_baseCurrency = null;

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer = null;

    /**
     * @var null
     */
    protected $_address = null;

    /**
     * @var null
     */
    protected $_requestItems = null;

    /**
     * @var null
     */
    protected $_weight = null;

    /**
     * @var null
     */
    protected $_itemsQty = null;

    /**
     * @var null
     */
    protected $_items = null;

    /**
     * @var null
     */
    protected $_totalAmounts = null;

    /**
     * @var int
     */
    protected $_dataBaseToQuoteRate = 1;

    /**
     * Construct function
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/qqadvcustomer');
    }

    /**
     * Quote Totals
     * Used in quote backend
     *
     * @param $quoteTotal
     * @return $this
     */
    public function setQuoteTotals($quoteTotal)
    {
        $this->_quoteTotal = $quoteTotal;

        return $this;
    }

    /**
     * Quote Totals
     * Used in quote backend
     *
     * @return array
     */
    public function getQuoteTotals()
    {
        return $this->_quoteTotal;
    }

    /**
     * Calculate Currency Rate from
     * Base => Quote
     *
     * @return int
     */
    public function getBase2QuoteRate()
    {
        if (!$this->getData('currency')) {
            return 1;
        }

        $baseCurrency = Mage::app()->getStore($this->getStoreId())->getBaseCurrencyCode();//Mage::app()->getBaseCurrencyCode();
        $quoteCurrency = $this->getData('currency');

        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrency, $quoteCurrency);
        $b2qRate = (isset($rates[$quoteCurrency])) ? $rates[$quoteCurrency] : 1;

        $this->_dataBaseToQuoteRate = $this->getData('base_to_quote_rate');
        $this->setData('base_to_quote_rate', $b2qRate);

        return $b2qRate;
    }

    /**
     * Create Array with Totals
     * Used in quote backend
     *
     * @param boolean // if $short is 'true' the 'address' and 'items' objects will be left out
     * @return array
     */
    public function getTotalsArray($short = false)
    {
        $this->getAddressesCollection();
        $this->setQuoteCurrencyCode($this->getCurrency());
        $getTotals = $this->getTotals();
        $totalsArray = array();
        if ($short === true) {
            foreach ($getTotals as $totalCode => $totalData) {
                $newTotalData = new Varien_Object();
                foreach ($totalData->getData() as $k => $v) {
                    if ($k != 'address' && $k != 'items') {
                        $newTotalData->setData($k, $v);
                    }
                }
                $totalsArray[$totalCode] = $newTotalData->getData();
            }
        } else {
            foreach ($getTotals as $totalCode => $totalData) {
                $totalsArray[$totalCode] = $totalData->getData();
            }
        }
        return $totalsArray;
    }

    /**
     * Get all quote totals (sorted by priority)
     * Method process quote states isVirtual and isMultiShipping
     *
     * @return array
     */
    public function getTotals()
    {
        /**
         * If quote is virtual we are using totals of billing address because
         * all items assigned to it
         */
        if ($this->isVirtual()) {
            return $this->getBillingAddress()->getTotals();
        }

        //get 'tax/calculation/based_on' the setting from magento
        $taxCalculationBasedOn = Mage::getStoreConfig('tax/calculation/based_on');

        //if it is not billing, fallback to shipping
        if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
            $shippingAddress = $this->getBillingAddress();
            $totals = $shippingAddress->getTotals();
            // Going through all quote addresses and merge their totals
            foreach ($this->getAddressesCollection() as $address) {
                if ($address->isDeleted() || $address->getId() == $shippingAddress->getId()) {
                    continue;
                }
                foreach ($address->getTotals() as $code => $total) {
                    if (isset($totals[$code])) {
                        $newData = $total->getData();
                        foreach ($newData as $key => $value) {
                            if (is_numeric($value)) {
                                $currentValue = $totals[$code]->getData($key);
                                if(!isset($currentValue) || empty($currentValue) || (int)$currentValue == 0 ){
                                    $totals[$code]->setData($key, $value);
                                }
                            }
                        }
                    } else {
                        $totals[$code] = $total;
                    }
                }
            }

            $sortedTotals = array();
            foreach ($this->getBillingAddress()->getTotalModels() as $total) {
                /* @var $total Mage_Sales_Model_Quote_Address_Total_Abstract */
                $totalCode = $total->getCode();
                if (isset($totals[$totalCode])) {
                    $sortedTotals[$totalCode] = $totals[$totalCode];
                }
            }
        } else {
            $shippingAddress = $this->getShippingAddress();
            $totals = $shippingAddress->getTotals();
            // Going through all quote addresses and merge their totals
            foreach ($this->getAddressesCollection() as $address) {
                if ($address->isDeleted() || $address->getId() == $shippingAddress->getId()) {
                    continue;
                }
                foreach ($address->getTotals() as $code => $total) {
                    if (isset($totals[$code])) {
                        $newData = $total->getData();
                        foreach ($newData as $key => $value) {
                            if (is_numeric($value)) {
                                $currentValue = $totals[$code]->getData($key);
                                if(!isset($currentValue) || empty($currentValue) || (int)$currentValue == 0 ){
                                    $totals[$code]->setData($key, $value);
                                }
                            }
                        }
                    } else {
                        $totals[$code] = $total;
                    }
                }
            }

            $sortedTotals = array();
            foreach ($this->getShippingAddress()->getTotalModels() as $total) {
                /* @var $total Mage_Sales_Model_Quote_Address_Total_Abstract */
                $totalCode = $total->getCode();
                if (isset($totals[$totalCode])) {
                    $sortedTotals[$totalCode] = $totals[$totalCode];
                }
            }
        }

        return $sortedTotals;
    }

    /**
     * Event function to trigger before event
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        //frontend currency switcher fix, make sure that the currency on the quote store is set correctly
        if(Mage::app()->getStore()->isAdmin() || (Mage::getDesign()->getArea() == 'adminhtml')){
            try {
                Mage::helper('qquoteadv')->setCurrentCurrency($this->getCurrency());
            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
        
        if($this->getCurrency()){
            $this->setForcedCurrency(Mage::getModel('directory/currency')->load($this->getCurrency()));
        }

        parent::_beforeSave();
        Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesave', array('quote' => $this));

        return $this;
    }

    /**
     * Event function to trigger after event
     *
     * @return $this
     */
    protected function _afterSave()
    {
        Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersave', array('quote' => $this));
        return $this;
    }

    /**
     * Add quote to qquote_customer table
     * @param array $params quote created information
     * @return mixed
     */
    public function addQuote($params)
    {
        $params['hash'] = $this->getRandomHash(40);
        $this->setData($params);
        $this->addNewAddress();
        $this->save();

        return $this;
    }

    /**
     * Add customer address for the particular quote
     * @param integer $id quote id to be updated
     * @param array $params array of field(s) to be updated
     * @return mixed
     */
    public function addCustomer($id, $params)
    {
        if (!empty($params['shipping_address'])) {
            $this->load($id)->addData($params)->setId($id);
            $this->addNewAddress();
            $this->save();
        } else {
            //get the data from the existing user (on email)
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId($params['store_id']);
            $customer->loadByEmail($params['email']);
            foreach ($customer->getAddresses() as $addressModel) {
                $params['country_id'] = $addressModel->getCountryId();
                $params['region_id'] = $addressModel->getRegionId();
                $params['shipping_country_id'] = $addressModel->getCountryId();
                $params['shipping_region_id'] = $addressModel->getRegionId();
                break; //just one address
            }

            $this->load($id)->addData($params)->setId($id);
            $this->addNewAddress();
            $this->save();
        }

        return $this;
    }

    /**
     * Check if email allready exists
     * If not, create new account
     * 
     * @param   array       // customer data
     * @return  object      // Mage_Customer_Model_Customer
     */
    public function checkCustomer($params)
    {
        // Params
        if (!isset($params['website_id'])) {
            $params['website_id'] = Mage::app()->getStore()->getWebsiteId();
        }
        try {
            if (!Zend_Validate::is($params['email'], 'EmailAddress')) {

                // TODO: Create action to do if email address is invalid
                //notice that we use the translation from 'newsletter'
                //so this sentence is always translated by default Magento translation files
                Mage::throwException(Mage::helper('newsletter')->__('Please enter a valid email address.'));
            }

            if (Mage::helper('qquoteadv')->userEmailAlreadyExists($params['email'])) {
                $this->_isEmailExists = true;
                // TODO: make 'update current address':
                // Set action to do if customer exists
                // Adding customer address if customer
                // already exists
                $customer = Mage::getModel('customer/customer')->setWebsiteId($params['website_id'])->loadByEmail($params['email']);
                $address = Mage::helper('qquoteadv/address')->buildAddress($params);

                // Add address information to quote
                foreach ($address as $key => $updateData) {
                    $customer->setData($key, $updateData);
                }

                // Check if address allready exists
                $addressFound = false;
                foreach ($customer->getAddresses() as $checkAddress) {
                    if ($checkAddress->getData('country_id') == $customer->getData('country_id') &&
                        $checkAddress->getData('postcode') == $customer->getData('postcode') &&
                        $checkAddress->getData('street') == $customer->getData('street')
                    ) {
                        $addressFound = true;
                    }
                }

                // Add new address
                if ($addressFound === false) {
                    $vars['saveAddressBook'] = 1;
                    $vars['defaultShipping'] = (!$customer->getDefaultShipping()) ? 1 : 0;
                    $vars['defaultBilling'] = (!$customer->getDefaultBilling()) ? 1 : 0;

                    Mage::helper('qquoteadv/address')->addCustomerAddress($customer, $address['billing'], $vars);
                }

            } else {
                // create new account                
                $customer = $this->_createNewCustomerAccount($params);

                // Set address
                $address = Mage::helper('qquoteadv/address')->buildAddress($params);

                foreach ($address as $key => $updateData) {
                    $customer->setData($key, $updateData);
                }

                $vars['saveAddressBook'] = 1;
                $vars['defaultShipping'] = 1;
                $vars['defaultBilling'] = 1;

                Mage::helper('qquoteadv/address')->addCustomerAddress($customer, $address['billing'], $vars);
            }
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }

        return $customer;
    }

    /**
     * Create new customer account
     * 
     * @param   array       // Customer account params
     * @return  object      // Mage_Customer_Model_Customer
     */
    protected function _createNewCustomerAccount($params)
    {
        $password_test = $this->_generatePassword(7);
        $is_subscribed = 0;

        //# NEW USER REGISTRATION
        if ($params['email'] && !$params['logged_in'] === true) {
            $cust = Mage::getModel('customer/customer');
            $cust->setWebsiteId($params['website_id'])->loadByEmail($params['email']);

            //#create new user
            if (!$cust->getId()) {
                $customerData = array(
                    'firstname' => $params['firstname'],
                    'lastname' => $params['lastname'],
                    'email' => $params['email'],
                    'password' => $password_test,
                    'password_hash' => md5($password_test),
                    'is_subscribed' => $is_subscribed,
                    'new_account' => true
                );

                $customer = Mage::getModel('qquoteadv/customer_customer');
                $customer->setWebsiteId($params['website_id']);
                $customer->setData($customerData);
                $customer->save();
            }
        }

        return $customer;
    }

    /**
     * Update Quote
     *
     * @param integer $id
     * @param array $params
     * @return \Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function updateQuote($id, $params)
    {
        $this->load($id)
            ->setData($params)
            ->setId($id);
        $this->save();

        return $this;
    }

    /**
     * Get the store croup name of the store on this quote
     *
     * @return mixed
     */
    public function getStoreGroupName()
    {
        $storeId = $this->getStoreId();
        if (is_null($storeId)) {
            return $this->getStoreName(1); // 0 - website name, 1 - store group name, 2 - store name
        }
        return $this->getStore()->getGroup()->getName();
    }

    /**
     * Retrieve store model instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        $storeId = $this->getStoreId();
        if ($storeId) {
            return Mage::app()->getStore($storeId);
        }
        return Mage::app()->getStore();
    }


    /**
     * Get formated quote created date in store timezone
     *
     * @param   string $format date format type (short|medium|long|full)
     * @return  string
     */
    public function getCreatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getCreatedAt(), $format);
    }

    /**
     * Get formated quote created date in given format
     *
     * @param   string $format date format
     * @return  string
     */
    public function getCreatedAtInFormat($format = 'm/d/Y')
    {
        return Mage::getModel('core/date')->date($format, $this->getCreatedAt());
    }

    /**
     * Get formated quote expire date in store timezone
     * Additionally show how many days a proposal is valid
     *
     * @param $format
     * @param bool $showRemainingDays
     * @return string
     */
    public function getExpireAtFormated($format, $showRemainingDays = false)
    {
        if($showRemainingDays){
            $proposalDate = $this->getCreatedAt();
            $expiryDate = $this->getExpiry();

            if($expiryDate){
                $expDays = (int)round((date_create($expiryDate)->format("U") - date_create($proposalDate)->format("U")) / (60 * 60 * 24));
            } else {
                $expDays = (int)Mage::getStoreConfig('qquoteadv_quote_configuration/expiration_times_and_notices/expirtime_proposal', $this->getStoreId());
            }

            if ($expDays) {
                $date = date('D M j Y', strtotime("+$expDays days", strtotime($proposalDate)));
                $note = "( " . Mage::helper('qquoteadv')->__("%s days", $expDays) . " )";
                return Mage::helper('core')->formatDate($date, $format) . ' ' . $note;
            }
        }

        return Mage::helper('core')->formatDate($this->getExpiry(), $format);
    }

    /**
     * Get formated quote expire date in given format
     *
     * @param   string $format date format
     * @return  string
     */
    public function getExpireAtInFormat($format = 'm/d/Y')
    {
        return Mage::getModel('core/date')->date($format, $this->getExpiry());
    }

    /**
     * Get Address formatted for html
     * @param string $type
     * @return string
     */
    public function getBillingAddressFormatted($type = 'html')
    {
        return $this->getBillingAddress()->format($type);
    }

    /**
     * Get Address formatted for html
     * @param string $type
     * @return string
     */
    public function getShippingAddressFormatted($type = 'html')
    {
        return $this->getShippingAddress()->format($type);
    }

    /**
     * Get the rate for the currency on this quote and the base currency
     *
     * @return int|float
     */
    public function getBaseToQuoteRate()
    {
        $currency = Mage::getModel('directory/currency');
        $currency->setData('currency_code', Mage::getStoreConfig('currency/options/base'));
        if ($this->getData('currency')) {
            return $currency->getRate($this->getData('currency'));
        } else {
            return 1;
        }
    }

    /**
     * Get Shipping Methods formatted for html
     * @return string
     */
    public function getShippingMethodsFormatted()
    {
        // Get Shipping Methods
        $shippingRates = Mage::getModel('qquoteadv/quoteshippingrate')->getShippingRatesList($this);
        $shippingRateList = $shippingRates['shippingList'];

        // Draw Shipping Rates
        $str = "";
        foreach ($shippingRateList as $k => $v) {
            // Draw Carrier Title
            $str .= '<span style="font-weight:bold;line-height:2em;">' . $k . '</span><br />';
            foreach ($v as $rate) {
                $price = $this->formatPrice($this->getBaseToQuoteRate() * $rate['price']);
                $str .= '<span style="margin-left: 10px;">' . uc_words($rate['method_list']) . " -  <b>" . $price . "</b></span><br />";
            }
        }

        return $str; //$this->_formatAddress($str); 
    }

    /**
     * Function to get variables in email templates
     * If $var is allowed, it's value will be returned
     *
     * @param $var
     * @return null
     */
    public function getVariable($var)
    {
        $allowed_var = array(
            "created_at",
            "updated_at",
            "is_quote",
            "prefix",
            "firstname",
            "middlename",
            "lastname",
            "suffix",
            "company",
            "email",
            "country_id",
            "region",
            "region_id",
            "city",
            "address",
            "postcode",
            "telephone",
            "fax",
            "client_request",
            "shipping_type",
            "increment_id",
            "shipping_prefix",
            "shipping_firstname",
            "shipping_middlename",
            "shipping_lastname",
            "shipping_suffix",
            "shipping_company",
            "shipping_country_id",
            "shipping_region",
            "shipping_region_id",
            "shipping_city",
            "shipping_address",
            "shipping_postcode",
            "shipping_telephone",
            "shipping_fax",
            "imported",
            "currency",
            "expiry",
            "shipping_description",
            "address_shipping_description",
            "address_shipping_method"
        );

        if (in_array($var, $allowed_var)) {
            return $this->getData($var);
        }

        return null;
    }

    /**
     * Get the upload http path for files for this quote
     *
     * @return string
     */
    public function getFullPath()
    {
        $valid = Mage::helper('qquoteadv')->isValidHttp($this->getPath());
        $path = $this->getPath(); //urlencode($this->getPath());
        if ($valid) {
            return $path;
        } else {
            return self::getUploadPath(array('dir' => $this->getData('quote_id'), 'file' => $path));
        }
    }

    /**
     * Get the upload url for a give file path
     *
     * @param null $filePath
     * @return string
     */
    public function getUploadPath($filePath = null)
    {
        if (Mage::getStoreConfig('qquoteadv_advanced_settings/general/upload_folder', $this->getStoreId())) {
            $fileUpload = Mage::getStoreConfig('qquoteadv_advanced_settings/general/upload_folder', $this->getStoreId());
        } else {
            $fileUpload = 'qquoteadv';
        }

        if ($filePath != null) {
            if (is_array($filePath)) {
                $fileUpload .= DS . $filePath['dir'] . DS . $filePath['file'];
            } else {
                $fileUpload .= DS . $filePath;
            }
        }

        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $fileUpload;

    }

    /**
     * Get the upload dir for a give path
     *
     * @param null $filePath
     * @return string
     */
    public function getUploadDirPath($filePath = null)
    {
        if (Mage::getStoreConfig('qquoteadv_advanced_settings/general/upload_folder', $this->getStoreId())) {
            $fileUpload = Mage::getStoreConfig('qquoteadv_advanced_settings/general/upload_folder', $this->getStoreId());
        } else {
            $fileUpload = 'qquoteadv'; // default value
        }

        if ($filePath != null) {
            $fileUpload .= DS . $filePath;
        }

        return Mage::getBaseDir('media') . DS . $fileUpload;

    }

    /**
     * Function that sends the expire email for all the quotes that are expired
     * After sending the email the quote status is set to expired
     */
    public function sendExpireEmail()
    {
        $expiredQuotes = $this->getCollection()
            ->addFieldToFilter('status', array('in' => array(50, 53, 56, 57)))
            ->addFieldToFilter('no_expiry', array('eq' => 0))
            ->addFieldToFilter('is_quote',  array('eq' => 1))
            ->addFieldToFilter('expiry', array('eq' => date('Y-m-d')));

        foreach ($expiredQuotes as $expiredQuote) {
            $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($expiredQuote->getData('quote_id'));

            $vars['quote'] = $_quoteadv;
            $vars['customer'] = Mage::getModel('customer/customer')->load($_quoteadv->getCustomerId());

            $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel($_quoteadv->getStoreId());
            $disabledEmail = Ophirah_Qquoteadv_Model_System_Config_Source_Email_Templatedisable::VALUE_DISABLED_EMAIL;

            if ($template != $disabledEmail){
                $expireTemplateId = Mage::getStoreConfig('qquoteadv_quote_emails/templates/proposal_expire', $_quoteadv->getStoreId());

                if (is_numeric($expireTemplateId)) {
                    $template->load($expireTemplateId);
                } else {
                    $template->loadDefault($expireTemplateId);
                }

                $sender = $this->getEmailSenderInfo();
                $template->setSenderName($sender['name']);
                $template->setSenderEmail($sender['email']);

                $bcc = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/bcc', $_quoteadv->getStoreId());
                if ($bcc) {
                    $bccData = explode(";", $bcc);
                    $template->addBcc($bccData);
                }

                if ((bool)Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc', $_quoteadv->getStoreId())
                    && Mage::helper('qquoteadv/licensechecks')->isAllowedSalesBcc()) {
                    $template->addBcc(Mage::getModel('admin/user')->load($_quoteadv->getUserId())->getEmail());
                }

                /**
                 * Opens the qquote_request.html, throws in the variable array
                 * and returns the 'parsed' content that you can use as body of email
                 */
                //emulate quote store for corret email design
                $appEmulation = Mage::getSingleton('core/app_emulation');
                $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($_quoteadv->getStoreId());

                /*
                 * getProcessedTemplate is called inside send()
                 */
                $template->setData('c2qParams', array('email' => $_quoteadv->getEmail(), 'name' => $_quoteadv->getFirstname()));
                Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $template));
                $res = $template->send($_quoteadv->getEmail(), $_quoteadv->getFirstname(), $vars);
                Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $template, 'result' => $res));

                // Stop store emulation process
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

            }

            // update quote status
            $_quoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_EXPIRED);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
            $_quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
        }
    }

    /**
     * Function that sends the reminder email
     */
    public function sendReminderEmail()
    {
        if (Mage::getStoreConfig('qquoteadv_quote_configuration/expiration_times_and_notices/send_reminder') > 0) {

            $reminderQuotes = $this->getCollection()
                ->addFieldToFilter('status', array('in' => array(50, 52, 53, 56, 57)))
                ->addFieldToFilter('no_reminder', array('eq' => 0))
                ->addFieldToFilter('is_quote', array('eq' => 1))
                ->addFieldToFilter('reminder', array('eq' => date('Y-m-d')));

            foreach ($reminderQuotes as $_quoteadv) {
                if (substr($_quoteadv->getData('proposal_sent'), 0, 4) != 0) {
                    $vars['quote'] = $_quoteadv;
                    $vars['store'] = Mage::app()->getStore($_quoteadv->getStoreId());
                    $vars['customer'] = Mage::getModel('customer/customer')->load($_quoteadv->getCustomerId());

                    $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel($_quoteadv->getStoreId());

                    // get locale of quote sent so we can sent email in that language	
                    $storeLocale = Mage::getStoreConfig('general/locale/code', $_quoteadv->getStoreId());

                    $reminderTemplateId = Mage::getStoreConfig('qquoteadv_quote_emails/templates/proposal_reminder', $_quoteadv->getStoreId());
                    if ($reminderTemplateId) {
                        $templateId = $reminderTemplateId;
                    } else {
                        $templateId = self::XML_PATH_QQUOTEADV_PROPOSAL_REMINDER_EMAIL_TEMPLATE;
                    }

                    if (is_numeric($templateId)) {
                        $template->load($templateId);
                    } else {
                        $template->loadDefault($templateId, $storeLocale);
                    }

                    $sender = $_quoteadv->getEmailSenderInfo();
                    $template->setSenderName($sender['name']);
                    $template->setSenderEmail($sender['email']);

                    $bcc = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/bcc', $_quoteadv->getStoreId());
                    if ($bcc) {
                        $bccData = explode(";", $bcc);
                        $template->addBcc($bccData);
                    }

                    if ((bool)Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc', $_quoteadv->getStoreId())
                        && Mage::helper('qquoteadv/licensechecks')->isAllowedSalesBcc()) {
                        $template->addBcc(Mage::getModel('admin/user')->load($_quoteadv->getUserId())->getEmail());
                    }

                    /**
                     * Opens the qquote_request.html, throws in the variable array
                     * and returns the 'parsed' content that you can use as body of email
                     */
                    //emulate quote store for corret email design
                    $appEmulation = Mage::getSingleton('core/app_emulation');
                    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($_quoteadv->getStoreId());

                    /*
                     * getProcessedTemplate is called inside send()
                     */
                    $template->setData('c2qParams', array('email' => $_quoteadv->getEmail(), 'name' => $_quoteadv->getFirstname()));
                    Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $template));
                    $res = $template->send($_quoteadv->getEmail(), $_quoteadv->getFirstname(), $vars);
                    Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $template, 'result' => $res));

                    // Stop store emulation process
                    $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                }

            }
        }
    }

    /**
     * Function that send the email for a accepted quote
     *
     * @param $quoteId
     */
    public function sendQuoteAccepted($quoteId)
    {
        $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        $acceptedTemplateId = Mage::getStoreConfig('qquoteadv_quote_emails/templates/proposal_accepted', $_quoteadv->getStoreId());

        $disabledEmail = Ophirah_Qquoteadv_Model_System_Config_Source_Email_Templatedisable::VALUE_DISABLED_EMAIL;
        if ($acceptedTemplateId != $disabledEmail){
            if ($acceptedTemplateId) {
                $templateId = $acceptedTemplateId;
            } else {
                $templateId = self::XML_PATH_QQUOTEADV_PROPOSAL_ACCEPTED_EMAIL_TEMPLATE;
            }

            $vars['quote'] = $_quoteadv;
            $vars['store'] = Mage::app()->getStore($_quoteadv->getStoreId());
            $vars['customer'] = Mage::getModel('customer/customer')->load($_quoteadv->getCustomerId());

            $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel($_quoteadv->getStoreId());

            // get locale of quote sent so we can sent email in that language
            $storeLocale = Mage::getStoreConfig('general/locale/code', $_quoteadv->getStoreId());

            if (is_numeric($templateId)) {
                $template->load($templateId);
            } else {
                $template->loadDefault($templateId, $storeLocale);
            }

            $sender = $_quoteadv->getEmailSenderInfo();
            $template->setSenderName($sender['name']);
            $template->setSenderEmail($sender['email']);
            $vars['adminname'] = $sender['name'];

            $bcc = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/bcc', $_quoteadv->getStoreId());
            if ($bcc) {
                $bccData = explode(";", $bcc);
                $template->addBcc($bccData);
            }

            if ((bool)Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc', $_quoteadv->getStoreId())
                && Mage::helper('qquoteadv/licensechecks')->isAllowedSalesBcc()) {
                $template->addBcc(Mage::getModel('admin/user')->load($_quoteadv->getUserId())->getEmail());
            }

            /**
             * Opens the qquote_request.html, throws in the variable array
             * and returns the 'parsed' content that you can use as body of email
             */
            //emulate quote store for corret email design
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($_quoteadv->getStoreId());

            //generate template
            //$template->getProcessedTemplate($vars);

            //Uncomment this to enable attachments in the proposal confirmed email
//            //is pdf or doc attached bools
//            $vars['attach_pdf'] = $vars['attach_doc'] = false;
//
//            //Create pdf to attach to email
//            if (Mage::getStoreConfig('qquoteadv_quote_emails/attachments/pdf', $_quoteadv->getStoreId())) {
//                $_quoteadv->_saveFlag = true;
//
//                //totals need to be collected before generating the pdf (until we save the totals in the database)
//                //$_quoteadv->collectTotals();
//
//                $pdf = Mage::getModel('qquoteadv/pdf_qquote')->getPdf($_quoteadv);
//                $_quoteadv->_saveFlag = false;
//                $realQuoteadvId = $_quoteadv->getIncrementId() ? $_quoteadv->getIncrementId() : $_quoteadv->getId();
//                try {
//                    $file = $pdf->render();
//                    $name = Mage::helper('qquoteadv')->__('Price_proposal_%s', $realQuoteadvId);
//                    $template->getMail()->createAttachment($file, 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $name . '.pdf');
//                    $vars['attach_pdf'] = true;
//                } catch (Exception $e) {
//                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
//                }
//
//            }
//
//            //Check if attachment needs to be sent with email
//            if ($doc = Mage::getStoreConfig('qquoteadv_quote_emails/attachments/doc', $_quoteadv->getStoreId())) {
//                $pathDoc = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'quoteadv' . DS . $doc;
//                try {
//                    $file = file_get_contents($pathDoc);
//                    $mimeType = Mage::helper('qquoteadv/file')->getMimeType($pathDoc);
//
//                    $info = pathinfo($pathDoc);
//                    //$extension = $info['extension'];
//                    $basename = $info['basename'];
//                    $template->getMail()->createAttachment($file, $mimeType, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $basename);
//                    $vars['attach_doc'] = true;
//                } catch (Exception $e) {
//                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
//                }
//            }

            /*
             * getProcessedTemplate is called inside send()
             */
            $template->setData('c2qParams', $sender);
            Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $template));
            $res = $template->send($sender['email'], $sender['name'], $vars);
            Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $template, 'result' => $res));

            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }

    }

    /**
     * Function that handles the quote export to csv for the given quote ids
     *
     * @param $customerCollection
     * @param $filePath
     * @return bool
     * @throws Exception
     */
    public function exportQuotes($customerCollection, $filePath)
    {
        $csv_titles = array(
            "id",
            "timestamp",
            "name",
            "address",
            "zipcode",
            "city",
            "country",
            "phone",
            "email",
            "remarks",
            "product id",
            "product name",
            "product attributes",
            "quantity",
            "product sku"
        );

        $file = fopen($filePath, 'w'); //open, truncate to 0 and create if doesnt exist
        if (!$this->writeCsvRow($csv_titles, $file)) {
            fclose($file);
            throw new Exception($this->__('Could not write CSV'));
        }

        foreach ($customerCollection as $qquote) {
            $quoteId = $qquote->getQuoteId();
            $timestamp = $qquote->getCreatedAt();

            // build name
            $nameArr = array();
            if ($qquote->getPrefix()) {
                array_push($nameArr, $qquote->getPrefix());
            }
            if ($qquote->getFirstname()) {
                array_push($nameArr, $qquote->getFirstname());
            }
            if ($qquote->getMiddlename()) {
                array_push($nameArr, $qquote->getMiddlename());
            }
            if ($qquote->getLastname()) {
                array_push($nameArr, $qquote->getLastname());
            }
            if ($qquote->getSuffix()) {
                array_push($nameArr, $qquote->getSuffix());
            }
            $name = join($nameArr, " ");
            $email = $qquote->getEmail();
            $city = $qquote->getCity();
            $address = rtrim($qquote->getData('address'));
            $postcode = $qquote->getPostcode();
            $tel = $qquote->getTelephone();
            $country = $qquote->getCountryId();
            $remarks = $qquote->getClientRequest();

            $collection = Mage::getModel('qquoteadv/qqadvproduct')->getQuoteProduct($quoteId);

            $basicFields = array(
                $quoteId, $timestamp, $name, $address, $postcode,
                $city, $country, $tel, $email, $remarks
            );

            foreach ($collection as $item) {
                $baseProductId = $item->getProductId();
                $productObj = Mage::getModel('catalog/product')->load($baseProductId);

                $productName = $productObj->getName();
                $productAttributes = "";

                $productObj->setStoreId($item->getStoreId() ? $item->getStoreId() : 1);
                $quoteByProduct = Mage::helper('qquoteadv')->getQuoteItem($productObj, $item->getAttribute());

                foreach ($quoteByProduct->getAllItems() as $_unit) {

                    if ($_unit->getProductId() == $productObj->getId()) {
                        if ($_unit->getProductType() == "bundle") {
                            $_helper = Mage::helper('bundle/catalog_product_configuration');
                            $_options = $_helper->getOptions($_unit);
                        } else {
                            $_helper = Mage::helper('catalog/product_configuration');
                            $_options = $_helper->getCustomOptions($_unit);
                        }

                        foreach ($_options as $option) {
                            if (is_array($option['value'])) $option['value'] = implode(",", $option['value']);
                            $productAttributes .= $option['label'] . ":" . strip_tags($option['value']);
                            $productAttributes .= "|";
                        }
                    }
                }

                $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                $requestItem = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($quote)
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->addFieldToFilter('product_id', $baseProductId)
                    ->addFieldToFilter('quoteadv_product_id', $item->getId())
                    ->getFirstItem();

                $qty = $requestItem->getRequestQty();
                $SKU = $productObj->getSku();

                $productFields = array($baseProductId, $productName, $productAttributes, $qty, $SKU);
                $fields = array_merge($basicFields, $productFields);
                if (!$this->writeCsvRow($fields, $file)) {
                    fclose($file);
                    throw new Exception($this->__('Could not write CSV'));
                }
            }
        }

        fclose($file);
        return true;
    }

    /**
     * Function that writes a csv row imploded to a given file (pointer)
     *
     * @param $row
     * @param $filePointer
     * @return bool
     */
    public function writeCsvRow($row, $filePointer)
    {
        if (is_array($row)) $row = '"' . implode('","', $row) . '"';
        $row = $row . "\n";
        try {
            fwrite($filePointer, $row);
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            return false;
        }
        return true;
    }

    /**
     * Function that generates a random hash of a given length
     *
     * @param int $length
     * @return string
     */
    public function getRandomHash($length = 40)
    {
        $max = ceil($length / 40);
        $random = '';
        for ($i = 0; $i < $max; $i++) {
            $random .= sha1(microtime(true) . mt_rand(10000, 90000));
        }
        return substr($random, 0, $length);
    }

    /**
     * Function that gets a hash to use in a url (for autologin urls)
     *
     * @return string
     */
    public function getUrlHash()
    {
        if ($this->getHash() == "") {
            $hash = $this->getRandomHash();
            $this->setHash($hash);
            $this->save();
        }

        $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
        $hash = sha1($customer->getEmail() . $this->getHash() . $customer->getPasswordHash());
        return $hash;
    }

    /**
     * Get the currency object for the currency that is set on this quote
     *
     * @return null
     */
    public function getQuoteCurrency()
    {
        if (is_null($this->_quoteCurrency)) {
            $this->_quoteCurrency = Mage::getModel('directory/currency')->load($this->getCurrency());
        }
        return $this->_quoteCurrency;
    }

    /**
     * Function that checks if the currency on this quote is different from the base currency
     *
     * @return bool
     */
    public function isCurrencyDifferent()
    {
        return $this->getQuoteCurrency()->getCurrencyCode() != $this->getBaseCurrencyCode();
    }

    /**
     * Function that gets the base currency code of the store that is set on this quote
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return Mage::app()->getStore($this->getStoreId())->getBaseCurrencyCode();//Mage::app()->getBaseCurrencyCode();
    }

    /**
     * Get the currency object for the currency that is set as base currency on the store of this quote
     *
     * @return null
     */
    public function getBaseCurrency()
    {
        if (is_null($this->_baseCurrency)) {
            $this->_baseCurrency = Mage::getModel('directory/currency')->load($this->getBaseCurrencyCode());
        }
        return $this->_baseCurrency;
    }

    /**
     * Function that formats a base price
     *
     * @param $price
     * @return mixed
     */
    public function formatBasePrice($price)
    {
        return $this->formatBasePricePrecision($price, 2);
    }

    /**
     * Function that formats a base price for a given precision
     *
     * @param $price
     * @param $precision
     * @return mixed
     */
    public function formatBasePricePrecision($price, $precision)
    {
        return $this->getBaseCurrency()->formatPrecision($price, $precision);
    }

    /**
     * Function that formats a price and adds brackets if requested
     *
     * @param $price
     * @param bool|false $addBrackets
     * @return mixed
     */
    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    /**
     * Function that formats a price for a given precision and adds brackets if requested
     *
     * @param $price
     * @param $precision
     * @param bool|false $addBrackets
     * @return mixed
     */
    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getQuoteCurrency()->formatPrecision($price, $precision, array(), true, $addBrackets);
    }

    /**
     * Virtual items are not supported on quotes
     *
     * @return int
     */
    public function getVirtualItemsQty()
    {
        return 0;
    }

    /**
     * Add new addresses to quote
     * in database
     */
    public function addNewAddress()
    {
        $helper = Mage::helper('qquoteadv/address');
        // check for existing data
        if ($helper->getAddressCollection($this->getData('quote_id'))) {
            $this->updateAddress();
        } else {
            // Get addresses from quote
            $addresses = $helper->getAddresses($this);
            if ($addresses) {
                foreach ($addresses as $address) {
                    $helper->addAddress($this->getData('quote_id'), $address, true);
                }
            }
        }
    }

    /**
     * Update Quote addresses
     * in database
     */
    public function updateAddress()
    {
        // Update addresses associated to the quote
        Mage::helper('qquoteadv/address')->updateAddress($this);
    }

    /**
     * Function to get the address for a quote based on the given type
     * If no type is given, the type is set by the Magento setting: 'tax/calculation/based_on'
     *
     * @param null $type
     * @return Mage_Core_Model_Abstract|null
     */
    public function getAddress($type = null)
    {
        //if no type is given, get the type from the Magento settings
        if ($type == null) {
            //get 'tax/calculation/based_on' the setting from magento
            $taxCalculationBasedOn = Mage::getStoreConfig('tax/calculation/based_on');

            //if it is not billing, fallback to shipping
            if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING;
            } else {
                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING;
            }
        }

        //check if this address is already loaded (speed improvement)
        if ($this->_address == null || ($this->_address->getData('address_type') != $type)) {
        //if ($this->_address == null){
            $this->_address = Mage::getSingleton('qquoteadv/address');

            //collect the quote addresses
            $addresses = Mage::helper('qquoteadv/address')->buildQuoteAdresses($this, true, false);

            //check for each address if it is the requested type
            foreach ($addresses as $address) {
                if ($address->getData('address_type') == $type) {
                    // Set Address to quote  //first time customer_id is set to the quote / setCustomerId
                    $this->addData($address->getData());

                    // Set Address to address
                    $this->_address->addData($address->getData());

                    break;
                }
            }

            //set this quote to the current address
            $this->_address->setQuote($this);
        }

        return $this->_address;
    }

    /**
     * Function to get only the address(es) object for a quote
     *
     * @param null $type
     * @return mixed
     */
    public function getAddressRaw($type = null){
        //if no type is given, get the type from the Magento settings
        if ($type == null) {
            //get 'tax/calculation/based_on' the setting from magento
            $taxCalculationBasedOn = Mage::getStoreConfig('tax/calculation/based_on');

            //if it is not billing, fallback to shipping
            if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING;
            } else {
                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING;
            }
        }

        //create address model
        $rawAddress = Mage::getModel('qquoteadv/address');

        //collect the quote addresses
        $addresses = Mage::helper('qquoteadv/address')->buildQuoteAdresses($this);

        //check for each if it is the request type
        foreach ($addresses as $address) {
            if ($address->getData('address_type') == $type) {
                // Set Address to address
                $rawAddress->addData($address->getData());
                $rawAddress->setQuote($this);
                return $rawAddress;
            }
        }

        //return empty model if no result is found
        return $rawAddress;
    }

    /**
     * Fix for seperate shipping address
     * with prefix and 'address / street' naming
     * @return string
     */
    public function getShippingStreets()
    {
        return $this->getData('shipping_address');
    }

    /**
     * Retrieve Shipping Address
     * @return object Ophirah_Qquoteadv_Model_Address
     */
    public function getShippingAddress()
    {
        return $this->getAddress(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING);
    }

    /**
     * Retrieve Billing Address as object of 'qquoteadv/address'
     *
     * @return mixed
     */
    public function getShippingAddressRaw()
    {
        return $this->getAddressRaw(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING);
    }

    /**
     * Retrieve Billing Address
     * @return object Ophirah_Qquoteadv_Model_Address
     */
    public function getBillingAddress()
    {
        return $this->getAddress(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING);
    }

    /**
     * Retrieve Billing Address as object of 'qquoteadv/address'
     *
     * @return mixed
     */
    public function getBillingAddressRaw()
    {
        return $this->getAddressRaw(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING);
    }

    /**
     * Retrieve customer address info
     * by type
     *
     * @param string $type
     * @return array
     */
    public function getAddressInfoByType($type)
    {
        return Mage::helper('qquoteadv/address')->getAddressInfoByType($this->getData('quote_id'), $type);
    }

    /**
     * Retrieve quote address collection
     *
     * @return array        // Mage_Sales_Model_Quote_Address
     */
//    public function getAddressesCollection()
//    {
//        if (is_null($this->_addresses)) {
//            // Load only one address
//            if (Mage::getStoreConfig('tax/calculation/based_on') == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
//                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING;
//            } else {
//                $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING;
//            }
//            $this->_addresses[] = $this->getAddress($type);
//
//            // Assign quote to the addresses
//            if ($this->getId()) {
//                foreach ($this->_addresses as $address) {
//                    $address->setQuote($this);
//                }
//            }
//        }
//        return $this->_addresses;
//    }
    /**
     * Retrieve quote address collection
     *
     * @return Ophirah_Qquoteadv_Model_Mysql4_Address_Collection
     */
    public function getAddressesCollection()
    {
        if (is_null($this->_addresses)) {
            $this->_addresses = Mage::getModel('qquoteadv/address')->getCollection()->setQuoteFilter($this->getId());

            // Assign quote to the addresses
            if ($this->getId()) {
                foreach ($this->_addresses as $address) {
                    $address->setQuote($this);
                }
            }
        }
        return $this->_addresses;
    }

    /**
     * Collect Quote Totals
     *
     * @return \Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function collectTotals()
    {
        //minimize the number of totals collect
        if ($this->getTotalsCollectedFlag()) {
            return $this;
        }

        //get 'tax/calculation/based_on' the setting from magento
        $taxCalculationBasedOn = Mage::getStoreConfig('tax/calculation/based_on');

        //if it is not billing, fallback to shipping
        if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
            $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING;
        } else {
            $type = Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING;
        }

        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_before', array($this->_eventObject => $this));

        $addresses = $this->getAllAddresses();
        foreach ($addresses as $address) {
            /** @var $address Ophirah_Qquoteadv_Model_Address */
            $this->setSubtotal(0);
            $this->setBaseSubtotal(0);
            $this->setGrandTotal(0);
            $this->setBaseGrandTotal(0);
            $this->setTaxAmount(0);
            $this->setBaseTaxAmount(0);
            $this->setSubtotalInclTax(0);
            $this->setBaseSubtotalInclTax(0);
            $this->setBaseShippingAmountInclTax(0);
            $this->setShippingAmountInclTax(0);
            $this->setBaseShippingInclTax(0);
            $this->setShippingInclTax(0);
            $this->setShippingAmount(0);
            $this->setBaseShippingAmount(0);
            $this->setShipping(0);
            $this->setBaseShipping(0);

            $this->setSubtotalWithDiscount(0);
            $this->setBaseSubtotalWithDiscount(0);
            $this->setShippingTaxAmount(0);
            $this->setBaseShippingTaxAmount(0);
            $this->setDiscountAmount(0);
            $this->setBaseDiscountAmount(0);
            $this->setDiscountDescription(null);

            $address->setTotalAmount('subtotal', 0);
            $address->setBaseTotalAmount('subtotal', 0);
            $address->setGrandTotal(0);
            $address->setBaseGrandTotal(0);
            $address->setTotalAmount('tax', 0);
            $address->setBaseTotalAmount('tax', 0);
            $address->setSubtotalInclTax(0);
            $address->setBaseSubtotalInclTax(0);
            $address->setBaseShippingInclTax(0);
            $address->setShippingInclTax(0);
            $address->setBaseShippingInclTax(0);
            $address->setShippingInclTax(0);
            $address->setShippingAmount(0);
            $address->setBaseShippingAmount(0);
            $address->setOrgFinalBasePrice(0);
            $address->setOrgBasePrice(0);
            $address->setQuoteBaseCostPrice(0);
            $address->setQuoteFinalBasePrice(0);

            $this->setItemsCount(0);
            $this->setItemsQty(0);
            $this->save();

            //fooman surcharge compatible
            if (!Mage::helper('core')->isModuleEnabled('Fooman_Surcharge')) {
                //Only collect what is needed ( speed improvement )
                if ($taxCalculationBasedOn == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
                    $address->collectTotals();
                } else {
                    if ($address->getData('address_type') == $type) {
                        $address->collectTotals();

                        //sync tax details back to billing
                        if ($type == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING) {
                            $previouslyAppliedTaxes = $address->getAppliedTaxes();
                            foreach ($addresses as $addresSearch) {
                                if ($addresSearch->getData('address_type') == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING) {
                                    $addresSearch->setAppliedTaxes($previouslyAppliedTaxes);
                                }
                            }
                        }
                    }
                }
            } else {
                $address->collectTotals();
            }

            $this->setSubtotal($address->getTotalAmount('subtotal'));
            $this->setBaseSubtotal($address->getBaseTotalAmount('subtotal'));
            $this->setGrandTotal($address->getGrandTotal());
            $this->setBaseGrandTotal($address->getBaseGrandTotal());
            $this->setTaxAmount($address->getTotalAmount('tax'));
            $this->setBaseTaxAmount($address->getBaseTotalAmount('tax'));
            $this->setSubtotalInclTax($address->getSubtotalInclTax());
            $this->setBaseSubtotalInclTax($address->getBaseSubtotalInclTax());
            $this->setBaseShippingAmountInclTax($address->getBaseShippingInclTax());
            $this->setShippingAmountInclTax($address->getShippingInclTax());
            $this->setBaseShippingInclTax($address->getBaseShippingInclTax());
            $this->setShippingInclTax($address->getShippingInclTax());
            $this->setShippingAmount($address->getShippingAmount());
            $this->setBaseShippingAmount($address->getBaseShippingAmount());
            $this->setShipping($address->getShippingAmount());
            $this->setBaseShipping($address->getBaseShippingAmount());

            $this->setSubtotalWithDiscount($address->getSubtotalWithDiscount());
            $this->setBaseSubtotalWithDiscount($address->getBaseSubtotalWithDiscount());
            $this->setShippingTaxAmount($address->getShippingTaxAmount());
            $this->setBaseShippingTaxAmount($address->getBaseShippingTaxAmount());
            $this->setDiscountAmount($address->getDiscountAmount());
            $this->setBaseDiscountAmount($address->getBaseDiscountAmount());
            $this->setDiscountDescription($address->getDiscountDescription());
            $this->setOrgFinalBasePrice($address->getOrgFinalBasePrice());
            $this->setOrgFinalBasePriceInclTax($address->getOrgFinalBasePriceInclTax());
            $this->setOrgBasePrice($address->getOrgBasePrice());
            $this->setQuoteBaseCostPrice($address->getQuoteBaseCostPrice());
            $this->setQuoteFinalBasePrice($address->getQuoteFinalBasePrice());

            $this->checkQuoteAmount($this->getGrandTotal());
            $this->checkQuoteAmount($this->getBaseGrandTotal());
            $this->_totalsCollected = $address;

            //fooman surcharge compatible
            if (!Mage::helper('core')->isModuleEnabled('Fooman_Surcharge')) {
                //if fooman surcharge is not installed, this is safe
                Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
            } else {
                $version = (int)Mage::getConfig()->getNode()->modules->Fooman_Surcharge->version;

                //if ($version > 1 && $version < 3) {
                    //for version 2 this must be disabled
                    //Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
                //}

                if ($version > 2 && $version < 4) {
                    //for version 3 this is safe
                    Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
                }
            }

            //update and return calculated type
            if ($address->getData('address_type') == $type) {
                //$this->updateAddress($this);
                $this->updateAddress();
                $return = $this;
            }
        }
        $this->setTotalsCollectedFlag(true);

        if (isset($return) && !empty($return)) {
            $return->setTotalsCollectedFlag(true);
            return $return;
        }

        return $this;
    }

    /**
     * If fixed Quote Total is given
     * recalculate custom item prices
     *
     * @param array $recalPrice
     * @return bool
     */
    public function recalculateFixedPrice($recalPrice = array())
    {
        // Declare price types
        $recalValue = null;
        $recalType = null;
        $recalPriceTypes = array('fixed' => 1, 'percentage' => 2);

        // Get price type to handle
        foreach ($recalPrice as $k => $v) {
            if ((int)trim($v) !== null && trim($v) !== "") {
                $recalType = $recalPriceTypes[$k];
                $recalValue = (int)$v;
            }
        }

        // Make sure all variables are set
        if ($recalType == null || $recalValue === null || $recalValue === "" || !is_numeric($recalValue)) {
            return false;
        }

        // Collect current Totals
        $currentTotals = $this->getAddress()->getAllTotalAmounts();
        if (!$currentTotals || (!$this->getOrgBasePrice() && !$this->getOrgFinalBasePrice())) {
            return false;
        }

        // Get Base to Quote Rate
        //$b2qRate = $this->getBase2QuoteRate($this->getData('currency'));
        //$b2qRate = $this->getBase2QuoteRate();

        // Get current Items
        $requestItems = Mage::getSingleton('qquoteadv/requestitem')->getCollection()->setQuote($this);

        try {
            if ($requestItems){
                if ($recalType == 1) { // Fixed
                    // Setting variables
                    $itemCount = count($requestItems);
                    $count = 1;
                    $restBasePrice = (float)0;
                    $expectedBasePrice = (float)0;
                    $deltaMax = (float)0.0049; //max number that rounds down to 0 cents
                    $useExpectedPrice = false;

                    //converting isn't required anymore - 5.2.x
                    //$fixedBasePrice = round($recalPrice['fixed'], 2) / $b2qRate;
                    $fixedBasePrice = round($recalPrice['fixed'], 4);

                    //neutralize VAT/TAX difference based on first product in the quote
                    //$store = Mage::app()->getStore($this->getStoreId());
                    // Item Original Price
                    if (Mage::helper('tax')->priceIncludesTax($this->getStoreId())) {
                        //get the tax rate for the default store and remove it
                        $taxCalculation = Mage::getModel('tax/calculation');
                        $customer = $this->getCustomer();
                        if ($customer) {
                            $taxCalculation->setCustomer($customer);
                        }
                        $request = $taxCalculation->getRateOriginRequest($this->getStore());
                        $requestItemCounter = 0;
                        foreach ($requestItems as $item) {
                            $requestItemCounter++;
                            $firstProduct = Mage::getModel('catalog/product')->load($item->getProductId());
                            //avoid dynamic bundle as tax calculation product
                            if ($firstProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                                break;
                            } else {
                                $priceType = $firstProduct->getPriceType();
                                if ($priceType != 0){
                                    //price is not dynamic
                                    break;
                                } else {
                                    if ($requestItemCounter == count($requestItems)) {
                                        $requestItemCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                                            ->addFieldToFilter('quote_id', $this->getQuoteId())
                                            ->addFieldToFilter('product_id', $item->getProductId())
                                            ->addFieldToFilter('request_qty', $item->getRequestQty());

                                        if ($item->getQuoteadvProductId()) {
                                            $requestItemCollection->addFieldToFilter('quoteadv_product_id', $item->getQuoteadvProductId());
                                        }
                                        $quoteCustomPrice = $requestItemCollection->getFirstItem();

                                        //dynamic bundle doesn't have a tax class
                                        //todo: Check if attribute is available on this object
                                        $attribute = unserialize($quoteCustomPrice->getData('attribute'));
                                        foreach ($attribute['bundle_option'] as $key => $bindeOptionIdA) {
                                            if (!is_array($bindeOptionIdA)) {
                                                $bindeOptionIds[] = $bindeOptionIdA;
                                            } else {
                                                $bindeOptionIds = $bindeOptionIdA;
                                            }

                                            foreach ($bindeOptionIds as $bindeOptionId) {
                                                $childId = Mage::getModel('bundle/selection')->load($bindeOptionId)->getData('product_id');
                                                $firstProduct = Mage::getModel('catalog/product')->load($childId);
                                                break;
                                                //TODO: tax class of first bundle item is assumed for all items
                                            }

                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        $taxClassId = $firstProduct->getTaxClassId();
                        $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
                        $fixedBasePrice = $fixedBasePrice / ((100+$percent)/100);

                        //add the tax rate from the current store
                        //$request = $taxCalculation->getRateRequest($this->_address, null, null, $store);
                        //$percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
                        //$fixedBasePrice = $fixedBasePrice * ((100+$percent)/100);
                    }

                    //if available use final base price
                    $orgFinalBasePrice = $this->getOrgFinalBasePrice();
                    if (isset($orgFinalBasePrice) && !empty($orgFinalBasePrice) && ($orgFinalBasePrice != 0)) {
                        $totalOrgRatio = $fixedBasePrice / ($orgFinalBasePrice);
                    } else {
                        $totalOrgRatio = $fixedBasePrice / ($this->getOrgBasePrice());
                    }

                    foreach ($requestItems as $item) {
                        // Last item gets custom price calculated
                        // from difference between fixedTotal and
                        // current custom price subtotal
                        if ($count == $itemCount) {
                            if ($item->getData('request_qty') > 0) {
                                $expectedBasePrice = (float)($fixedBasePrice - $restBasePrice) / $item->getData('request_qty');
                                //$expectedBasePrice = round($expectedBasePrice, 4);
                                $expectedDelta = $expectedBasePrice - ($item->getData('original_price') * $totalOrgRatio);

                                if ($expectedDelta < 0) { // Create positive delta value
                                    $expectedDelta = -1 * $expectedDelta;
                                }
                                // check the expected price is within error margin
                                if ($expectedDelta < $deltaMax) {
                                    $useExpectedPrice = true;
                                }
                            }
                        }

                        $magentoPrecision = 2; //Yes, we round to 2, as Magento doesn't support more in their tax calculator
                        if ($useExpectedPrice === true) {
                            $roundedExpectedBasePrice = round($expectedBasePrice, $magentoPrecision);
                            $item->setData('owner_base_price', ($roundedExpectedBasePrice));
                            $item->setData('owner_cur_price', round($roundedExpectedBasePrice * $this->_dataBaseToQuoteRate, $magentoPrecision));
                        } else {
                            $resultPrice = $item->getData('original_price') * $totalOrgRatio;
                            $roundedResultPrice = round($resultPrice, $magentoPrecision);
                            $item->setData('owner_base_price', $roundedResultPrice);
                            $item->setData('owner_cur_price', round($roundedResultPrice * $this->_dataBaseToQuoteRate, $magentoPrecision));
                        }

                        $restBasePrice += $item->getData('request_qty') * ($item->getData('original_price') * $totalOrgRatio);
                        $count++;
                    }

                    $requestItems->save();
                } elseif ($recalType == 2) { // Percentage
                    if($recalValue > 100){
                        $recalValue = 100;
                    }

                    // Setting variables
                    $totalOrgRatio = (100 - $recalValue) / 100;

                    foreach ($requestItems as $item) {
                        $item->setData('owner_base_price', $item->getData('original_price') * $totalOrgRatio);
                        $item->setData('owner_cur_price', $item->getData('original_price') * $totalOrgRatio * $this->_dataBaseToQuoteRate);
                    }

                    $requestItems->save();

                } else {
                    return false;
                }

            }

        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            return false;
        }

        return true;
    }

    /**
     * Calculate Quote reduction
     * from stored quote data
     * See Ophirah_Qquoteadv_Model_Quote_Total_C2qtotal
     * and Ophirah_Qquoteadv_Model_Observer
     *
     * @return boolean / float reduction
     */
    public function getQuoteReduction()
    {
        $rate = $this->getBase2QuoteRate();
        $quoteFinalPrice = $this->getQuoteFinalBasePrice() * $rate;

        $taxHelper = Mage::helper('tax');
        if ($taxHelper->priceIncludesTax($this->getStoreId())) {
            $orgBasePrice = $this->getOrgFinalBasePriceInclTax(); //already in the current currency
        } else {
            $orgBasePrice = $this->getOrgFinalBasePrice(); //already in the current currency
        }

        $reduction = $orgBasePrice - $quoteFinalPrice;
        if ($reduction > 0) {
            return $reduction;
        }

        return false;
    }

    /**
     * Returns the profit on a quotes based on the final price and cost price
     *
     * @return mixed
     */
    public function getQuoteProfit()
    {
        $rate = $this->getBase2QuoteRate();
        $orgCostPrice = $this->getQuoteBaseCostPrice() * $rate;
        $quoteFinalPrice = $this->getQuoteFinalBasePrice() * $rate;
        $profit = $quoteFinalPrice - $orgCostPrice;

        return $profit;
    }

    /**
     * @return int
     */
    public function getSubtotalOriginal(){
        $subtotalOriginal = $this->getAddress()->getSubtotalOriginal();
        if ($subtotalOriginal > 0) {
            return $subtotalOriginal;
        }else{
            return 0;
        }
    }

    /**
     * @return boolean|array
     *
     */
    public function getAllRequestItemsForCart()
    {
        $returnValue = array();

        if ($this->_requestItems != null) {
            $requestItems = Mage::getSingleton('qquoteadv/requestitem')->getCollection()->setQuote($this);

            foreach ($requestItems as $item) {
                $returnValue[$item->getQuoteadvProductId()] = $item->getId();
            }
        }

        return $returnValue;
    }

    /**
     * Add requested products to the object.
     * addQuoteProductAdvanced() method customized
     * core addProductAdvanced() method
     *
     * @param bool $removeTax (for frontend to backend conversion?)
     * @param bool $forceReload (for frontend to backend conversion?)
     * @param bool $keepOnObject (for frontend to backend conversion?)
     * @return object      //quote items in $this->_requestedItems
     */
    public function getAllRequestItems($removeTax = true, $forceReload = false, $keepOnObject = true)
    {
        if ($this->_requestItems == null || $forceReload) {
            $qqadvproductData = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()->addFieldToFilter('quote_id', array("eq" => $this->getQuoteId()));

            // Get full products objects, with child products, for requested products
            //sum of products weight
            $final_weight = 0;

            foreach ($qqadvproductData as $qqadvproduct) {
                //weight of this product request
                $weight = 0;

                // Load Item prices
                $quoteItems = Mage::getModel('qquoteadv/requestitem')->getCollection()
                    ->addFieldToFilter('quote_id', array("eq" => $qqadvproduct->getQuoteId()))
                    ->addFieldToFilter('request_qty', array("eq" => $qqadvproduct->getQty()))
                    ->addFieldToFilter('quoteadv_product_id', array("eq" => $qqadvproduct->getId()))
                    ->load();

                //remove duplicates
                $quoteItems = $this->_cleanQuoteItems($quoteItems);

                foreach ($quoteItems as $quoteItem) {
                    $product = Mage::getModel("catalog/product")->load($quoteItem->getProductId());
                    $product->setHasOptions($qqadvproduct->getHasOptions());
                    $product->setOptions($qqadvproduct->getOptions());
                    $product->setSkipCheckRequiredOption(true);

                    if ($qqadvproduct->getUseDiscount() == 1) {
                        $noDiscount = 0;
                    } else {
                        if ($this->getSalesrule() != null) {
                            $noDiscount = 0;
                        } else {
                            $noDiscount = 1;
                        }
                    }

                    $attributes = unserialize($qqadvproduct->getAttribute());

                    $calculationPrice = $quoteItem->getOriginalCurPrice();
                    $customPrice = $quoteItem->getOwnerCurPrice();

                    //remove website tax if applied - so that it shows correct in the admin?
                    if ($removeTax && Mage::helper('tax')->priceIncludesTax($this->getStoreId())) {
                        /** @var \Mage_Tax_Model_Calculation $taxCalculation */
                        $taxCalculation = Mage::getModel('tax/calculation');
                        $customer = $this->getCustomer();
                        if ($customer) {
                            $taxCalculation->setCustomer($customer);
                        }
                        $request = $taxCalculation->getRateOriginRequest($this->getStore());

                        //get tax percent
                        $taxClassId = $product->getTaxClassId();
                        $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));

                        //get user tax
                        $customerTaxClass = null;
                        if ($this->getCustomerTaxClassId()) {
                            $userRequest = $taxCalculation->getRateRequest(
                                $this->getShippingAddress(),
                                $this->getBillingAddress(),
                                $this->getCustomerTaxClassId(),
                                $this->getStore()
                            );

                            $percent = $taxCalculation->getRate($userRequest->setProductClassId($taxClassId));
                        }

                        if ($percent > 0) {
                            //Don't remove tax on calculation price
                            //$calculationPrice = ($calculationPrice / (100 + $percent)) * 100;
                            $customPrice = ($customPrice / (100 + $percent)) * 100;
                        }
                    }

                    /** @var Mage_Sales_Model_Quote_Item $item */
                    $item = Mage::getModel('sales/quote_item')
                        ->setQuote($this)
                        ->setProduct($product)
                        ->setQty($quoteItem->getRequestQty())
                        ->setStoreId($this->getStoreId())
                        ->setCalculationPrice($calculationPrice)
                        ->setCustomPrice($customPrice)
                        ->setClientRequest($qqadvproduct->getClientRequest())
                        ->setNoDiscount($noDiscount)
                        ->addOption(array(
                            'code' => 'info_buyRequest',
                            'value' => $qqadvproduct->getAttribute(),
                            'product_id' => $quoteItem->getProductId(),
                            'product' => $product
                        ));

                    //add extra data that only exists in memory
                    $item->setQuoteadvProductId($qqadvproduct->getId());
                    $item->setRequestItemId($quoteItem->getId());

                    //get bundle options
                    if (isset($attributes) && !empty($attributes)) {
                        if (isset($attributes['bundle_option'])) {
                            $item->addOption(array(
                                'code' => 'bundle_selection_ids',
                                'value' => serialize($attributes['bundle_option']),
                                'product_id' => $quoteItem->getProductId(),
                                'product' => $product
                            ));
                        }
                    }

                    //get options
                    if (isset($attributes) && !empty($attributes)) {
                        if (isset($attributes['options'])) {
                            $item->addOption(array(
                                'code' => 'bundle_option_ids',
                                'value' => serialize($attributes['options']),
                                'product_id' => $quoteItem->getProductId(),
                                'product' => $product
                            ));
                        }
                    }

                    //get bundle options childs
                    if (isset($attributes) && !empty($attributes)) {
                        if (isset($attributes['bundle_option'])) {
                            foreach ($attributes['bundle_option'] as $key => $option) {
                                if (!is_array($option)) {
                                    $bundleSelection = Mage::getModel('bundle/selection')->load($option);
                                    $childId = $bundleSelection->getData('product_id');
                                    $childProduct = Mage::getModel('catalog/product')->load($childId);
                                    if (isset($attributes['bundle_option_qty'][$key])) {
                                        $childQty = $attributes['bundle_option_qty'][$key];
                                    } else {
                                        $childQty = $bundleSelection->getSelectionQty();
                                    }

                                    $child = Mage::getModel('sales/quote_item')
                                        ->setQuote($this)
                                        ->setProduct($childProduct)
                                        ->setQty($childQty)
                                        ->setStoreId($this->getStoreId());

                                    $item->addChild($child);
                                    $weight = $weight + ($childProduct->getWeight() * $childQty);

                                } else {
                                    foreach ($option as $opt) {
                                        $bundleSelection = Mage::getModel('bundle/selection')->load($opt);
                                        $childId = $bundleSelection->getData('product_id');
                                        $childProduct = Mage::getModel('catalog/product')->load($childId);
                                        $childQty = 1;

                                        $child = Mage::getModel('sales/quote_item')
                                            ->setQuote($this)
                                            ->setProduct($childProduct)
                                            ->setQty($childQty)
                                            ->setStoreId($this->getStoreId());

                                        $item->addChild($child);
                                        $weight = $weight + ($childProduct->getWeight() * $childQty);
                                    }
                                }
                            }
                        }

                        if (isset($attributes['super_attribute']) && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($attributes['super_attribute'], $product);
                            $childProduct = Mage::getModel('catalog/product')->load($childProduct->getEntityId());

                            $child = Mage::getModel('sales/quote_item')
                                ->setQuote($this)
                                ->setProduct($childProduct)
                                ->setQty('1')
                                ->setStoreId($this->getStoreId());

                            $item->addChild($child);

                            //configurable tax class fix:
                            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                $item->getProduct()->setTaxClassId($childProduct->getTaxClassId());
                            }

                            $weight = $weight + $childProduct->getWeight();
                        }
                    }

                    //check for APO
                    if (Mage::helper('qquoteadv/compatibility_apo')->isApoEnabled()) {
                        //fix APO custom weight p1v2
                        $apoHelper = Mage::helper('qquoteadv/compatibility_apo')->getApoHelper();
                        if ($apoHelper->isWeightEnabled()) {
                            Mage::dispatchEvent('sales_quote_item_set_product', array(
                                'product' => $product,
                                'quote_item' => $item
                            ));
                        }
                    }

                    $items[] = $item;
                    //$weight = $weight + $product->getWeight();

                    //Check for APO
                    if (Mage::helper('qquoteadv/compatibility_apo')->isApoEnabled()) {
                        //fix APO custom weight p2v2
                        $apoHelper = Mage::helper('qquoteadv/compatibility_apo')->getApoHelper();
                        if ($apoHelper->isWeightEnabled()) {
                            $weight = $weight + $item->getWeight();
                        } else {
                            //if APO but weight option is disabled
                            $weight = $weight + $product->getWeight();
                        }
                    } else {
                        //if no APO
                        $weight = $weight + $product->getWeight();
                    }

                    $weight = $weight * $quoteItem->getRequestQty();
                    $final_weight = $final_weight + $weight;
                }
            }

            if (isset($items)) {
                // Set Total Item weight for quote
                $this->_weight = $final_weight;

                if ($keepOnObject) {
                    $this->_requestItems = $items;
                } else {
                    return $items;
                }
            } else {
                if ($keepOnObject) {
                    //no request items, but array is expected
                    $this->_requestItems = array();
                } else {
                    return array();
                }
            }
        }

        return $this->_requestItems;
    }

    /**
     * ================================================================================
     * Cart2Quote Customized Core function: Mage_Sales_Model_Quote->addProductAdvanced()
     * ================================================================================
     *
     * Advanced func to add product to quote - processing mode can be specified there.
     * Returns error message if product type instance can't prepare product.
     *
     * @param mixed $product
     * @param null|float|Varien_Object $request
     * @param null|string $processMode
     * @return Mage_Sales_Model_Quote_Item|string
     */
    public function addQuoteProductAdvanced(Mage_Catalog_Model_Product $product, $request = null, $processMode = null)
    {
        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = new Varien_Object(array('qty' => $request));
        }
        if (!($request instanceof Varien_Object)) {
            Mage::throwException(Mage::helper('sales')->__('Invalid request for adding product to quote.'));
        }

        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $parentItem = null;
        $errors = array();
        $items = array();
        foreach ($cartCandidates as $candidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);
            //C2Q customized _addCatalogQuoteProduct()
            $item = $this->_addCatalogQuoteProduct($candidate, $candidate->getCartQty());
            if ($request->getResetCount() && !$stickWithinParent && $item->getId() === $request->getId()) {
                $item->setData('qty', 0);
            }
            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId()) {
                $item->setParentItem($parentItem);
            }

            /**
             * We specify qty after we know about parent (for stock)
             */
            $item->addQty($candidate->getCartQty());
            $item->removeErrorInfosByParams(Mage_CatalogInventory_Helper_Data::ERROR_QTY);
            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $message = $item->getMessage();
                if (!in_array($message, $errors)) { // filter duplicate messages
                    $errors[] = $message;
                }
            }
        }
        if (!empty($errors)) {
            Mage::throwException(implode("\n", $errors));
        }

        Mage::dispatchEvent('sales_quote_product_add_after', array('items' => $items));

        return $item;
    }

    /**
     * ======================================================================================
     * Cart2Quote Customized Core function: Mage_Sales_Model_Quote->_addCatalogQuoteProduct()
     * ======================================================================================
     *
     * Adding catalog product object data to quote
     *
     * @param   Mage_Catalog_Model_Product $product
     * @param int $qty
     * @return Mage_Sales_Model_Quote_Item
     */
    protected function _addCatalogQuoteProduct(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        $newItem = false;
        // C2Q - customized getQuoteItemByProduct()
        $item = $this->getQuoteItemByProduct($product);
        if (!$item) {
            $item = Mage::getModel('sales/quote_item');
            $item->setQuote($this);
            if (Mage::app()->getStore()->isAdmin()) {
                $item->setStoreId($this->getStore()->getId());
            } else {
                $item->setStoreId(Mage::app()->getStore()->getId());
            }
            $newItem = true;
        }

        /**
         * We can't modify existing child items
         */
        if ($item->getId() && $product->getParentProductId()) {
            return $item;
        }

        $item->setOptions($product->getCustomOptions())
            ->setProduct($product);

        // Add only item that is not in quote already (there can be other new or already saved item
        if ($newItem) {
            $this->addItem($item);
        }

        return $item;
    }

    /**
     * ===============================================================================
     * Cart2Quote Customized Core function: Mage_Sales_Model_Quote->getItemByProduct()
     * ===============================================================================
     *
     * Retrieve quote item by product id
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item || false
     */
    public function getQuoteItemByProduct($product)
    {
        // C2Q customized getAllQuoteItems()
        foreach ($this->getAllQuoteItems() as $item) {
            if ($item->representProduct($product)) {
                return $item;
            }
        }
        return false;
    }

    /**
     * ===============================================================================
     * Cart2Quote Customized Core function: Mage_Sales_Model_Quote->getItemByProduct()
     * ===============================================================================
     * ADDED: excluding products from 'sales/quote_item'
     * ===============================================================================
     *
     * Retrieve quote items array
     *
     * @return array
     */
    public function getAllQuoteItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted() && !$item->getId()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Check if amount exceeds the maximum available number
     *
     * @param $amount
     */
    public function checkQuoteAmount($amount)
    {
        if (!$this->getHasError() && ($amount >= self::MAXIMUM_AVAILABLE_NUMBER)) {
            $this->setHasError(true);
            $this->addMessage(
                Mage::helper('sales')->__('Items maximum quantity or price do not allow checkout.')
            );
        }
    }

    /**
     * Get the customer object from the customer on this quote
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (!$this->_customer instanceof Mage_Customer_Model_Customer) {
            $this->_customer = Mage::getModel('qquoteadv/customer_customer')->load($this->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * Get the group id of the customer on this quote
     *
     * @return mixed
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = parent::getCustomerGroupId();
        if((int)$customerGroupId == Mage_Customer_Model_Group::NOT_LOGGED_IN_ID){
            $customerGroupId = $this->getCustomer()->getGroupId();
        }

        //set it if it isn't set
        if(!$this->getData('customer_group_id')){
            $this->setCustomerGroupId($customerGroupId);
        }

        return $customerGroupId;
    }

    /**
     * Get a request item for a given id
     *
     * @param $id
     * @return mixed
     */
    public function getItemById($id)
    {
        return Mage::getModel('qquoteadv/requestitem')->load($id);
    }

    /**
     * Get the coupon code that is set on this quote
     *
     * @return string
     */
    public function getCouponCode()
    {
        return $this->getData('coupon_code');
    }

    /**
     * Retrieve Full Tax info from quote
     *
     * @return boolean
     */
    public function getFullTaxInfo()
    {
        foreach ($this->getTotals() as $total) {
            if ($total->getCode() == 'tax') {
                $fullInfo = $total->getData('full_info');
                if ($fullInfo) {
                    return $fullInfo;
                }
            }
        }
        return false;
    }

    /**
     * Get the grand total of this quote without tax
     *
     * @return mixed
     */
    public function getGrandTotalExclTax()
    {
        return $this->getGrandTotal() - $this->getTaxAmount();
    }

    /**
     * Get customername formatted
     *
     * @param array $address
     * @param string $prefix
     * @return string
     */
    public function getNameFormatted($address, $prefix = null)
    {
        return Mage::helper('qquoteadv')->getNameFormatted($address, $prefix = null);
    }

    /**
     * Create array from streetdata
     * in case multi line address
     *
     * ## address will be DEPRECATED ##
     *
     * @param array $address
     * @return array
     */
    public function getStreetFormatted($address)
    {
        if (isset($address['street'])) {
            return explode(',', $address['street']);
        } elseif (isset($address['address'])) { // 'address' will be DEPRECATED
            return explode(',', $address['address']);
        }
        return array();
    }

    /**
     * Format City and Zipcode
     *
     * @param array $address
     * @return string
     */
    public function getCityZipFormatted($address)
    {
        $cityZip = '';
        $city = false;
        if (isset($address['city'])) {
            $cityZip .= $address['city'];
            $city = true;
        }
        if (isset($address['postcode'])) {
            if ($city === true) {
                $cityZip .= ', ';
            }
            $cityZip .= $address['postcode'];
        }

        return $cityZip;
    }

    /**
     * Format address by type
     *
     * @param string $type
     * @return array
     */
    public function getAddressFormatted($type = null)
    {
        if ($type == null) {
            return null;
        }

        // Declare variables        
        $return = '';
        $name = '';
        $company = '';
        $street = '';
        $cityZip = '';
        $region = '';
        $country = '';
        $telephone = '';

        // Get address info
        $addressData = $this->getAddressInfoByType($type);
        // Name
        $name = $this->getNameFormatted($addressData->getData());
        // Company
        if ($addressData->getData('company')) {
            $company = $addressData->getData('company');
        }
        // Street
        $preFix = '';
        foreach ($this->getStreetFormatted($addressData->getData()) as $streetLine) {
            $street .= $preFix . $streetLine;
            $preFix = ", ";
        }
        // City and Zipcode
        $cityZip = $this->getCityZipFormatted($addressData->getData());
        //Region
        if ($addressData->getData('region')) {
            $region = $addressData->getData('region');
        } elseif ($addressData->getData('region_id')) {
            $region = Mage::getModel('directory/region')->load($addressData->getData('region_id'))->getName();
        }
        // Country
        $country = Mage::getModel('directory/country')->load($addressData->getData('country_id'))->getName();
        // Telephone
        if ($addressData->getData('telephone')) {
            $telephone = 'T: ' . $addressData->getData('telephone');
        }

        return array('name' => $name,
            'company' => $company,
            'street' => $street,
            'cityzip' => $cityZip,
            'region' => $region,
            'country' => $country,
            'telephone' => $telephone
        );
    }

    /**
     * get the weight of this quote
     *
     * @return int|null
     */
    public function getWeight()
    {
        if ($this->_weight == null) {
            // reset weight
            $this->_weight = 0;
            // weight is set in getAllRequestItems()
            $this->getAllRequestItems();
        }
        return $this->_weight;
    }

    /**
     * Get Total Quote items Qty
     *
     * @return int|float
     */
    public function getItemsQty()
    {
        if ($this->_itemsQty == null) {
            $this->_itemsQty = 0;
            $items = $this->getAllRequestItems();
            foreach ($items as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                $this->_itemsQty += $item->getData('qty');
            }
        }

        return $this->_itemsQty;
    }

    /**
     * check if the shipping type on this quote is a custom type
     *
     * @return bool
     */
    public function getIsCustomShipping()
    {
        if ($this->getShippingType() == "I" || $this->getShippingType() == "O") {
            return true;
        }
        return false;
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getSalesRepresentative()
    {
        if (!$this->hasData('user')) {
            $user = Mage::getModel('admin/user')->load($this->getUserId());
            $this->setData('user', $user);
        }
        return $this->getData('user');
    }

    /**
     * Get sender info for quote
     *
     * @return array
     */
    public function getEmailSenderInfo()
    {
        // Sender from store
        $senderValue = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/sender', $this->getStoreId());
        if (empty($senderValue)) {
            // Default setting
            $senderValue = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/sender', 0);
            // fallback
            if (empty($senderValue)) {
                $admin = Mage::getModel("admin/user")->getCollection()->getData();
                return array(
                    'name' => $admin[0]['firstname'] . " " . $admin[0]['lastname'],
                    'email' => $admin[0]['email'],
                );
            }
        }

        if ($senderValue == 'qquoteadv_sales_representive') {
            return array(
                'name' => $this->getSalesRepresentative()->getName(),
                'email' => $this->getSalesRepresentative()->getEmail()
            );
        }

        $email = Mage::getStoreConfig('trans_email/ident_' . $senderValue . '/email', $this->getStoreId());
        if (!empty($email)) {
            return array(
                'name' => Mage::getStoreConfig('trans_email/ident_' . $senderValue . '/name', $this->getStoreId()),
                'email' => $email
            );
        }

        return array(
            'name' => $senderValue,
            'email' => $senderValue
        );
    }

    /**
     * Get list of available coupons
     *
     * @param $websiteId
     * @param $customerGroup
     * @return array // array with available coupons
     * @internal param $ int || array        // $customerGroup
     */
    public function getCouponList($websiteId = 1, $customerGroup)
    {
        $couponCollection = Mage::getModel('salesrule/rule')->getCollection();
        $couponCollection->addWebsiteGroupDateFilter(
            $websiteId,
            $customerGroup,
            Mage::getModel('core/date')->date('Y-m-d')
        );

        if ($couponCollection) {
            $couponList = null;
            foreach ($couponCollection as $coupon) {
                if ($coupon->getIsActive() && !$coupon->getUseAutoGeneration()) {
                    $couponList[] = $coupon->getData();
                }
            }
            return $couponList;
        }

        return false;
    }

    /**
     * Create options array from coupon list
     *
     * @param $websiteId
     * @param $customerGroup
     * @return array // array with available coupons
     * @internal param $ int || array        // $customerGroup
     */
    public function getCouponOptions($websiteId, $customerGroup)
    {
        $couponList = $this->getCouponList($websiteId, $customerGroup);

        if ($couponList) {
            $return = array();
            $returnCoupons = array();
            $returnRules = array();

            $return[0] = Mage::helper('qquoteadv')->__('-- Select Coupon --');
            foreach ($couponList as $coupon) {
                if (isset($coupon['code']) && !empty($coupon['code'])) {
                    $returnCoupons[$coupon['rule_id']] = trim($coupon['name'] . ' (' . $coupon['code'] . ')');
                } else {
                    $returnRules[$coupon['rule_id']] = trim('* ' . $coupon['name']);
                }
            }

            //make the sorted result
            natcasesort($returnCoupons);
            natcasesort($returnRules);
            $return = array_replace($return, $returnCoupons, $returnRules);

            return $return;
        }

        return false;
    }

    /**
     * Retrieve Coupon name from id
     *
     * @param   int // $couponId
     * @return  string
     */
    public function getCouponNameById($couponId)
    {
        $couponCollection = Mage::getModel('salesrule/rule')->load($couponId, 'rule_id');
        return $couponCollection->getData('name');
    }

    /**
     * Retrieve Coupon code from id
     *
     * @param   int // $couponId
     * @return  string
     */
    public function getCouponCodeById($couponId)
    {
        $couponCollection = Mage::getModel('salesrule/rule')->load($couponId, 'rule_id');
        if ($couponCollection) {
            return $couponCollection->getData('coupon_code');
        } else {
            return false;
        }
    }

    /**
     * Function to set the quoteadv shipping method on this quote
     *
     * @param Ophirah_Qquoteadv_Model_Quoteshippingrate $rateData
     * @return bool
     */
    public function setShippingMethod(Ophirah_Qquoteadv_Model_Quoteshippingrate $rateData = null)
    {
        $success = false;
        if(empty($rateData)) {
            $shippingType = $this->getShippingType();
            $shippingRate = Mage::getModel('qquoteadv/quoteshippingrate');

            if ($shippingType == "I" || $shippingType == "O") {
                $ratePrice = $this->getShippingPrice() / $this->getBase2QuoteRate();
                $methodDescription = Mage::getStoreConfig("carriers/qquoteshiprate/title", $this->getStoreId());
                $methodDescription .= " - " . Mage::getStoreConfig("carriers/qquoteshiprate/name", $this->getStoreId());
                $rateData = $shippingRate
                    ->setData('code', 'qquoteshiprate_qquoteshiprate')
                    ->setData('price', $ratePrice)
                    ->setData('method_description', $methodDescription);
            } else {
                if(is_string($shippingType)) {
                    $methods = $shippingRate->getShippingRatesList($this);
                    if($methods['shippingList']) {
                        foreach($methods['shippingList'] as $shippingRateArray) {
                            foreach($shippingRateArray as $shippingRateArrayData) {
                                $shipping = explode('_',$shippingType);
                                if (!isset($shipping[1])) { //!(count($shipping) > 1)
                                    $shippingType = $shippingType . '_' . $shippingType;
                                }

                                if($shippingRateArrayData['code'] == $shippingType) {
                                    $address = $this->getAddress()->getData('address_id');
                                    $rateData = $shippingRate->getShippingMethodByCode($address, $shippingRateArrayData['code']);
                                    break;
                                }
                            }
                        }
                    }
                }

                if (is_integer($shippingType)) {
                    $rateData = $shippingRate->load($shippingType);
                }
            }
        }

        if ($rateData) {
            $methodDescription = $rateData->getData('method_description');
            if($methodDescription == null){
                $methodDescription = $rateData->getData('carrier_title'). ' - ' . $rateData->getData('method_title');
            }

            /** @var \Ophirah_Qquoteadv_Model_Address $address */
            $address = $this->getAddress(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING)
                ->setData('shipping_method',        $rateData->getData('code'))
                ->setData('shipping_description',   $methodDescription)
                ->setData('base_shipping_amount',   (float)$rateData->getData('price'))
                ->setData('shipping_amount',        (float)$rateData->getData('price') * $this->getBase2QuoteRate())
                ->setData('collect_shipping_rates', true);
            $address->save();

            /** @var \Ophirah_Qquoteadv_Model_Address $address */
            $address = $this->getAddress(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING)
                ->setData('shipping_method',        $rateData->getData('code'))
                ->setData('shipping_description',   $methodDescription)
                ->setData('base_shipping_amount',   (float)$rateData->getData('price'))
                ->setData('shipping_amount',        (float)$rateData->getData('price') * $this->getBase2QuoteRate())
                ->setData('collect_shipping_rates', true);
            $address->save();

            $address = $this->getAddress();

            //set the shipping rate on the quote object
            $this->setData('shipping_method',               $rateData->getData('method'));
            $this->setData('shipping_method_title',         $rateData->getData('method_title'));
            $this->setData('shipping_carrier',              $rateData->getData('carrier'));
            $this->setData('shipping_carrier_title',        $rateData->getData('carrier_title'));
            $this->setData('shipping_code',                 $rateData->getData('code'));
            $this->setData('shipping_description',          $methodDescription);
            $this->setData('address_shipping_description',  $methodDescription);
            $this->setData('address_shipping_method',       $rateData->getData('code'));
            $this->setData('base_shipping_amount',          $address->getData('base_shipping_amount'));
            $this->setData('shipping_amount',               $address->getData('shipping_amount'));
            //?$this->setData('shipping_base_price',          $address->getData('base_shipping_amount'));

            $success = true;
        }
        return $success;
    }

    /**
     * Remove Shipping Method from Quote
     *
     */
    public function unsetShippingMethod()
    {
        // Data to reset
        $resetArray = array(
            'shipping_type',
            'shipping_price',
            'shipping_code',
            'shipping_carrier',
            'shipping_carrier_title',
            'shipping_method',
            'shipping_method_title',
            'shipping_amount',
            'shipping_description',
            'base_shipping_amount',
            'address_shipping_description',
            'address_shipping_method'
        );

        try {
            foreach ($resetArray as $reset) {
                $this->setData($reset, null);
                $this->getAddress(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_BILLING)->setData($reset, null);
                $this->getAddress(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING)->setData($reset, null);
//                $this->getAddress()->save();
//                $this->save();
            }
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }
    }

    /**
     * Get Proposal sent date
     * if proposal is not sent yet,
     * use created at date
     *
     * @return string
     */
    public function getProposalDate()
    {
        if ($this->isValidDate($this->getData('proposal_sent'))) {
            return $this->getData('proposal_sent');
        } else {
            return $this->getData('created_at');
        }
    }

    /**
     * Check if database date has a value
     *
     * @param string $date
     * @return bool
     */
    public function isValidDate($date)
    {
        $return = false;

        if (!is_string($date)) {
            $message = 'Date is not a string';
            Mage::log('Message: ' . $message, null, 'c2q.log', true);
            return false;
        }

        $intDate = (int)$date;
        if ($intDate && strtotime($date)) {
            $return = true;
        }

        return $return;
    }

    /**
     * This function is needed to support Fooman surcharge
     */
    public function getPayment(){
        $salesQuoteId = $this->getCreatedFromQuoteId();

        if(isset($salesQuoteId) && $salesQuoteId != null){
            $salesQuote = Mage::getModel("sales/quote")->loadByIdWithoutStore($salesQuoteId);
            return $salesQuote->getPayment();
        }

        $payment = Mage::getModel('sales/quote_payment');
        $this->addPayment($payment);
        return $payment;
    }

    /**
     * Generate a random password
     *
     * @param int $length
     * @return string           // Random password
     */
    protected function _generatePassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    /**
     * Function to set the status on this quote
     *
     * @param $status
     * @return $this
     */
    public function setStatus($status){
        $statusArray = Mage::getModel('qquoteadv/status')->getOptionArray(true);
        $message = Mage::helper('qquoteadv')->__('Status changed to'). ' ' .'<strong>'.$statusArray[$status].'</strong>';

        if((intval($status) >= 50) && (intval($status) < 60)){
            //proposal state so trail the prices that are send to the customer
            $items = $this->getAllRequestItems();
            $itemsMessage = '';
            foreach ($items as $item){
                $itemsMessage .= $item->getQty().'x '.$item->getName().' for '.$item->getCustomPrice().'<br />';
            }
            $message = $itemsMessage . '<br />' . $message;
        }

        Mage::getModel('qquoteadv/quotetrail')->addMessage($message, $this->getQuoteId());
        $this->setData('status', $status);
        return $this;
    }

    /**
     * Allows you to upload a file to media/qquoteadv/[quote id]/[file name]
     * You need to specify the file name and the form key of the FILE type.
     * @param $fileTitle
     * @param $formDataName
     */
    public function uploadFile($fileTitle, $formDataName)
    {
        if (array_key_exists($formDataName, $_FILES)) {
            if ($_FILES[$formDataName]['error'] == 0) {
                if (empty($fileTitle)) {
                    if (isset($_FILES[$formDataName]['name'])) {
                        $fileTitle = $_FILES[$formDataName]['name'];
                    } else {
                        $fileTitle = 'File_' . $this->getData('increment_id');
                    }
                }

                $extensionCheck = pathinfo($fileTitle);
                if(empty($extensionCheck['extension'])){
                    $extension = pathinfo($_FILES[$formDataName]['name']);
                    $fileTitle = $fileTitle.'.'.$extension['extension'];
                }

                $path = Mage::getModel('qquoteadv/qqadvcustomer')->getUploadDirPath($this->getId());

                $uploader = new Varien_File_Uploader($formDataName);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $uploader->setAllowCreateFolders(true);

                try {
                    $uploader->save($path, $fileTitle);
                } catch (Exception $e) {
                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }

            } else {
                if (is_integer($_FILES[$formDataName]['error'])) {
                    Mage::helper('qquoteadv/file')->getPhpFileErrorMessage($_FILES[$formDataName]['error']);
                } else {
                    $message = "An non-integer is given as error when uploading a file to a quote.";
                    Mage::log('Message: ' .$message, null, 'c2q.log', true);
                }
            }
        }
    }

    /**
     * Removes a single file from the quote
     * @param $fileTitle
     * @return boolean
     */
    public function removeFile($fileTitle){
        $pathToFile = 'media/qquoteadv/'.$this->getId().'/'.$fileTitle;
        $fileRemoved = false;
        try {
            unlink($pathToFile);
            $fileRemoved = true;
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $fileRemoved;
    }

    /**
     * Same function as getFileUploadsToHtml but adds additional CSS for static transactional email template.
     * @return string
     */
    public function getFileUploadsToHtmlStatic(){
        $style = 'font-size:12px; padding:7px 9px 9px 9px; border-left:1px solid #EAEAEA; border-bottom:1px solid #EAEAEA; border-right:1px solid #EAEAEA;';
        return $this->getFileUploadsToHtml($style);
    }

    /**
     * Function made for the transactional emails. Returns table rows of available uploads on the quote.
     * @param string $style
     * @return string
     */
    public function getFileUploadsToHtml($style = ''){
        $pathOfDir = Mage::getModel('qquoteadv/qqadvcustomer')->getUploadDirPath($this->getId());
        $html = "";
        if(file_exists($pathOfDir)) {
            $handle = opendir($pathOfDir);
            if ($handle) {
                while (false !== ($entry = readdir($handle))) {
                    $file_parts = pathinfo($entry);
                    if (!empty($file_parts['extension'])) {
                        $pathOfFile = Mage::getModel('qquoteadv/qqadvcustomer')->getUploadPath(array('dir' => $this->getId(), 'file' => $entry));
                        $html .= '<tr>
                                    <td class="address-details" style="'.$style.'"><a href="'.$pathOfFile. '" target="_blank">' . $file_parts['filename'] . '</a></td>
                                  </tr>';
                    }
                }
                closedir($handle);
            }
        }
        return $html;
    }

    /**
     * Function that returns an array of quotes that have this quote as parrent
     *
     * @return array|bool
     */
    public function getRelationChildRealIds(){
        if(!$this->getId()){
            return array();
        }

        $relationChildRealIds = array();
        $quotes = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
            ->addFieldToFilter('is_quote',  array('eq' => 1))
            ->addFieldToFilter('relation_parent_id', $this->getId());
        foreach ($quotes as $quote){
            $relationChildRealIds[$quote->getId()] = $quote->getIncrementId();
        }

        ksort($relationChildRealIds);
        return $relationChildRealIds;
    }

    /**
     * Function that returns an array with a hash and an id
     *
     * @return array
     */
    public function getCreateHashArray() {
        return array(
            $this->getCreateHash(),
            $this->getIncrementId()
        );
    }

    /**
     * Function that cleans the request item collection of duplicated values
     *
     * @param $quoteItems Ophirah_Qquoteadv_Model_Mysql4_Requestitem_Collection
     * @return mixed
     */
    protected function _cleanQuoteItems($quoteItems)
    {
        //no cleaning required if there is only one option
        if ($quoteItems->count() == 1) {
            return $quoteItems;
        }

        $newestRequestItem = null;
        $highestPricedRequestItem = null;
        $bestRequestItem = null;

        $newestRequestItemId = 0;
        $highestRequestItemPrice = 0;

        //find best items
        foreach ($quoteItems as $requestItem) {
            //find the newest request item
            if ($newestRequestItemId < $requestItem->getId()) {
                $newestRequestItemId = $requestItem->getId();
                $newestRequestItem = $requestItem;
            }

            //find the highest priced request item
            if ($highestRequestItemPrice < $requestItem->getOriginalPrice()) {
                $highestRequestItemPrice = $requestItem->getOriginalPrice();
                $highestPricedRequestItem = $requestItem;
            }
        }

        //default best request item
        $bestRequestItem = $quoteItems->getFirstItem();

        //find better request item
        if ($newestRequestItem != null) {
            $bestRequestItem = $newestRequestItem;
            if (($highestPricedRequestItem != null) && !($newestRequestItem->getOriginalPrice() > 0)) {
                $bestRequestItem = $highestPricedRequestItem;
            }
        }

        //remove bad request items
        foreach ($quoteItems as $requestItem) {
            if ($requestItem->getId() != $bestRequestItem->getId()) {
                $message = 'Found duplicate request items, removed duplicate: ' . $requestItem->getId();
                Mage::log('Warning: ' . $message, null, 'c2q.log');
                $requestItem->delete();
                $quoteItems->removeItemByKey($requestItem->getId());
            }
        }

        return $quoteItems;
    }
}
