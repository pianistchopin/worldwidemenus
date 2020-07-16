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
 * Class Ophirah_Qquoteadv_Helper_Data
 */
final class Ophirah_Qquoteadv_Helper_Data extends Mage_Core_Helper_Abstract
{
    const TYPE_QUOTE_ALLOW  = 'group_allow_quotemode';

    /**
     * @var null|Mage_Sales_Model_Quote
     */
    protected $_virtualQuote = null;

    /**
     * Get Last Quote information from qquote_product table for the particular customer
     * @return quote object
     */
    public function getLatestQuote()
    {
        // get customer quote data for the latest quote he/she has made
        // is_quote = 2 => 2 is for unsubmitted quote, 1 is for submitted quote
        $customerQuoteId = 0;

        // get unsubmitted quote by the customer
        $quoteRemaining = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getId())
            ->addFieldToFilter('is_quote', 2)
            ->setOrder('quote_id', 'desc')
            ->setPageSize(1);
        // getting array from collection
        $quoteRemaining = $quoteRemaining->getData();

        // get latest quote by the customer
        $latestQuote = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getId())
            ->setOrder('quote_id', 'desc')
            ->setPageSize(1);
        // getting array from collection
        $latestQuote = $latestQuote->getData();


        if ($latestQuote != array()) {
            if ($quoteRemaining != array()) {
                if ($quoteRemaining[0]['quote_id'] >= $latestQuote[0]['quote_id']) {
                    $customerQuoteId = $quoteRemaining[0]['quote_id'];
                }
            } else {
                $customerQuoteId = $latestQuote[0]['quote_id'];
            }
        }
        return $customerQuoteId;
    }

    /**
     * Get Product information from qquote_product table
     * @return quote object
     */
    public function getQuote()
    {
        // if the customer is logged in
        $customerQuoteId = 0;
        if (Mage::getSingleton('customer/session')->getId()) {
            $customerQuoteId = $this->getLatestQuote();
        }

        // if the customer has done quote previously
        if ($customerQuoteId > 0) {
            // settting the session quote id with the latest quote done by the customer
            Mage::getSingleton('customer/session')->setQuoteId($customerQuoteId);
        }

        $quoteId = Mage::getSingleton('customer/session')->getQuoteadvId();
        $quoteProduct = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);

        return $quoteProduct;

    }

    /**
     * Get total number of items in quote
     * @return integer total number of items
     */
    public function getTotalQty()
    {
        $totalQty = 0;
        $quoteId = Mage::getSingleton('customer/session')->getQuoteadvId();

        if (!$quoteId){
            return $totalQty;
        }

        $products = $this->getQuote();
        foreach ($products as $value) {
            $totalQty += $value['qty'];
        }
        return $totalQty;
    }

    /**
     * Get total number of products in quote
     * @return integer total number of items
     */
    public function getTotalItems()
    {
        $quoteId = Mage::getSingleton('customer/session')->getQuoteadvId();
        if (!$quoteId){
            return 0;
        }

        $products = $this->getQuote();
        return count($products);
    }

    /**
     * Get the number of items/products bases on the use_qty setting
     *
     * @return int
     */
    public function getLinkQty(){
        if(Mage::getStoreConfig('checkout/cart_link/use_qty') == 0){
            $count = $this->getTotalItems();
        } else {
            $count = $this->getTotalQty();
        }

        return $count;
    }

    /**
     * get Total items text
     * @return string
     */
    public function totalItemsText()
    {
        $count = $this->getLinkQty();

        if ($count == 1) {
            $text = $this->__('My Quote (%s item)', $count);
        } elseif ($count > 0) {
            $text = $this->__('My Quote (%s items)', $count);
        } else {
            $text = $this->__('My Quote');
        }

        return $text;
    }

    /**
     * Checks if Quick Quote is on and allowed
     * @return bool
     */
    public function checkQuickQuote(){
        if(Mage::getStoreConfig('qquoteadv_quote_frontend/catalog/quick_quote_mode') == "1" &&
            Mage::helper('qquoteadv/licensechecks')->isAllowedQuickQuote()){
            return true;
        }
        return false;
    }

    /**
     * Checks if automatic tier prices is on and allowed
     * @return bool
     */
    public function checkAutomaticTierPrices()
    {
        if (Mage::getStoreConfig('qquoteadv_advanced_settings/checkout/display_tierprices') == "1"
            && Mage::helper('qquoteadv/licensechecks')->isAllowedAutomaticTierPrices()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if direct print quote is on and allowed
     * @return bool
     */
    public function checkDirectPrint()
    {
        return Mage::helper('qquoteadv/licensechecks')->isAllowedQuoteDirectPrint()
        && $printEnabled = Mage::getStoreConfig('qquoteadv_quote_frontend/shoppingcart_quotelist/enable_quote_direct_print')
            && $loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Checks if Quick Quote is on and allowed in the layout xml
     * @param fileName
     * @return bool | string
     */
    public function canUseQuickQuoteAssets($fileName){
        if($this->checkQuickQuote()){
            return $fileName;
        }
        return false;
    }

    /**
     * Get product customize options
     * @param object $product
     * @param array $attribute
     * @return array || false
     */
    public function getSimpleOptions($product, $attribute)
    {
        if (is_array($attribute) && array_key_exists('options', $attribute)) {
            if (is_array($attribute['options'])) {
                $options = array();
                foreach ($attribute['options'] as $k => $v) {
                    if ($v != '') {
                        if (!is_object($product->getOptionById($k))) continue;
                        $label = $product->getOptionById($k)->getTitle();
                        $values = $product->getOptionById($k)->getValues();

                        if (is_array($v)) {
                            $finalValue = "";
                            foreach ($v as $sk => $sv) {
                                try {
                                    if ($product->getOptionById($k)->getType() == "date") {
                                        $value = Mage::helper('core')->formatDate($sv, 'medium', false);
                                    } else if ($product->getOptionById($k)->getType() == "date_time") {
                                        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                                        $value = Mage::app()->getLocale()->date($v['date_internal'], Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)->toString($format);
                                    } else if ($product->getOptionById($k)->getType() == "file") {
                                        $value = $v['title'];
                                    } else {
                                        $value = $values[$sv]->getTitle();
                                    }

                                    if ($product->getOptionById($k)->getType() == "image" || $product->getOptionById($k)->getType() == "imagecheckbox") {
                                        $collection = Mage::getModel("imageoption/imageoption")->getCollection()->getTitleById($value);
                                        $imageName = "";
                                        $title = "";
                                        if ($collection->getSize() > 0) {
                                            foreach ($collection as $col) {
                                                $imageName = $col->getFilename();
                                                $title = $col->getTitle();
                                                break;
                                            }
                                            $storex = Mage::getStoreConfig("checkout/optionscust/imageoptionx");
                                            $storey = Mage::getStoreConfig("checkout/optionscust/imageoptiony");
                                            $showTitle = Mage::getStoreConfig("checkout/optionscust/titleshow");
                                            if ($storey == "") {
                                                $url = Mage::helper("imageoption")->getResizedUrl($imageName, $storex);
                                            } else {
                                                $url = Mage::helper("imageoption")->getResizedUrl($imageName, $storex, $storey);
                                            }

                                            if ($showTitle == '1') {
                                                $finalValue .= '<img src="' . $url . '"/><br/><span class="cart-image-option-title">' . $title . '</span>';
                                            } else {
                                                $finalValue .= '<img src="' . $url . '"/><br/>';
                                            }
                                        }
                                    } else {
                                        $type = $product->getOptionById($k)->getType();
                                        if ($type == "date" || $type == "file" || $type == "date_time") {
                                            $finalValue = $value;
                                        } else {
                                            if(empty($options[$label])){
                                                $options[$label] = $product->getOptionById($k)->getValueById($sv)->getTitle().PHP_EOL;
                                            } else {
                                                $options[$label] .= $product->getOptionById($k)->getValueById($sv)->getTitle().PHP_EOL;
                                            }
                                        }
                                    }

                                } catch (Exception $e) {
                                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                                }
                            }

                            //foreach
                            if(empty($options[$label])){
                                $options[$label] = $finalValue;
                            }
                        } else {
                            try {
                                if (isset($values[$v]) && is_object($values[$v])) {
                                    $value = $values[$v]->getTitle();
                                } else {
                                    $value = $v; //#text field
                                }

                                if ($product->getOptionById($k)->getType() == "image" || $product->getOptionById($k)->getType() == "imagecheckbox") {

                                    $collection = Mage::getModel("imageoption/imageoption")->getCollection()->getTitleById($value);
                                    $imageName = "";
                                    $title = "";
                                    if ($collection->getSize() > 0) {
                                        foreach ($collection as $col) {
                                            $imageName = $col->getFilename();
                                            $title = $col->getTitle();
                                            break;
                                        }

                                        $storex = Mage::getStoreConfig("checkout/optionscust/imageoptionx");
                                        $storey = Mage::getStoreConfig("checkout/optionscust/imageoptiony");
                                        $showTitle = Mage::getStoreConfig("checkout/optionscust/titleshow");
                                        if ($storey == "") {
                                            $url = Mage::helper("imageoption")->getResizedUrl($imageName, $storex);
                                        } else {
                                            $url = Mage::helper("imageoption")->getResizedUrl($imageName, $storex, $storey);
                                        }

                                        if ($showTitle == '1') {
                                            $value = '<img src="' . $url . '"/><br/><span class="cart-image-option-title">' . $title . '</span>';
                                        } else {
                                            $value = '<img src="' . $url . '"/>';
                                        }

                                    } else {
                                        $value = "";
                                    }
                                }

                                $options[$label] = $value;
                            } catch (Exception $e) {
                                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                            }
                        }
                    }
                }

                return $options;
            }

            return false;
        }

        return null;
    }

    /**
     * Function that handles the resizing of an image
     *
     * @param $imageUrl
     * @param $imageResized
     * @param int $width
     * @param int $height
     */
    public function resizeImage($imageUrl, $imageResized, $width = 80, $height = 80)
    {
        if (!file_exists($imageResized) && file_exists($imageUrl)) {
            $imageObj = new Varien_Image($imageUrl);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepFrame(FALSE);
            $imageObj->resize($width, $height);
            $imageObj->save($imageResized);
        }
    }

    /**
     * Function that returns the status of a give status id
     *
     * @param $id
     * @return null
     */
    public function getStatus($id)
    {
        $params = Mage::getSingleton('qquoteadv/status')->getOptionArray();

        if(isset($params[$id])){
            return $params[$id]; //$status = $params[$id];
        }
        return null;
    }

    /**
     * Function that returns the exact table name of a given table name
     *
     * @param $tablename
     * @return string
     */
    public function getTableName($tablename)
    {
        $return = "";
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $results = $write->query("show tables;");

        $found = false;
        while ($line = $results->fetch()) {
            if (!$found) {
                foreach ($line as $name => $var) {
                    if (strpos($var, $tablename) === strlen($var) - strlen($tablename)) {
                        $return = $var;
                        $found = true;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Function that verifies if an email already exists in the database
     *
     * @param $email
     * @return bool
     */
    public function userEmailAlreadyExists($email)
    {
        $return = false;
        $customer = Mage::getModel('customer/customer');

        $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

        if ($customer->getId()) {
            $return = true;
        }

        return $return;
    }

    /**
     * Return quote shipping price by quote id
     *
     * @param $quoteId
     * @return float
     */
    public function getQquoteShipPriceById($quoteId)
    {
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        return $quote->getShippingPrice();
    }

    /**
     * Return quote shipping base price by quote id
     *
     * @param $quoteId
     * @return float
     */
    public function getQquoteBaseShipPriceById($quoteId)
    {
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        return $quote->getShippingPrice() / $quote->getBaseToQuoteRate();
    }

    /**
     * Return quote shipping type by quote id
     *
     * @param string $quoteId
     * @return float
     */
    public function getShipTypeByQuote($quoteId = '')
    {
        if (empty($quoteId)) {
            $quoteId = $this->getProposalQuoteId();
        }

        if ($quoteId) {
            $qquote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
            $type = $qquote->getShippingType();
            if ($type) {
                return $type;
            }
        }
        return '';
    }


    /**
     * Return quote id when client made confirmation and
     *
     * @return int
     */
    public function getProposalQuoteId()
    {
        return Mage::getSingleton('core/session')->proposal_quote_id;
    }

    /**
     * Is set quote shipping price for quote
     *
     * @return bool
     */
    public function isSetQuoteShipPrice()
    {
        $result = false;

        $quoteId = $this->getProposalQuoteId();
        if ($quoteId) {
            $quoteShipPrice = $this->getQquoteShipPriceById($quoteId);
            //0.00 is also price
            if ($quoteShipPrice > -1)
                $result = true;
        }

        return $result;
    }

    /**
     * Return price for selected attributes by configurable product
     *
     * @param int $productId
     * @param string $selectedAttributes
     * @return float
     */
    public function getConfigurableFinalPrice($productId, $selectedAttributes)
    {
        $price = 0;

        /**
         * Cusomer selected attributesgetConfigurableFinalPrice
         */
        $selectedAtrb = unserialize($selectedAttributes);

        /**
         * Load Product to get Super attributes
         */
        $product = Mage::getModel("catalog/product")->load($productId);
        /**
         * Get Configurable Type Product Instace and get Configurable attributes collection
         */
        $attributeCollection = $product->getTypeInstance()->getConfigurableAttributes();
        $options = array();
        $allProducts = $product->getTypeInstance()->getUsedProducts(null, $product->getId());

        foreach ($allProducts as $p) {
            //if ($p->isSaleable()) {
            $productId = $p->getId();

            foreach ($attributeCollection as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeValue = $p->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttribute->getId()])) {
                    $options[$productAttribute->getId()] = array();
                }

                if (!isset($options[$productAttribute->getId()][$attributeValue])) {
                    $options[$productAttribute->getId()][$attributeValue] = array();
                }
                $options[$productAttribute->getId()][$attributeValue][] = $productId;
            }

            //}
        }

        foreach ($attributeCollection as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();

            $optionPrices = array();
            $prices = $attribute->getPrices(); //print_r($prices);
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }

                    if (isset($selectedAtrb['super_attribute']) && $value['value_index'] != $selectedAtrb['super_attribute'][$attributeId]) {
                        continue;
                    }

                    $optionPrices[] = $this->_preparePrice($value['pricing_value'], $value['is_percent']);

                }
            }

        }
        if (count($optionPrices)) {
            $price = array_sum($optionPrices);
        }

        return $price;
    }

    /**
     * Validating of super product option value
     *
     * @param $attributeId
     * @param array $value
     * @param array $options
     * @return bool
     * @internal param array $attribute
     */
    protected function _validateAttributeValue($attributeId, &$value, &$options)
    {
        if (isset($options[$attributeId][$value['value_index']])) {
            return true;
        }

        return false;
    }

    /**
     * Function that prepares a price for viewing
     * Takes a persent option into account
     *
     * @param $price
     * @param bool|false $isPercent
     * @return int
     */
    protected function _preparePrice($price, $isPercent = false)
    {
        if ($isPercent && !empty($price)) {
            $price = $this->getProduct()->getFinalPrice() * $price / 100;
        }

        return $this->_convertPrice($price, true);
    }

    /**
     * Converts and rounds a price to a given store
     *
     * @param $price
     * @param bool|false $round
     * @return int
     */
    protected function _convertPrice($price, $round = false)
    {
        if (empty($price)) {
            return 0;
        }

        $price = Mage::app()->getStore()->convertPrice($price);
        if ($round) {
            $price = Mage::app()->getStore()->roundPrice($price);
        }

        return $price;
    }

    /**
     * Getter for a virtual quote object
     *
     * @param $quote
     * @param $storeId
     * @param $reUseObject
     * @return false|\Mage_Sales_Model_Quote
     */
    public function getVirtualQuoteObject($quote, $storeId, $reUseObject = false)
    {
        //check if we can reuse an object from memory
        if ($this->_virtualQuote !== null && $reUseObject) {
            return $this->_virtualQuote;
        }

        $virtualQuote = Mage::getModel('sales/quote');
        $virtualQuote->setStoreId($storeId);

        //set the customer session group on the virual quote (only in frontend)
        $session = Mage::getSingleton("customer/session");
        if (isset($session) && !Mage::app()->getStore()->isAdmin()) {
            $customer = $session->getCustomer();
            if ($customer) {
                $virtualQuote->setCustomerGroupId($customer->getGroupId());
                $virtualQuote->assignCustomer($customer);
            }
        }

        if (count($virtualQuote->getAddressesCollection()) > 0) {
            $virtualQuote->getBillingAddress();
            $virtualQuote->getShippingAddress();
        } elseif (isset($quote)) {
            $shippingAddress = $quote->getShippingAddress();
            $data = array('country_id' => $shippingAddress->getData('country_id'),
                'region_id' => $shippingAddress->getData('region_id'));
            $virtualQuote->getBillingAddress()->addData($data);
            $virtualQuote->getShippingAddress()->addData($data);

            //set the customer group id of the quote on the virtual quote
            if ($quote->getCustomerGroupId()) {
                $virtualQuote->setCustomerGroupId($quote->getCustomerGroupId());
            }
        }

        if (!$this->hasTaxAddressDetails($virtualQuote)) {
            $country = Mage::getStoreConfig('tax/defaults/country', $storeId);
            $region = Mage::getStoreConfig('tax/defaults/region', $storeId);

            $data = array('country_id' => $country, 'region_id' => $region);
            $virtualQuote->getBillingAddress()->addData($data);
            $virtualQuote->getShippingAddress()->addData($data);
        }

        $this->_virtualQuote = $virtualQuote;

        return $virtualQuote;
    }

    /**
     * Check if address has country and state set for tax calculation
     *
     * @param $virtualQuote
     * @return bool
     */
    public function hasTaxAddressDetails($virtualQuote)
    {
        $hasDetails = false;
        $billingAddress = $virtualQuote->getBillingAddress();
        $shippingAddress = $virtualQuote->getShippingAddress();

        if (isset($billingAddress, $shippingAddress)) {
            $billingCountry = $billingAddress->getData('country_id');
            //$billingRegion = $billingAddress->getData('region_id');
            $shippingCountry = $shippingAddress->getData('country_id');
            //$shippingRegion = $shippingAddress->getData('region_id');

            //if (isset($billingCountry, $billingRegion, $shippingCountry, $shippingRegion)) {
            if (isset($billingCountry, $shippingCountry)) {
                $hasDetails = true;
            }
        }

        return $hasDetails;
    }

    /**
     * Function that adds an item to an virtual quote
     *
     * @param Mage_Catalog_Model_Product|null $product
     * @param null $attributes
     * @param null $quote
     * @param null $quoteProduct
     * @param bool $originalPrice
     * @param bool $skipOptions
     * @param bool $reUseObject
     * @return false|Mage_Core_Model_Abstract
     */
    public function addItemToVirtualQuote(
        Mage_Catalog_Model_Product $product = null,
        $attributes = null,
        $quote = null,
        $quoteProduct = null,
        $originalPrice = false,
        $skipOptions = true,
        $reUseObject = false
    ) {
        //prepare the product
        $product = $this->_prepareProductForVirtualQuote($product, $quoteProduct, $originalPrice, $skipOptions);
        if (!$product) {
            return false;
        }

        //prepare the product parameters
        $productParams = $this->_prepareProductParamsForVirtualQuote($attributes, $quoteProduct);
        if (!$productParams) {
            return false;
        }

        //get store ID from product
        if (isset($quoteProduct) && !empty($quoteProduct)) {
            $storeId = $quoteProduct->getStoreId();
        } else {
            $storeId = $product->getStockItem()->getStoreId();
        }

        //get a virtual quote object
        /** @var \Mage_Sales_Model_Quote $virtualQuote */
        $virtualQuote = $this->getVirtualQuoteObject($quote, $storeId, $reUseObject);

        try {
            //add item to the quote
            $item = $virtualQuote->addProductAdvanced($product, $productParams);
            $this->prepareCustomOptionImage($quoteProduct, $item);

            //set this new mage quote item to the c2q quote item
            if ($quoteProduct !== null) {
                if ($item->getParentItem()) {
                    $quoteProduct->setQuoteItem($item->getParentItem());
                } else {
                    $quoteProduct->setQuoteItem($item);
                }
            }

            return $virtualQuote;
        } catch (Exception $e) {
            //we receiving exception when product is out of stock
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            return $virtualQuote;
        }
    }

    /**
     * Validation of super product option
     *
     * @param array $info
     * @return boolean
     */
    protected function _validateAttributeInfo(&$info)
    {
        if (count($info['options']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get country name by country code
     * @param string $countryCode
     * @return string country name
     */
    public function getCountryName($countryCode)
    {
        return Mage::getModel('directory/country')->load($countryCode)->getName();
    }

    /**
     * Get region name by region code
     * @param string $regionCode
     * @return string region name
     */
    public function getRegionName($regionCode)
    {
        return Mage::getModel('directory/region')->load($regionCode)->getName();
    }

    /**
     * Check if Cart2Quote Quotation is enabled in the settings for the current store
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig('qquoteadv_general/quotations/enabled', Mage::app()->getStore()->getStoreId());
    }

    /**
     * In case quote proposal will be confirmed then quote items will be removed to shopping cart with restriction to
     * modify:
     *  -  qty per each item
     *  -  proposal price and items
     * So will be used param to check is current confirmation mode or not with quote proposal
     * @param null $checkout
     * @return bool
     */
    public function isActiveConfirmMode($checkout = null)
    {
        $return = false;
        $value = Mage::getSingleton('core/session')->qquote_in_confirmation_mode;
        if (!empty($value) && (Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/quoteconfirmation') == 1 || $checkout === true)) {
            $return = true;
        }

        return $return;
    }

    /**
     * Function that sets or un-sets the confirm mode based on the given value
     *
     * @param $val
     * @return $this
     */
    public function setActiveConfirmMode($val)
    {
        if (isset($val)){
            Mage::getSingleton('core/session')->qquote_in_confirmation_mode = $val;
            return $this;
        }

        Mage::getSingleton('core/session')->qquote_in_confirmation_mode = null;
        return $this;
    }

    /**
     * Returns an array of quotable items names
     *
     * @return array
     */
    public function getQuotableItems()
    {
        $items = array();

        /** @var \Mage_Sales_Model_Quote_Item $_item */
        foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems() as $_item) {
            $allowed_to_quotemode = Mage::getModel("catalog/product")
                ->load($_item->getProductId())
                ->getAllowedToQuotemode();

            /**
             * Add support for OrganicInternet_SimpleConfigurableProducts
             */
            $ignore = false;
            if (Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')) {
                $infoBuyRequest = $_item->getOptionByCode('info_buyRequest');
                $attibutes = unserialize($infoBuyRequest->getValue());
                if (isset($attibutes['cpid'])) {
                    $ignore = true;
                }
            }

            if (!$allowed_to_quotemode && !$ignore) {
                $items[] = $_item->getName();
            }
        }

        return $items;
    }

    /**
     * Calculated the options price (check if it adds price fixed or percentual)
     *
     * @param $price
     * @param string $price_type
     * @param $optionPrice
     * @return float
     */
    protected function _prepareOptionPrice($price, $price_type = 'fixed', $optionPrice)
    {
        if ($price_type == 'fixed') {
            return $optionPrice;
        } else {
            if ($price > 0) {
                return $optionPrice * $price / 100;
            }
        }

        return $optionPrice;
    }

    /**
     * Check if given url is a valid http(s) url
     *
     * @param $value
     * @return bool
     */
    public function isValidHttp($value)
    {
        if (!preg_match('#^{{((un)?secure_)?base_url}}#', $value)) {
            $parsedUrl = parse_url($value);
            if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the full url for a give url
     *
     * @param $path
     * @return string
     */
    public function getFullPath($path)
    {
        if (self::isValidHttp($path)) {
            return $path;
        }

        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $path;
    }

    /**
     * Make a quote item from a given product
     *
     * @param Mage_Catalog_Model_Product|null $product
     * @param $attributes
     * @param null $quote
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct|null $quoteProduct
     * @param bool|false $originalPrice
     * @param bool|true $skipOptions
     * @return mixed
     */
    public function getQuoteItem(
        Mage_Catalog_Model_Product $product = null,
        $attributes = null,
        $quote = null,
        $quoteProduct = null,
        $originalPrice = false,
        $skipOptions = true
    ) {
        if ($quoteProduct === null || ($quoteProduct instanceof Ophirah_Qquoteadv_Model_Qqadvproduct)) {
            //add item to the virtual quote
            $virtualQuote = $this->addItemToVirtualQuote(
                $product,
                $attributes,
                $quote,
                $quoteProduct,
                $originalPrice,
                $skipOptions,
                false
            );
        } else {
            if ($quoteProduct instanceof Ophirah_Qquoteadv_Model_Mysql4_Qqadvproduct_Collection) {
                //add items to the virtual quote
                foreach ($quoteProduct as $quoteProductItem) {
                    //add item to the virtual quote
                    $virtualQuote = $this->addItemToVirtualQuote(
                        $product,
                        $attributes,
                        $quote,
                        $quoteProductItem,
                        $originalPrice,
                        $skipOptions,
                        true
                    );
                }
            }
        }

        //collect totals
        try {
            //If virtualQuote == null, collectTotals() causes an error that can't be caught
            if ($virtualQuote == null) {
                return null;
            }
            $virtualQuote->collectTotals();
            return $virtualQuote;
        } catch (Exception $e) {
            //we receiving exception when product is out of stock
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            return $virtualQuote;
        }
    }

    /**
     * Adds the Option ID for the following URL:
     * /sales/download/downloadCustomOption/id/<ID>/
     *
     * @param $quoteProduct
     * @param $item
     */
    protected function prepareCustomOptionImage($quoteProduct, $item)
    {
        if ($quoteProduct && $item instanceof Mage_Sales_Model_Quote_Item && is_array($item->getOptions())) {
            if ($item->getParentItem()) {
                $this->prepareCustomOptionImage($quoteProduct, $item->getParentItem());
            }

            foreach ($item->getOptions() as $option) {
                $optionSelector = 'option_';
                $optionCode = $option->getCode();
                if (strpos($optionCode, $optionSelector) === false) {
                    continue;
                }

                $optionId = substr($optionCode, strlen($optionSelector));
                if (!is_numeric($optionId)) {
                    continue;
                }

                $option->setId($this->setupOptionImageId($quoteProduct->getId(), $optionId));
            }
        }
    }

    /**
     * Generates a ID for the downloadCustomOption link
     *
     * @param $quoteProductId
     * @param $optionId
     * @return string
     */
    protected function setupOptionImageId($quoteProductId, $optionId)
    {
        $optionIdData = array(
            'product' => $quoteProductId,
            'option'  => $optionId
        );

        return base64_encode(json_encode($optionIdData));
    }

    /**
     * DEPRECATED
     *
     * Function that returns the result of a quote if a given product would be added
     *
     * @param Mage_Catalog_Model_Product $product
     * @param $attributes
     * @param null $quote
     * @return null
     */
    public function getQuoteItem2(Mage_Catalog_Model_Product $product, $attributes, $quote = null)
    {

        $product->setSkipCheckRequiredOption(true);
        if ($quote != null) {
            $quote2 = clone $quote;
            $items = $quote2->getAllItems();
            foreach ($items as $item) {
                // Product configuration is same as in other quote item
                $quote2->removeItem($item->getId());

            }

            $productParams = new Varien_Object(unserialize($attributes));


            try {
                $quote2->addProductAdvanced($product, $productParams);
                $quote2->collectTotals();
                return $quote2;
            } catch (Exception $e) {
                //we receiving exception when product is out of stock
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                return $quote2;
            }
        }

        return null;
    }

    /**
     * Get the customer name formated from the address
     *
     * @param array $address
     * @param string $prefix
     * @return string
     */
    public function getNameFormatted($address, $prefix = null)
    {
        $name = '';
        if ($address[$prefix . 'prefix']) {
            $name .= $address[$prefix . 'prefix'] . ' ';
        }

        $name .= $address[$prefix . 'firstname'] . ' ';

        if ($address[$prefix . 'middlename']) {
            $name .= $address[$prefix . 'middlename'] . ' ';
        }

        $name .= $address[$prefix . 'lastname'];

        if ($address[$prefix . 'suffix']) {
            $name .= ' ' . $address[$prefix . 'suffix'];
        }

        return trim($name);
    }

    /**
     * ### DEPRECATED, USE getNameFormatted()  ###
     * Get customer name from
     * shippingaddress
     *
     * @param array $quote
     * @return string
     */
    public function getShippingName($quote)
    {
        return $this->getNameFormatted($quote, 'shipping_');
    }

    /**
     * ### DEPRECATED, USE USE getNameFormatted() ###
     * Get customer name from
     * billingaddress
     *
     * @param array $quote
     * @return string
     */
    public function getBillingName($quote)
    {
        return $this->getNameFormatted($quote);
    }

    /**
     * Calculate price base on qty and product attributes
     * @param int $quoteProductId - table primary key
     * @param int|type $qty
     * @param bool $currencyCode
     * @param bool $originalPrice
     * @param bool $skipCheckRequiredOption
     * @return null
     */
    public function _applyPrice($quoteProductId, $qty = 1, $currencyCode = false, $originalPrice = false, $skipCheckRequiredOption = true)
    {
        $qty = $this->_tofloat($qty);
        $quoteProduct = Mage::getModel('qquoteadv/qqadvproduct')->load($quoteProductId);
        if ($quoteProduct) {
            $storeId = $quoteProduct->getStoreId();
            if (!$storeId) {
                $storeId = Mage::app()->getStore()->getStoreId();
            }

            if($quoteProduct->getProductId()){
                $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($quoteProduct->getProductId());
            } else {
                return null;
            }

            $_product->setSkipCheckRequiredOption($skipCheckRequiredOption);
            $params = unserialize($quoteProduct->getAttribute());
            $params['qty'] = $qty;

            if ($_product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $origPrice = Mage::getModel('qquoteadv/bundle')->getOriginalPrice($_product, $params);

                return $this->getCurrencyPrice($currencyCode, $storeId, $origPrice);
            }

            if ($originalPrice) {
                $quoteByProduct = $this->getQuoteItem($_product, serialize($params), null, null, true);
            } else {
                $quoteByProduct = $this->getQuoteItem($_product, serialize($params), null, null, false, false);
            }

            if ($quoteByProduct && !$quoteByProduct->getHasError()) {

                if (Mage::helper('tax')->priceIncludesTax($storeId)){
                    $basePrice = $quoteByProduct->getBaseGrandTotal();
                } else {
                    $basePrice = $quoteByProduct->getBaseSubtotal();
                }

                //$origPrice = $basePrice / ($qty > 0 ? $qty : 1);
                $origPrice = $basePrice / $quoteByProduct->getItemsQty();
            }
            if (!isset($origPrice) || ($origPrice == null) || $origPrice == 0 || $qty < 1) {
                $origPrice = $_product->getFinalPrice();
            }

            return $this->getCurrencyPrice($currencyCode, $storeId, $origPrice);
        }

        return null;
    }

    /**
     * Returns the order(s) that are created from a given store and from a give quote
     *
     * @param $quote_id
     * @param $store_id
     * @return array|null
     */
    public function getOrderByC2Q($quote_id, $store_id)
    {
        $_collection = Mage::getModel('sales/order')->setStoreId($store_id)->getCollection()->addFieldToFilter("c2q_internal_quote_id", $quote_id);
        if ($_collection->getSize() > 0) {
            $data = array();

            foreach ($_collection as $order) {
                $data[$order->getId()] = $order->getIncrementId(); //$order->getRealOrderId()); //
            }
            return $data;
        }

        return null;
    }


    /**
     * Get the admin email based on a given admin user id
     *
     * @param $id
     * @return mixed
     */
    public function getAdminEmail($id)
    {
        $uid = Mage::getModel('qquoteadv/qqadvcustomer')->load($id)->getData('user_id');
        $model = Mage::getModel('admin/user')->load($uid);
        if (!$model->getId()) {
            //return current session user email address
            return Mage::getSingleton('admin/session')->getUser()->getEmail();
        }

        return $model->getEmail();
    }


    /**
     * Get the full name of the admin based on a given admin user id
     *
     * @param $id
     * @return null|string
     */
    public function getAdminName($id)
    {
        if (!$id) {
            return null;
        }

        $model = Mage::getModel('admin/user')->load($id);
        if (!$model->getId()) {
            return null;
        }

        return $model->getFirstname() . ' ' . $model->getLastname();
    }

    /**
     * Returns a collection of all admins
     *
     * @return mixed
     */
    public function getAdmins()
    {
        return Mage::getModel('admin/user')->getCollection();
    }

    /**
     * Returns a collection of all available admins based on settings for a give store id or the current store
     *
     * @param null $storeId
     * @param null $currentAdminId
     * @param null $customerId
     * @return mixed
     */
    public function getAllowedAdmins($storeId = null, $currentAdminId = null, $customerId = null)
    {
        if($storeId == null){
            $storeId = Mage::app()->getStore()->getStoreId();
        }

        if(Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/limit_available_salesrep', $storeId)){
            $adminIds = Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/available_salesrep', $storeId);
            $adminIds = explode(",", $adminIds);

            if($currentAdminId != null){
                if (!in_array($currentAdminId, $adminIds)) {
                    $adminIds[] = $currentAdminId;
                }
            }

            if($customerId != null){
                //check for an assigned sales rep
                $customer = Mage::getModel('customer/customer')->load($customerId);
                if($customer && $customer->getAssignedSalesRep()){
                    if (!in_array($customer->getAssignedSalesRep(), $adminIds)) {
                        $adminIds[] = $customer->getAssignedSalesRep();
                    }
                }
            }

            return $collection = Mage::getModel('admin/user')
                ->getCollection()
                ->addFieldToFilter('user_id', array('in' => $adminIds));
        } else {
            return $this->getAdmins();
        }
    }

    /**
     * Get the expiry date of a quote based on the give quote id
     *
     * @param null $id
     * @return bool|string
     */
    public function getExpiryDate($id = null)
    {
        if ($id > 0) {
            $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($id);
            if ($quote->getData('expiry')) {
                $expiry = $quote->getData('expiry');
            }
        }

        if (!isset($expiry)) {
            $storeid = Mage::app()->getStore()->getStoreId();
            if(isset($quote)){
                $storeid = $quote->getStoreId();
            }

            $days = Mage::getStoreConfig('qquoteadv_quote_configuration/expiration_times_and_notices/expirtime_proposal', $storeid);
            if (isset($days)) {
                $expiry = date('Y-m-d', strtotime("+$days days"));
            } else {
                $expiry = date('Y-m-d', Mage::getModel('core/date')->timestamp(time()));
            }
        }

        return $expiry;
    }

    /**
     * Get the reminder date of a quote based on the give quote id
     *
     * @param null $id
     * @return bool|string
     */
    public function getReminderDate($id = null)
    {
        if ($id > 0) {
            $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($id);
            if ($quote->getData('proposal_sent')) {
                $reminder = $quote->getData('reminder');
            }
        }

        if (!isset($reminder)) {
            $days = Mage::getStoreConfig('qquoteadv_quote_configuration/expiration_times_and_notices/send_reminder', Mage::app()->getStore()->getStoreId());
            if (isset($days)) {
                $reminder = date('Y-m-d', strtotime("+$days days"));
            } else {
                $reminder = date('Y-m-d', Mage::getModel('core/date')->timestamp(time()));
            }
        }

        return $reminder;
    }

    /**
     * Check if the request qty is possible for the given product
     *
     * @param $item
     * @param $qty
     * @return Varien_Object
     * @throws Exception
     */
    public function checkQuantities($item, $qty)
    {
        if (is_numeric($item)) {
            $_product = Mage::getModel('catalog/product')->load($item);
        } elseif ($item instanceof Mage_Catalog_Model_Product) {
            $_product = $item;
        } elseif (is_object($item) && $item->getData('product') instanceof Mage_Catalog_Model_Product) {
            $_product = $item->getData('product');
        } else {
            $result = new Varien_Object();
            $result->setHasError(true)
                ->setMessage(
                    Mage::helper('qquoteadv')->__("incorrect first parameter for checkQuantities should be product or product_id")
                )
                ->setErrorCode('no_product');

            return $result;
        }

        $stockItem = $_product->getStockItem();
        $result = new Varien_Object();
        $result->setProductUrl($_product->getProductUrl());

        if (!is_numeric($qty)) {
            try {
                if (is_string($qty)) {
                    $qty = $this->_tofloat($qty);
                    if (!is_numeric($qty)) {
                        throw new Exception('Qty is undefined');
                    }
                }
                if (is_array($qty)) {
                    foreach ($qty as $qtyKey => $qtyData) {
                        $qty = $qty[$qtyKey];
                        break;
                    }
                }
            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')->__('%s cannot be quotated for the requested quantity.', $_product->getName()))
                    ->setErrorCode('qty_available')
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('%s cannot be ordered in requested quantity.', $_product->getName()))
                    ->setQuoteMessageIndex('qty');
            }
        }

        if ($_product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && $_product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            if ($stockItem->getMinSaleQty() && $qty < $stockItem->getMinSaleQty()) {
                $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')->__('The minimum quantity allowed for quotation for %s is %s.', $_product->getName(), $stockItem->getMinSaleQty() * 1))
                    ->setErrorCode('qty_min')
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('%s cannot be quotated for in requested quantity.', $_product->getName()))
                    ->setQuoteMessageIndex('qty');

                return $result;
            }

            if ($stockItem->getMaxSaleQty() && $qty > $stockItem->getMaxSaleQty()) {
                $result->setHasError(true)
                    ->setMessage(
                        Mage::helper('cataloginventory')->__('The maximum quantity allowed for quotation for %s is %s.', $_product->getName(), $stockItem->getMaxSaleQty() * 1)
                    )
                    ->setErrorCode('qty_max')
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('%s cannot be ordered in requested quantity.', $_product->getName()))
                    ->setQuoteMessageIndex('qty');

                return $result;
            }

            if ($stockItem->getQty() && $qty > $stockItem->getQty() && $this->manageStock($_product) && !$stockItem->getBackorders()) {
                $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')->__('%s cannot be quotated for the requested quantity.', $_product->getName()))
                    ->setErrorCode('qty_available')
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('%s cannot be ordered in requested quantity.', $_product->getName()))
                    ->setQuoteMessageIndex('qty');
                return $result;
            }
        }

        //make sure no decimal qty is used on unsupported products
        if (!$stockItem->getIsQtyDecimal()) {
            if ((int)$qty != $qty) {
                $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')->__('%s cannot be quotated in a decimal quantity.', $_product->getName()))
                    ->setErrorCode('qty_decimal')
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('%s cannot be quotated in a decimal quantity.', $_product->getName()))
                    ->setQuoteMessageIndex('qty');

                return $result;
            }
        }

        if (is_object($item) && !$item->getParentItemId()) {
            $result->addData($this->checkQtyIncrements($item, $qty)->getData());
        }

        return $result;
    }

    /**
     * Check if a given item is in stock
     *
     * @param $item
     * @return Varien_Object
     * @throws Exception
     */
    public function isInStock($item)
    {
        if (is_numeric($item)) {
            $_product = Mage::getModel('catalog/product')->load($item);
        } elseif ($item->getData('product') instanceof Mage_Catalog_Model_Product) {
            $_product = $item->getData('product');
        } elseif ($item instanceof Mage_Catalog_Model_Product) {
            $_product = $item;
        } else {
            throw new Exception(Mage::helper('qquoteadv')->__("incorrect parameter for isInStock should be product or product_id"));
        }

        $result = new Varien_Object();
        if ($_product->getData('is_in_stock') == 0 && $this->manageStock($_product)) {
            $result->setHasError(true)
                ->setProductUrl($_product->getProductUrl())
                ->setMessage(
                    Mage::helper('qquoteadv')->__('item %s is out of stock.', $_product->getName())
                )
                ->setErrorCode('out_of_stock');
        }

        return $result;
    }

    /**
     * Check if the requested quantity is allowed for a given item
     *
     * @param $item
     * @param $qty
     * @return Varien_Object
     * @throws Exception
     */
    public function checkQtyIncrements($item, $qty)
    {
        if (is_numeric($item)) {
            $_product = Mage::getModel('catalog/product')->load($item);
        } elseif ($item instanceof Mage_Catalog_Model_Product) {
            $_product = $item;
        } elseif ($item->getData('product') instanceof Mage_Catalog_Model_Product) {
            $_product = $item->getData('product');
        } else {
            throw new Exception(Mage::helper('qquoteadv')->__("incorrect first parameter for checkQtyIncrements should be product or product_id"));
        }

        $result = new Varien_Object();

        if (is_object($_product) && !$_product->getParentItemId()) {
            $stockItem = $_product->getStockItem();
            $qtyIncrements = $stockItem->getQtyIncrements();

            if ($qtyIncrements && (Mage::helper('core')->getExactDivision($qty, $qtyIncrements) != 0)) {
                $result->setHasError(true)
                    ->setProductUrl($_product->getProductUrl())
                    ->setQuoteMessage(
                        Mage::helper('qquoteadv')->__('%s cannot be added to the quotation in the requested quantity.', $_product->getName())
                    )
                    ->setErrorCode('qty_increments')
                    ->setQuoteMessageIndex('qty');


                $result->setMessage(
                    Mage::helper('qquoteadv')->__('%s is available for quotation in increments of %s only.', $_product->getName(), $qtyIncrements * 1)
                );

            }
        }

        return $result;
    }

    /**
     * Check if a product is quotable for the given qty
     *
     * @param $product
     * @param $qty
     * @return Varien_Object
     * @throws Exception
     */
    public function isQuoteable($product, $qty)
    {
        $errors = array();
        // Quantity errors have priority above increments
        $resultQuantities = $this->checkQuantities($product, $qty);
        if ($resultQuantities->getHasError()) {
            $errors[] = $resultQuantities->getMessage();
        } else {
            $resultIncrement = $this->checkQtyIncrements($product, $qty);
            if ($resultIncrement->getHasError()) {
                $errors[] = $resultIncrement->getMessage();
            }
        }
        if (!$resultQuantities->getHasError() && !$resultIncrement->getHasError()) {
            $resultInStock = $this->isInStock($product);
            if ($resultInStock->getHasError()) {
                $errors[] = $resultInStock->getMessage();
            }
        }
        $result = new Varien_Object();
        if (count($errors)) {
            $result->setHasErrors(true)
                ->setErrors($errors);
        }

        return $result;
    }

    /**
     * This function recalculates a product including VAT based on custommer adress
     *
     * @param $product
     * @param $addresses
     * @return mixed
     */
    public function updatePriceOnAddress($product, $addresses)
    {
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($product['quote_id']);
        $storeId = $quote->getStoreId();

        //get address data
        $shippingAddress = $addresses['shipping'];
        $billingAddress = $addresses['billing'];
        $billingAddressData = $billingAddress->getData();
        $shippingAddressData = $shippingAddress->getData();
        $addressData = array_merge($billingAddressData, $shippingAddressData);

        //get customer data
        if(is_object($billingAddress)){
            $customerId = $billingAddress->getCustomerId();
        } elseif (is_object($shippingAddress) && !isset($customerId)){
            $customerId = $shippingAddress->getCustomerId();
        } else {
            if(isset($addressData['customer_id'])){
                $customerId = $addressData['customer_id'];
            } else {
                $message = "Addresses did not provide a customer id in updatePriceOnAddress";
                Mage::log('Message: ' .$message, null, 'c2q.log', true);
                return $product;
            }
        }

        $customer = Mage::getModel('customer/customer')->load($customerId);

        $priceIncludeTaxInCatalog = Mage::helper('tax')->priceIncludesTax($storeId);

        if($priceIncludeTaxInCatalog == 1){
            //tax_display_type or tax_cart_display_price?
            $displayTaxInCatalog = Mage::getStoreConfig('tax/display/type', $storeId);

            if($displayTaxInCatalog == 2 || $displayTaxInCatalog == 3){
                $taxCalculationBasesOnShopLocation = Mage::getStoreConfig('tax/calculation/based_on', $storeId);

                if($taxCalculationBasesOnShopLocation != "origin"){
                    $shopCountry = Mage::getStoreConfig("tax/defaults/country", $storeId);
                    $shopRegion = Mage::getStoreConfig("tax/defaults/region", $storeId);

                    //get country and region for user from available data
                    if(isset($addressData['country_id']) && !empty($addressData['country_id']) ){
                        //try to use the form data
                        $customerCountry = $addressData['country_id'];
                        $customerRegion = $addressData['region_id'];

                    } else {
                        //get the data from the existing user (on email)
                        //$customer = Mage::getModel("customer/customer");
                        $customer->setWebsiteId($storeId);
                        //$customer->loadByEmail($adrress['email']);
                        foreach ($customer->getAddresses() as $addressModel)
                        {
                            $customerCountry = $addressModel->getCountryId();
                            $customerRegion = $addressModel->getRegionId();
                            break; //just one address
                        }
                    }

                    //if country is set
                    if(isset($customerCountry) && !empty($customerCountry)){
                        //make sure region is not empty
                        if(isset($customerRegion) && empty($customerRegion)){
                            $customerRegion = "0";
                        }

                        //check if the result is different from the shop
                        if(($customerCountry != $shopCountry) || ($customerRegion != $shopRegion)){
//                            //not supported
//                            //check if data is pre calculated by magento
//                            $checkout = Mage::getSingleton('checkout/session')->getQuote();
//                            $shippingAddress = $checkout->getShippingAddress();
//
//                            if($shippingAddress->getCountryId() == null){
                            //recalculate
                            //remove tax
                            $store = Mage::app()->getStore($storeId);
                            $productModel = Mage::getModel('catalog/product')->load($product['product_id']);
                            $taxClassId = $productModel->getTaxClassId();
                            $taxCalculation = Mage::getModel('tax/calculation');
                            $customer = $quote->getCustomer();
                            if ($customer) {
                                $taxCalculation->setCustomer($customer);
                            }

                            //get store tax
                            $shopRequest = $taxCalculation->getRateOriginRequest($store);
                            $shopPercent = $taxCalculation->getRate($shopRequest->setProductClassId($taxClassId));

                            //get user tax
                            $shippingAddress = Mage::getModel('customer/address')->setCountryId($customerCountry)->setRegionId($customerRegion);
                            $customerTaxClass = null;
                            if($quote->getCustomerTaxClassId()){
                                $customerTaxClass = $quote->getCustomerTaxClassId();
                            }
                            $userRequest = $taxCalculation->getRateRequest($shippingAddress, $billingAddress, $customerTaxClass, $store);
                            $userPercent = $taxCalculation->getRate($userRequest->setProductClassId($taxClassId));

                            //add tax
                            $product['owner_cur_price'] = ($product['owner_cur_price'] / (100 + $shopPercent)) * (100 + $userPercent);
                            $product['original_cur_price'] = ($product['original_cur_price'] / (100 + $shopPercent)) * (100 + $userPercent);

                            if($shopPercent != $userPercent){
                                Mage::getSingleton("core/session")->addNotice($this->__("Quote request is converted from %d%% VAT to %d%% VAT.", $shopPercent, $userPercent));
                            }

//                            }

                        }
                    }
                }
            }
        }

        return $product;
    }

    /**
     * Check if inventory needs to be checked
     * for the product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function manageStock(Mage_Catalog_Model_Product $product)
    {
        // Check Store setting for stock management
        if ((int)Mage::getStoreConfig('cataloginventory/item_options/manage_stock') == 0) {
            return false;
        }
        // Check product setting
        if ((int)$product->getStockItem()->getManageStock() == 0) {
            return false;
        }

        return true;
    }

    /**  Check if product is congigurable
     *  if so, only add the ordered simple product
     *  with quantity of the configurable product
     *
     *  @param  object              // Mage_Catalog_Model_Product
     *  @param  integer             // Qty
     *  @return integer || false
     */
    public function isConfigurable($item, $qty)
    {

        $return = false;
        $item_id = $item->getId();
        $product = $item->getData('product');
        $session = Mage::getSingleton('adminhtml/session');

        // Check if simple product is child of the stored configurable
        if ($session->getConfParent()) {
            foreach ($session->getConfParent() as $parent) {
                if ($item->getData('parent_item_id') == $parent['item_id']) {
                    $return = $parent['qty'];
                }
            }
        }

        // for configurable products get array of children
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {

            $conf_children = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());

            if ($conf_children) {
                $parent_data = $session->getConfParent();
                $parent_data[] = array(
                    'item_id' => $item_id,
                    'children' => $conf_children,
                    'qty' => $qty
                );

            }
            // store data in the admin session
            $session->setConfParent($parent_data);
        }

        return $return;
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getAdminUser()
    {
        $adminSessionName = 'adminhtml';
        /** @var $currentSession Mage_Core_Model_Session */
        $currentSession = Mage::getSingleton('core/session');
        $currentSessionName = $currentSession->getSessionName();

        //fix if magento doesn't use the normal session id
        if (!isset($_COOKIE[$adminSessionName])) {
            $adminSessionName = "PHPSESSID";
        }

        if (isset($_COOKIE[$currentSessionName]) && isset($_COOKIE[$adminSessionName])) {
            $adminSessionId = $_COOKIE[$adminSessionName];

            //load the log object of this user id
            $logAdmin = Mage::getModel('qquoteadv/qqadvlogadmin')->load($adminSessionId, 'session_id');

            //check if it already exists
            $adminId = $logAdmin->getData("admin_id");
            if ($adminId) {
                return Mage::getModel('admin/user')->load($adminId);
            }

        }

        return null;
    }

    /**
     * Get admin user ID for next rotation
     *
     * @param int|null $roleId
     * @return int
     */
    public function getNextAssignToAdmin($roleId = null)
    {
        if ($roleId === null) {
            $roleId = (int)Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/auto_assign_role');
        }

        /** @var $model Ophirah_Qquoteadv_Model_Qqadvrotation */
        $model = Mage::getModel('qquoteadv/qqadvrotation');

        try {
            return $model->getNextUserId($roleId);
        } catch (Zend_Db_Statement_Exception $e) {
            $message = 'Could not save next user ID rotation. This is probably due to a badly configured role ID';
            Mage::log('Message: ' .$message, null, 'c2q.log', true);
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            return 0;
        }
    }

    /**
     * Get admin user ID previously handeled this customer
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @param null $customerId
     * @param bool $backend
     * @return int
     * @internal param Mage_Customer_Model_Customer $customer
     */
    public function getPreviousAssignedAdmin(
        Ophirah_Qquoteadv_Model_Qqadvcustomer $quote = null,
        $customerId = null,
        $backend = false
    ) {
        //check if we have a customer id or get it from the quote object
        if ($customerId === null) {
            $customerId = $quote->getCustomerId();

            //fallback if customer isn't saved to the quote yet / when customer isn't logged-in
            if (!$customerId && ($quote !== null)) {
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId($quote->getStoreId());
                $customer->loadByEmail($quote->getEmail());
                $customerId = $customer->getId();
            }
        }

        /* @var $collection Ophirah_Qquoteadv_Model_Mysql4_Qqadvcustomer_Collection */
        $collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
        $collection->addOrder('created_at')
            ->addFieldToFilter('main_table.customer_id', $customerId)
            ->addFieldToFilter('main_table.is_quote', 1)
            ->setPageSize(2);

        //make sure we don't use the current quote
        if ($quote !== null) {
            if ($quote->getId()) {
                $quoteId = $quote->getId();
                $filter = "neq";
            } else {
                $cloneColl = clone($collection);
                $quoteId = $cloneColl->getFirstItem()->getQuoteId();
                $filter = "eq";
            }

            $collection->addFieldToFilter('main_table.quote_id', array($filter => $quoteId));
        }

        //make sure we only use active admins for the rotation (backend only)
        if ($backend === false) {
            //join the admin user table
            $collection->getSelect()
                ->joinInner(
                    array('u' => $collection->getResource()->getTable('admin/user')),
                    'main_table.user_id = u.user_id'
                );

            //filter on active admins
            $collection->addFieldToFilter('u.is_active', 1);
        }

        return $collection->getFirstItem()->getUserId();
    }

    /**
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @param int $loggedInAdmin
     */
    public function assignQuote(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote, $loggedInAdmin = null)
    {
        $quote->setUserId($this->getExpectedQuoteAdminId($quote, $loggedInAdmin));
    }

    /**
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @param int $loggedInAdmin
     * @return Mage_Admin_Model_User|null
     */
    public function getExpectedQuoteAdmin(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote, $loggedInAdmin = null)
    {
        $adminId = $this->getExpectedQuoteAdminId($quote, $loggedInAdmin);
        if ($adminId == null) {
            return null;
        }

        return $this->getAdmin($adminId);
    }

    /**
     * @param int $adminId
     * @return Mage_Admin_Model_User|$admin
     */
    public function getAdmin($adminId)
    {
        /* @var $admin Mage_Admin_Model_User */
        $admin = Mage::getModel('admin/user');
        $admin->load($adminId);
        if (!$admin->getId()) {
            return null;
        }

        return $admin;
    }

    /**
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @param int $loggedInAdmin
     * @param bool $backend
     * @return int
     */
    public function getExpectedQuoteAdminId(
        Ophirah_Qquoteadv_Model_Qqadvcustomer $quote,
        $loggedInAdmin = null,
        $backend = false
    ) {
        if ($quote->getUserId()) {
            return $quote->getUserId();
        }

        //check for an assigned sales rep
        if ($quote->getCustomerId() || $quote->getEmail()) {
            if ($quote->getCustomerId()) {
                $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
            } else {
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId($quote->getStoreId());
                $customer->loadByEmail($quote->getEmail());
            }

            if ($customer && $customer->getAssignedSalesRep()) {
                return $customer->getAssignedSalesRep();
            }
        }

        if ((bool)Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/auto_assign_previous')) {
            $adminId = $this->getPreviousAssignedAdmin($quote, null, $backend);
            if ($adminId) {
                return $adminId;
            }
        }

        if ((bool)Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/auto_assign_login')) {
            if ($loggedInAdmin === null && Mage::helper('qquoteadv/licensechecks')->isAllowedLoggedInSalesRep()) {
                $admin = $this->getAdminUser();
                if ($admin && $admin->getId()) {
                    $loggedInAdmin = $admin->getId();
                }
            }

            if ($loggedInAdmin) {
                return $loggedInAdmin;
            }
        }

        if ((bool)Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/auto_assign_rotate')) {
            return $this->getNextAssignToAdmin();
        }

        return null;
    }

    /**
     * Returns if the quote button should be hidden for a given product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hideQuoteButton(Mage_Catalog_Model_Product $product)
    {
        $return = false;
        $returnStock = false;
        $returnPrice = false;
        if ($product->getData('allowed_to_quotemode') == 1 && $product->getData('quotemode_conditions') > 0) {
            // Check Product Price for dynamic bundle
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $product->getPriceType() == 0) {
                $priceModel = $product->getPriceModel()->getTotalPrices($product, null, true, false);
                if (is_array($priceModel)) {
                    foreach ($priceModel as $bundlePrice) {
                        if ($bundlePrice > 0) {
                            $returnPrice = true;
                        }
                    }
                }
            } else {
                if ($product->getPrice() > 0) {
                    $returnPrice = true;
                }
            }

            // Check Stock
            if ((int)$product->getStockItem()->getIsInStock() == 1) {
                $returnStock = true;
            }

        }

        // Check Product Attribute
        switch ($product->getData('quotemode_conditions')) {
            case 1:
                // Show when price is '0.00'
                $return = $returnPrice;
                break;
            case 2:
                // Show when out of stock
                $return = $returnStock;
                break;
            case 3:
                // Show when price is '0.00'
                // or when out of stock
                if ($returnStock === true && $returnPrice === true) {
                    $return = true;
                }
                break;
        }

        return $return;
    }

    /**
     * Check if Beta Features are enabled
     *
     * @param int $storeId
     * @return bool
     */
    public function betaIsEnabled($storeId = 0)
    {
        $return = false;
        if (is_int((int)$storeId) && Mage::getStoreConfig('qquoteadv_advanced_settings/beta_features/beta', $storeId)) {
            if ((int)Mage::getStoreConfig('qquoteadv_advanced_settings/beta_features/beta', $storeId) > 0) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Check if APO Compatibility setting is enabled
     *
     * @param int $storeId
     * @return bool
     */
    public function apoIsEnabled($storeId = 0)
    {
        $return = false;
        if ((int)Mage::getStoreConfig('qquoteadv_advanced_settings/beta_features/apo', (int)$storeId) > 0) {
            $return = true;
        }

        return $return;
    }

    /**
     * Check if customer for customer setting is enabled
     *  and is enabled for the current customer
     *
     * @return bool
     */
    public function customerForCustomerIsEnabled()
    {
        //check if the option is enabled
        if ((int)Mage::getStoreConfig('qquoteadv_advanced_settings/beta_features/customerforcustomer') > 0) {
            //check if the option is enabled for this customer
            return $this->customerForCustomerIsEnabledForCurrentGroup();
        }

        return false;
    }

    /**
     * Check if customer for customer setting is enabled for the current customer group
     *
     * @return bool
     */
    public function customerForCustomerIsEnabledForCurrentGroup()
    {
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $groups = Mage::getStoreConfig('qquoteadv_advanced_settings/beta_features/customerforcustomer_group');
        if (isset($groups) && (!empty($groups) || $groups === "0")) {
            $groups = explode(",", $groups);
            if (!in_array($customerGroupId, $groups)) {
                return false;
            }
        }

        //default enabled
        return true;
    }

    /**
     * Check if date value
     * contains date data
     *
     * @param $date
     * @return bool
     */
    public function isDate($date)
    {
        $subDate = (int)substr($date, 0, 4);
        $intDate = (int)preg_replace(' /\D/', '', $date);
        if ($subDate > 0 && $intDate > 0) {
            return true;
        }

        return false;
    }

    /**
     * Update all child and parent quotes's edit increment numbers.
     * Needed for Admin area.
     *
     * @param $quote
     */
    public function updateQuoteEditIncrements($quote)
    {
        if ($quote->getId() && $quote->getOriginalIncrementId()) {
            $collection = $quote->getCollection();
            $quotedIncrId = $collection->getConnection()->quote($quote->getOriginalIncrementId());
            $collection->getSelect()->where(
                "original_increment_id = {$quotedIncrId} OR increment_id = {$quotedIncrId}"
            );

            foreach ($collection as $quoteToUpdate) {
                $quoteToUpdate->setEditIncrement($quote->getEditIncrement());
                $quoteToUpdate->save();
            }
        }
    }

    /**
     * Function that copies the products on a quote to a new quote
     *
     * @param $oldQuote
     * @param $newQuote
     * @return $this
     */
    public function duplicateQuoteProductsToNewQuote($oldQuote, $newQuote){
        //get request items
        $requestItems = Mage::getModel('qquoteadv/requestitem')
            ->getCollection()
            ->setQuote($oldQuote)
            ->addFieldToFilter('quote_id', $oldQuote->getData("quote_id"))
            ->load();

        //array to keep track of duplicated products
        $manyToOneMapArray = Array();

        //duplicate request items and assigned products
        foreach($requestItems as $request_id => $requestItem){
            //duplicate for new quote
            $cloneData = $requestItem->getData();
            unset($cloneData['request_id']);
            $quoteadv_product_id = $cloneData['quoteadv_product_id'];
            unset($cloneData['quoteadv_product_id']);
            $new_requestItem = Mage::getModel("qquoteadv/requestitem")->setData($cloneData);
            $new_requestItem->setData("quote_id", $newQuote->getData("quote_id"));

            //check if product is already duplicated
            if(isset($manyToOneMapArray[$quoteadv_product_id])){
                //quoteadv_product already duplicated
                //relocate asigned product to new product
                $new_requestItem->setData("quoteadv_product_id", $manyToOneMapArray[$quoteadv_product_id]);
            } else {
                //get asigned product
                $quoteadv_product = Mage::getModel('qquoteadv/qqadvproduct')->load($quoteadv_product_id);

                //duplicate asigned product
                $cloneData = $quoteadv_product->getData();
                unset($cloneData['id']);
                unset($cloneData['quote_id']);
                $new_quoteadv_product = Mage::getModel("qquoteadv/qqadvproduct")->setData($cloneData);
                $new_quoteadv_product->setData("quote_id", $newQuote->getData("quote_id"));
                $new_quoteadv_product->save();

                //relocate asigned product to new product
                $new_requestItem->setData("quoteadv_product_id", $new_quoteadv_product->getData("id"));

                //save that this product is duplicated
                $manyToOneMapArray[$quoteadv_product_id] = $new_quoteadv_product->getData("id");
            }

            //save new models
            $new_requestItem->save();
        }

        return $this;
    }

    /**
     * @description This function gets an array of articles from https://cart2quote.zendesk.com
     * @link https://developer.zendesk.com/rest_api/docs/help_center/categories#show-category
     * @return array of articles in JSON
     */
    public function executeArticleRequest()
    {
        try {
            // VARS
            $section = 200293659; // Help section with the related articles
            $articleArray['articles'] = array(); // The array of articles
            $client = new Zend_Rest_Client('https://cart2quote.zendesk.com');  // Base URL
            $page = 1;
            $perPage = 10;
            if (extension_loaded('mbstring')) {
                $perPage = 100;
            }

            $getValue = array(
                'page' => $page,
                'per_page' => $perPage
            );

            $response = $client->restGet('/api/v2/help_center/en-us/sections/' . $section . '/articles.json', $getValue); // API Request

            if ($response->getStatus() == 200 || $response->getStatus() == 201) { // If Success
                $utf8DecodedBody = utf8_decode($response->getBody());

                //check for mbstring
                if (extension_loaded('mbstring')) {
                    $utf8DecodedBody = mb_convert_encoding($utf8DecodedBody, 'UTF-8', 'UTF-8');
                }

                $responsedecoded = json_decode($utf8DecodedBody);

                if(is_object($responsedecoded)){

                    for ($page = $responsedecoded->page; $page <= $responsedecoded->page_count; $page++) { // Go through the pages and adding the articles to the array.
                        $getValue= array(
                            'page' => $page,
                            'per_page' => $perPage
                        );
                        $articleList = $client->restGet('/api/v2/help_center/en-us/sections/' . $section . '/articles.json', $getValue);
                        if ($articleList->getStatus() == 200 || $articleList->getStatus() == 201) {
                            $utf8DecodedBody = utf8_decode($articleList->getBody());

                            //check for mbstring
                            if (extension_loaded('mbstring')) {
                                $utf8DecodedBody = mb_convert_encoding($utf8DecodedBody, 'UTF-8', 'UTF-8');
                            }

                            $articlelistDecoded = json_decode($utf8DecodedBody);
                            if ($articlelistDecoded && isset($articlelistDecoded->articles) && is_array($articlelistDecoded->articles)) {
                                foreach ($articlelistDecoded->articles as $articles) {
                                    $articleArray['articles'][] = $articles; // Add article to array
                                }
                            } else {
                                $error = json_last_error();
                                Mage::log('Exception: ' .$error, null, 'c2q_exception.log', true);
                            }
                        }
                    }
                }
            }

            return json_encode($articleArray['articles']); // Return array of articles in the JSON
        } catch (Zend_Rest_Client_Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            return false;
        } catch (Zend_Rest_Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            return false;
        } catch (Exception $e) {
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            return false;
        }
    }

    /**
     * This helper is for the template.xml files
     *
     * <reference name="product.info">
     *  <action ifconfig="qquoteadv_general/quotations/enabled" method="setTemplate">
     *   <template>qquoteadv/page/html/header.phtml</template>
     *  </action>
     *
     * Can now be used like:
     *
     * <reference name="product.info">
     *  <action ifconfig="qquoteadv_general/quotations/enabled" method="setTemplate">
     *   <template helper="qquoteadv/setQuoteTemplate">
     *    <path>qquoteadv/catalog/product/view.phtml</path>
     *    <name>product.info</name>
     *   </template>
     *  </action>
     *
     * @param $argOne
     * @param $argTwo
     * @return bool
     */
    public function setQuoteTemplate($argOne, $argTwo){
        $isFrontEnabled = Mage::getStoreConfig('qquoteadv_general/quotations/active_c2q_tmpl');

        $nodeTemplate = Mage::app()->getLayout()->getBlock($argTwo)->getTemplate();

        if($isFrontEnabled){
            return $argOne;
        } else {
            if(isset($nodeTemplate) && !empty($nodeTemplate)){
                return $nodeTemplate;
            } else {
                return false;
            }
        }
    }

    /**
     * Delete items with attribute allowed_to_quotemode=false;
     * This function uses ignoreNotAllowedToQuote from the session. If true then this function is ignored.
     */
    public function deleteNotAllowedProductsInQuoteFromSession()
    {
        $quoteId = Mage::getSingleton('customer/session')->getQuoteadvId();
        $collection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);
        if (count($collection)) {
            foreach ($collection as $item) {
                $productId = $item->getProductId();
                $_product = Mage::getModel('catalog/product')->load($productId);

                /*
                 * Add support for OrganicInternet_SimpleConfigurableProducts SCP
                 */
                $ignore = false;
                if (Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')) {
                    $attibutes = unserialize($item->getAttribute());
                    if (isset($attibutes['cpid'])) {
                        $ignore = true;
                    }
                }

                if (!$_product->getData('allowed_to_quotemode') && !$ignore) {
                    if (Mage::getSingleton("core/session")->getData('ignoreNotAllowedToQuote') != true) {
                        $collection->walk('delete',
                            array('id' => $item->getId())
                        );
                        Mage::getSingleton("core/session")->addError(
                            Mage::helper('adminhtml')->__('Error').': '.$this->__('The product "%s" could not be added. (No user rights)', $_product->getData('name'))
                        );
                    }
                }
            }
        }
    }

    /**
     * Function for the extra-custom-field functionality
     *
     * @return int
     */
    public function getNumberOfExtraFields(){
        return 12;
    }

    /**
     * Sets the reference ID in the core/session
     * @param $mageQuoteId
     * @param $c2qId
     */
    public function setReferenceIdInCoreSession($mageQuoteId, $c2qId){
        $c2qIdAssignedToMageId = array($mageQuoteId => $c2qId);
        $c2qIdAssignedToMageIds = Mage::getSingleton('core/session')->getData('c2qIdAssignedToMageIds');

        if(is_null($c2qIdAssignedToMageIds)){
            $c2qIdAssignedToMageIds = array();
        }
        $c2qIdAssignedToMageIds[] = $c2qIdAssignedToMageId;

        Mage::getSingleton('core/session')->setData('c2qIdAssignedToMageIds', $c2qIdAssignedToMageIds);
    }

    /**
     * Returns if the price is allowed in the Cart2Quote settings. Path: System > Cart2Quote > Quote Configuration >
     * Proposal > Show Items Price
     * @return bool
     */
    public function isPriceByDefaultAllowed(){
        $isPriceAllowed = true;
        if(Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/itemprice') == 0){
            $isPriceAllowed = false;
        }
        return $isPriceAllowed;
    }

    /**
     * This function was moved to 'qquoteadv/licensechecks'
     *  but to keep old design's compatible this redirect is added
     *
     * @param $quote
     * @param bool $my
     * @return mixed
     */
    public function getAutoLoginUrl($quote, $my = false)
    {
        return Mage::helper('qquoteadv/licensechecks')->getAutoLoginUrl($quote, $my);
    }

    /**
     * Function that makes a float from a given string or int
     *
     * @param $num
     * @return float
     */
    private function _tofloat($num) {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return floatval(preg_replace("/[^0-9]/", "", $num));
        }

        return floatval(
            preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
        );
    }

    /**
     * Function that renders a date without timezone difference but with the correct formatting
     *
     * @param null $input
     * @param string $format
     * @param bool|false $showTime
     * @return string
     */
    public function formatDateHard($input = null, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, $showTime = false)
    {
        if ($showTime) {
            $format = Mage::app()->getLocale()->getDateTimeFormat($format);
        } else {
            $format = Mage::app()->getLocale()->getDateFormat($format);
        }

        $date = Mage::getModel('core/date')->timestamp($input) - Mage::getModel('core/date')->getGmtOffset();
        $date = new Zend_Date($date);
        return $date->toString($format);
    }

    /**
     * Function that updates the currency in the cookie while keeping the header size as small as possible
     *
     * @param $currency
     * @param $storeId
     */
    public function setCurrentCurrency($currency, $storeId = null){
        $currency = strtoupper($currency);
        $store = Mage::app()->getStore($storeId);
        if ($currency != $store->getCurrentCurrencyCode()) {
            $store->setCurrentCurrencyCode($currency);
        }
    }

    /**
     * Prepare the attribute for product file options
     * Sometimes this needs to be an array and sometimes the same data needs to be an Varien_Object
     * That has probably something to do with increased security in later Magento releases.
     *
     * @param Varien_Object|array $productParams
     * @return Varien_Object|array
     */
    public function prepareFileOptions($productParams)
    {
        if (isset($productParams['options']) && !empty($productParams['options'])) {
            foreach ($productParams['options'] as $key => $value) {
                $optionKey = "options_" . $key . "_file_action";
                if (isset($value['size']) && isset($value['fullpath'])) {
                    $productParams[$optionKey] = "save_old";

                    //convert array to Varien_Object if needed
                    if (is_array($productParams)) {
                        $newProductParams = new Varien_Object();
                        $newProductParams->setData($productParams);
                    } else {
                        $newProductParams = $productParams;
                    }

                    $processingParams = new Varien_Object();
                    $processingParams->setCurrentConfig($newProductParams);

                    $productParams['_processing_params'] = $processingParams;
                }
            }
        }

        return $productParams;
    }

    /**
     * Prepare the product for a virtual quote
     *
     * @param Mage_Catalog_Model_Product|null $product
     * @param $quoteProduct
     * @param $originalPrice
     * @param $skipOptions
     * @return bool|Mage_Catalog_Model_Product
     */
    private function _prepareProductForVirtualQuote(
        $product,
        $quoteProduct,
        $originalPrice,
        $skipOptions
    ) {
        //set product if it isn't given
        if ($product === null) {
            if ($quoteProduct === null) {
                $message = '_prepareProductForVirtualQuote called without product and quoteProduct';
                Mage::log('Warning: ' . $message, null, 'c2q.log');
                return false;
            }

            $product = $quoteProduct->getProduct();
            if (!($product instanceof Mage_Catalog_Model_Product)) {
                $message = '_prepareProductForVirtualQuote called with product that is not an instance of Mage_Catalog_Model_Product';
                Mage::log('Warning: ' . $message, null, 'c2q.log');
                return false;
            }
        }

        $product->setSkipCheckRequiredOption($skipOptions);
        if ($originalPrice) {
            //overwrite custom price
            $product->setData('special_price', $product->getPrice());
        }

        return $product;
    }

    /**
     * Prepare the product parameters for a virtual quote
     *
     * @param $attributes
     * @param $quoteProduct
     * @return array|Varien_Object
     */
    private function _prepareProductParamsForVirtualQuote($attributes, $quoteProduct)
    {
        //set attributes if it isn't given
        if ($attributes == null) {
            if ($quoteProduct === null) {
                $message = 'addItemToVirtualQuote called without attributes and quoteProduct';
                Mage::log('Warning: ' . $message, null, 'c2q.log');
                return false;
            }

            $attributes = $quoteProduct->getAttribute();
        }

        //mage new product param object
        $productParams = new Varien_Object(unserialize($attributes));

        //check for file action
        $productParams = $this->prepareFileOptions($productParams);

        //make sure a qty is set
        if (!isset($productParams['qty'])) {
            if ($quoteProduct != null) {
                $productParams['qty'] = $quoteProduct->getQty();
            }
        }

        return $productParams;
    }

    /**
     * Get the allowed HTML tags for usage in the escapeHtml function
     *
     * @return array
     */
    public function getAllowedHtmlTags()
    {
        //list the HTML tags that don't need escaping
        $allowedTags = array(
            'strong',
            'p',
            'br',
            'em',
            'a[a-zA-Z="-_:\s\;\“\"\”\.\']*?',
            'span[a-zA-Z="-_:\s\;\“\"\”\.\']*?',
        );

        return $allowedTags;
    }

    /**
     * Escape html entities
     *
     * @param mixed $data
     * @param null $allowedTags
     * @param bool $allowInnerQuotes
     * @return array|mixed|string
     */
    public function escapeHtml($data, $allowedTags = null, $allowInnerQuotes = false)
    {
        if (!$allowInnerQuotes && !is_array($data)) {
            return Mage::helper('core')->escapeHtml($data, $allowedTags);
        }

        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item, $allowedTags, $allowInnerQuotes);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);

                    //add tag to keep inner quotes
                    if ($allowInnerQuotes) {
                        preg_match_all(
                            '/<([\/\s\r\n]*)(' . $allowed . ')(.*)>/siU',
                            $data,
                            $matches,
                            PREG_SET_ORDER
                        );
                        foreach ($matches as $match) {
                            if (!empty($match) && is_array($match) && isset($match[0])) {
                                $innerTag = $match[0];
                                $innerTagAllowed = str_replace('"', '##quot##', $innerTag);

                                $data = str_replace($innerTag, $innerTagAllowed, $data);
                            }
                        }
                    }

                    if (!$allowInnerQuotes) {
                        $result = preg_replace(
                            '/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si',
                            '##$1$2$3##',
                            $data
                        );
                    } else {
                        $result = preg_replace(
                            '/<([\/\s\r\n]*)(' . $allowed . ')(.*)>/siU',
                            '##$1$2$3##',
                            $data
                        );
                    }

                    $result = htmlspecialchars($result, ENT_COMPAT, 'UTF-8', false);

                    //replace tag to keep inner quotes
                    if ($allowInnerQuotes) {
                        $result = str_replace('##quot##', '"', $result);
                        $result = preg_replace(
                            '/##([\/\s\r\n]*)(' . $allowed . ')(.*)##/siU',
                            '<$1$2$3>',
                            $result
                        );
                    } else {
                        $result = preg_replace(
                            '/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si',
                            '<$1$2$3>',
                            $result
                        );
                    }
                } else {
                    $result = htmlspecialchars($data, ENT_COMPAT, 'UTF-8', false);
                }
            } else {
                $result = $data;
            }
        }

        return $result;
    }

    /**
     * Get currency price
     *
     * @param $currencyCode
     * @param $storeId
     * @param $origPrice
     * @return double
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function getCurrencyPrice($currencyCode, $storeId, $origPrice)
    {
        if ($currencyCode) {
            $baseCurrency = Mage::app()->getStore($storeId)->getBaseCurrencyCode();

            return Mage::helper('directory')->currencyConvert($origPrice, $baseCurrency, $currencyCode);
        }

        return $origPrice;
    }
}
