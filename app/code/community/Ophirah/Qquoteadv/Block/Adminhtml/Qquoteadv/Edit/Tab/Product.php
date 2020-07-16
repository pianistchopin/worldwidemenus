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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Tab_Product
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @var
     */
    protected $_quoteData;

    /**
     * @var
     */
    public $quote;

    /**
     * @var
     */
    public $customer;

    /**
     * @var
     */
    public $customerGroupCode;

    /**
     * @var array
     */
    public $products = array();

    /**
     * @var
     */
    public $rate;

    /**
     * @var
     */
    public $shippingPrice;

    /**
     * Set product template to display product information in admin tab
     */
    public function __construct()
    {
        parent::__construct();
//        $this->setTemplate('qquoteadv/product.phtml'); @deprecated: now managed in the qquoteadv.xml layout file
        $this->iniBlocks();
    }

    /**
     * Returns the quote model
     *
     * @return object
     */
    public function getQuoteModel(){
        if($this->_quoteData == null){
            $this->_quoteData = $this->getQuoteData($this->needCollectTotals());
        }
        return $this->_quoteData;
    }

    /**
     * Returns the corresponding magento quote for a given qquoteadv quote item
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $item
     * @return mixed
     */
    public function getMagentoQuote(Ophirah_Qquoteadv_Model_Qqadvproduct $item){
        $product = $this->getProductInfo($item->getProductId());
        $productParams = $item->getAttribute();
        return Mage::helper('qquoteadv')->getQuoteItem($product, $productParams);
    }

    /**
     *  Returns the corresponding magento quote item for a given qquoteadv quote item
     *
     * @param $item
     * @return bool
     */
    public function getMagentoQuoteItem($item){
        $product = $this->getProductInfo($item->getProductId());
        $magentoQuote = $this->getMagentoQuote($item);
        foreach ($magentoQuote->getAllItems() as $_unit) {
            if ($_unit->getProductId() == $product->getId()) {
                return $_unit;
            }
        }
        return false;
    }

    /**
     * Check if totals need collection
     * Extra check for the configuration setting calculate_quote_totals_on_load
     *
     * @return bool
     */
    public function needCollectTotals(){
        $shouldCalculate = $this->shouldCollectTotals();
        $totalsCollected = $this->isTotalsCollected();

        if($shouldCalculate == true){
            return !$totalsCollected;
        }

        return $shouldCalculate;
    }

    /**
     * Check if totals are collected
     *
     * @return bool
     */
    public function isTotalsCollected(){
        return $this->getQuoteData(false)->getTotalsCollectedFlag();
    }

    /**
     * Check if totals should be collected
     *
     * @return bool
     */
    public function shouldCollectTotals(){
        return Mage::getStoreConfig('qquoteadv_advanced_settings/backend/calculate_quote_totals_on_load');
    }

    /**
     * Initialize child-blocks
     */
    public function iniBlocks(){
        $this->setExtraFieldsBlock();
        $this->setMultiUploadBlock();
        $this->setCrmAddonAttachmentBlock();
        $this->setCrmAddonNewAttachmentBlock();
    }

    /**
     * Get Product information from qquote_product table
     * @return object
     */
    public function getProductData()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $product = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);
        return $product;
    }

    /**
     * Get product Information
     * @param integer $productId
     * @return object
     */
    public function getProductInfo($productId)
    {
        return Mage::getModel('catalog/product')->load($productId);
    }

    /**
     * Get attribute options array
     * @param object $product
     * @param string $attribute
     * @return array
     */
    public function getOption($product, $attribute)
    {
        $superAttribute = array();
        if ($product->isConfigurable()) {
            $superAttribute = Mage::getModel('qquoteadv/configurable')->getSelectedAttributesInfoText($product, $attribute);
        }

        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
            $superAttribute = Mage::helper('qquoteadv')->getSimpleOptions($product, unserialize($attribute));
        }
        return $superAttribute;
    }


    /**
     * Get Product information from qquote_request_item table
     * @param $id
     * @param $quote
     * @return object
     */
    public function getRequestedProductData($id, $quote)
    {
        $product = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($quote)
            ->addFieldToFilter('quoteadv_product_id', $id);

        $product->getSelect()->order('request_qty asc');
        return $product;
    }

    /**
     * Return quote
     *
     * @return object
     */
    public function getQuoteInfo()
    {
        return $this->getQuoteData(false);
    }

    /**
     * Returns the shipping price for this quote
     *
     * @return null
     */
    public function getQuoteShipPrice()
    {
        if(!$this->shippingPrice){
            $this->shippingPrice = $this->getQuoteInfo()->getShippingPrice();
        }
        //0.00 is also a price
        return ($this->shippingPrice > -1) ? Mage::app()->getStore()->roundPrice($this->shippingPrice) : null;
    }

    /**
     * Returns if a shipping price is available
     *
     * @return bool
     */
    public function isAvaliableShipPrice()
    {
        //0.00 is also a price
        return ($this->getQuoteShipPrice() > -1) ? true : false;
    }

    /**
     * Get Quote information from qquote_customer table
     * @param bool $collectTotals
     * @return object
     * @throws Exception
     */
    public function getQuoteData($collectTotals = true)
    {
        if (!$this->quote || $collectTotals) {
            $quoteId = $this->getRequest()->getParam('id');
            $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);

            // Set correct currency for the quote
            $quoteCurrency = $quote->getCurrency();
            if (!empty($quoteCurrency)) {
                $quote->getStore()->setCurrentCurrency(
                    Mage::getModel('directory/currency')->load($quote->getCurrency())
                );
            }

            // Collect totals
            if ($collectTotals) {
                $quote->collectTotals();
            }

            $this->quote = $quote;
        }

        return $this->quote;
    }

    /**
     * Returns the shipping address in a given format
     * Default format is html
     *
     * @param $customer_id
     * @param string $format
     * @return null
     */
    public function getShippingAddress($customer_id, $format = "html")
    {
        $customer = $this->getCustomer($customer_id);
        $address = $customer->getDefaultShippingAddress();

        if (!$address) {
            foreach ($customer->getAddresses() as $address) {
                if ($address) {
                    break;
                }
            }
        }

        if (!$address) {
            return null;
        }

        return $address->format($format);
    }

    /**
     * Get the group name of a customer based on the customer id and optionally the customer group id
     *
     * @param $customer_id
     * @param null $customerGroupId
     * @return null
     */
    public function getCustomerGroupName($customer_id, $customerGroupId = null)
    {
        if(!$this->customerGroupCode){
            if($customerGroupId == null){
                $customer = $this->getCustomer($customer_id);
                $groupId = $customer->getGroupId();
                if ($groupId) {
                    $customerGroupId = $groupId;
                }
            }

            if($customerGroupId != null){
                $this->customerGroupCode = Mage::getModel('customer/group')
                    ->load($customerGroupId)
                    ->getCustomerGroupCode();
            }
        }

        return $this->customerGroupCode;
    }

    /**
     * Returns the edit/view customer url based on the given customer id
     *
     * @param $customer_id
     * @return mixed
     */
    public function getCustomerViewUrl($customer_id)
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $customer_id));
    }

    /**
     * Returns the customer name based on the given customer id
     *
     * @param $customer_id
     * @return mixed
     */
    public function getCustomerName($customer_id)
    {
        return $this->getCustomer($customer_id)->getName();
    }

    /**
     * Get the store info based on the given store id
     * Has a fallback to the default store
     *
     * @param $storeId
     * @return string
     */
    public function getStoreViewInfo($storeId)
    {
        if (!$storeId) {
            $storeId = Mage::app()->getDefaultStoreView()->getId();
        }

        $store = Mage::app()->getStore($storeId);
        $params[] = $store->getWebsite()->getName();
        $params[] = $store->getGroup()->getName();
        $params[] = $store->getName();

        return implode('<br />', $params);
    }

    /**
     * Accept option value and return its formatted view
     *
     * @param mixed $optionValue
     * Method works well with these $optionValue format:
     *      1. String
     *      2. Indexed array e.g. array(val1, val2, ...)
     *      3. Associative array, containing additional option info, including option value, e.g.
     *          array
     *          (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *          )
     *
     * @return array
     */
    public function getFormatedOptionValue($optionValue)
    {
        /* @var $helper Mage_Catalog_Helper_Product_Configuration */
        $helper = Mage::helper('catalog/product_configuration');
        $params = array(
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots" onclick="return false">...</a>'
        );
        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * Gets the admin name based on a give admin user id
     *
     * @param $id
     * @return mixed
     */
    public function getAdminName($id)
    {
        return Mage::helper('qquoteadv')->getAdminName($id);
    }

    /**
     * Create update button
     *
     * @param
     * @param string $onclick
     * @return Mage_Core_Block_Abstract
     */
    public function getUpdateTotalButton($status = null, $onclick = '')
    {

        if ($status == 'disabled') {
            $class = 'disabled';
        } else {
            $class = '';
            $onclick = "event.preventDefault(); $('loading-mask').show(); save();";
        }

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setOnclick($onclick);

        $button->setLabel(Mage::helper('checkout')->__('Update Total'));
        $button->setClass($class);

        return $button;
    }

    /**
     * ** DEPRECATED **
     * Create Coupon button
     *
     * @param
     * @return Mage_Core_Block_Abstract
     */
    public function getCouponButton()
    {

        $class = '';
        $onclick = "event.preventDefault(); $('loading-mask').show(); save();";

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setOnclick($onclick);

        $button->setLabel(Mage::helper('rule')->__('Apply'));
        $button->setClass($class);

        return $button;
    }

    /**
     * ** DEPRECATED **
     * Create FixedPrice button
     *
     * @param
     * @return Mage_Core_Block_Abstract
     */
    public function getFixedPriceButton()
    {

        $class = '';
        $onclick = "event.preventDefault(); $('loading-mask').show(); save();";

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setOnclick($onclick);

        $button->setLabel($this->__('Recalculate'));
        $button->setClass($class);

        return $button;
    }

    /**
     * Create a save button
     *
     * @param Varien_Object $vars
     * @param bool $onclick
     * @return bool
     */
    public function getSaveButton($vars = null, $onclick = false)
    {
        if (!$vars instanceof Varien_Object) {
            return false;
        }

        if(!$onclick){
            $onclick = (!$vars->getData('onclick')) ? "event.preventDefault(); $('loading-mask').show(); save();" : "event.preventDefault(); " . $vars->getData('onclick');
        }

        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setOnclick($onclick);

        $button->setLabel($vars->getData('label'));
        $button->setClass($vars->getData('class'));

        return $button;
    }

    /**
     * Setup the extra fields block as child for this block.
     */
    public function setExtraFieldsBlock()
    {
        if (Mage::app()->getHelper('qquoteadv/licensechecks')->isAllowedCustomFields()) {
            $childBlock = Mage::getSingleton('core/layout')
                ->createBlock('qquoteadv/adminhtml_qquoteadv_quotedetails_extrafields')
                ->setTemplate('qquoteadv/details/extra_fields.phtml');
            $this->setChild('quoteExtraFields', $childBlock);
        }
    }

    /**
     * Setup the extra fields block as child for this block.
     */
    public function setMultiUploadBlock(){
        $childBlock =  Mage::getSingleton('core/layout')
            ->createBlock('qquoteadv/adminhtml_qquoteadv_quotedetails_multiupload')
            ->setTemplate('qquoteadv/details/multi_upload.phtml');
        $this->setChild('quoteMultiUpload', $childBlock);
    }

    /**
     * Setup the CRMaddon block as child for this block.
     */
    public function setCrmAddonAttachmentBlock(){
        if(Mage::helper('core')->isModuleEnabled('Ophirah_Crmaddon')) {
            $childBlock = Mage::getSingleton('core/layout')
                ->createBlock('crmaddon/adminhtml_attachment')
                ->setTemplate('qquoteadv/crmaddon/attachment.phtml');
            $this->setChild('crmaddon.attachment', $childBlock);
        }
    }

    /**
     * Setup the CRMaddon new attachment block as child for this block.
     */
    public function setCrmAddonNewAttachmentBlock(){
        if(Mage::helper('core')->isModuleEnabled('Ophirah_Crmaddon')) {
            $childBlock = Mage::getSingleton('core/layout')
                ->createBlock('crmaddon/adminhtml_attachment')
                ->setTemplate('qquoteadv/crmaddon/attachment_new.phtml');
            $this->setChild('crmaddon.attachment.new', $childBlock);
        }
    }

    /**
     * Get the multi currency rate
     *
     * @return mixed
     */
    public function getRate(){
        if(!$this->rate){
            $this->rate = $this->getQuoteModel()->getData('base_to_quote_rate');
            if(!$this->rate){
                $this->rate = $this->getQuoteModel()->getBaseToQuoteRate();
            }
        }

        return $this->rate;
    }

    /**
     * Get the totals model
     *
     * @return mixed
     */
    public function getQuoteTotalsModel(){
        $currency = $this->getQuoteModel()->getData('currency');
        $_qTotals = Mage::getModel('qquoteadv/quotetotal');
        $_qTotals->setTotalRate($this->getRate());
        $_qTotals->setTotalCurrency($currency);
        $_qTotals->setQuoteStore($this->getQuoteModel()->getStoreId());
        $_qTotals->setTotalCurrencyCode($currency);
        return $_qTotals;
    }

    /**
     * Setting Trial Hash data
     * @return array
     */
    public function getHash(){
        $createHash = array();
        $createHash[] = ($this->getQuoteModel()->getData('create_hash')) ? $this->getQuoteModel()->getData('create_hash') : '';
        $createHash[] = ($this->getQuoteModel()->getData('increment_id')) ? $this->getQuoteModel()->getData('increment_id') : '';
        return $createHash;
    }

    /**
     * Returns the out of stock error for a give product
     *
     * @param $product
     * @return string
     */
    public function getOutOfStockError($product){
        $html = '';
        if (!$product->getStockItem()->getIsInStock()){
            $html .= '<div class="error">';
            $html .=    '<div style="font-size:95%;">';
            $html .=        Mage::helper('cataloginventory')->__('This product is currently out of stock.');
            $html .=    '</div>';
            $html .= '</div>';
        }
        return $html;
    }

    /**
     * Returns the product disabled error for a give product
     *
     * @param $product
     * @return string
     */
    public function getProductStatsError($product){
        $html = '';
        if ($product->getStatus() == 2){
            $html .= '<div class="error">';
            $html .=     '<div style="font-size:95%;">';
            $html .=       Mage::helper('adminhtml')->__('This product is currently disabled.');
            $html .=       '<br/>';
            $html .=       '<em>'. Mage::helper('adminhtml')->__('To sent a proposal enable this product.').'</em>';
            $html .=    '</div>';
            $html .=  '</div>';
        }
        return $html;
    }

    /**
     * Returns the HTML for the options of a given bundle
     *
     * @param $product
     * @param $item
     * @return string
     */
    public function getBundleOptionsHtml($product, $item){
        $product->setStoreId($item->getStoreId() ? $item->getStoreId() : 1);
        $_unit = $this->getMagentoQuoteItem($item);
        $_options = Mage::helper('bundle/catalog_product_configuration')->getOptions($_unit);
        return $this->getOptionHtml($_options);
    }

    /**
     * Returns the HTML for the options of a given product
     *
     * @param $product
     * @param $item
     * @return string
     */
    public function getProductOptionsHtml($product, $item){
        $x = Mage::helper('qquoteadv')->getQuoteItem($product, $item->getAttribute(), $this->getQuoteModel(), $item);
        $html = '';
        foreach ($x->getAllItems() as $_zz){
            $_zz->setQuote($this->getQuoteModel());
            if ($_zz->getProductId() == $product->getId()) {
                switch ($product->getTypeId()) {
                    case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                        $obj = new Ophirah_Qquoteadv_Block_Item_Renderer_Configurable;
                        $obj->setTemplate('qquoteadv/item/configurable.phtml');
                        $obj->setItem($_zz);
                        break;

                    default:
                        $obj = new Ophirah_Qquoteadv_Block_Item_Renderer;
                        $obj->setTemplate('qquoteadv/item/default.phtml');
                        $obj->setItem($_zz);
                        break;
                }
                $_options = $obj->getOptionList();
                $html .= $this->getOptionHtml($_options);
            }
        };
        return $html;
    }

    /**
     * Returns the HTML for the options of a given downloadable product
     *
     * @param $item
     * @return string
     */
    public function getDownloadableOptions($item){
        $qqadvproductdownloadable = Mage::getModel('qquoteadv/qqadvproductdownloadable');
        $html = '';
        $qqadvproductdownloadable->loadProduct($item);

        $links = $qqadvproductdownloadable->getLinks();
        if ($links) {
            $html .= '<dl class="item-options">';
            $html .= '<dt>' . $qqadvproductdownloadable->getLinksTitle() . '</dt>';
            foreach ($links as $link) {
                $html .= '<dd>' . $this->escapeHtml($link->getTitle());
                $html .= '</dd>';
            }
            $html .= '</dl>';
        }
        return $html;
    }

    /**
     * Get the given options formatted in html
     *
     * @param $options
     * @return string
     */
    public function getOptionHtml($options){
        $html = '';
        if (is_array($options)) {
            $html .= '<dl class="item-options">';
            foreach ($options as $option) {
                $html .= '<dt>' . $option['label'] . ':</dt>';
                $html .= '<dd>';
                if (isset($option['custom_view']) && $option['custom_view']) {
                    $html .= $option['value'];
                } else {
                    if (is_array($option['value'])) {
                        foreach ($option['value'] as $optionValue) {
                            if (!is_array($optionValue)) {
                                $html .= $optionValue . '<br>';
                            } else {
                                $html .= $optionValue['nameandprice'] . '<br>';
                            }
                        }
                    } else {
                        // Todo: $_remainder?
                        if(!isset($_remainder)){
                            $_remainder = '';
                        }
                        $html .= Mage::helper('core/string')->truncate($option['value'], 45, '', $_remainder);
                        if (!empty($_remainder)) {
                            $html .= '... <span';
                            $html .= 'id="' . $_id = 'id' . uniqid() . '">' . $_remainder . '</span>';
                            $html .= '<script type="text/javascript">';
                            $html .= "$('" . $_id . "').hide();
                                        $('" . $_id . "').up().observe('mouseover', function () {
                                            $('" . $_id . "').show();
                                        });
                                        $('" . $_id . "').up().observe('mouseout', function () {
                                            $('" . $_id . "').hide();
                                        });
                                    </script>";
                        }
                    }
                }
                $html .= '</dd>';

            }
            $html .= '</dl>';
        }
        return $html;
    }

    /**
     * Get parsed qty depending on is_qty_decimal
     * @param Mage_Catalog_Model_Product $product
     * @param $value
     * @return int|string
     */
    protected function _getQtyValue(Mage_Catalog_Model_Product $product, $value)
    {
        $usesDecimalQty = $product->getStockItem()->getData('is_qty_decimal');
        if($usesDecimalQty){
            return number_format($value, 2);
        }
        return $this->_toInt($value);
    }

    /**
     * Convert a value to an integer
     *
     * @param $value
     * @return int
     */
    protected function _toInt(&$value)
    {
        return intval($value);
    }

    /**
     * Get the Qty html for a given collection of quote items
     * Todo: Refactor and split this in smaller functions
     *
     * @param $collection
     * @param $disabledHtmlRadio
     * @param $inputDisabled
     * @param $product
     * @param $q2qKey
     * @param $disabledHtml
     * @param $_qTotals
     * @return array
     */
    public function getQtyHtml(
        $collection,
        $disabledHtmlRadio,
        $inputDisabled,
        $product,
        $q2qKey,
        $disabledHtml,
        $_qTotals
    ) {
        $hideRadio = true;
        $prices = array();
        $margins = array();
        $customPriceLineHtml = array();
        $rowTotalLine = array();
        if (count($collection) > 0):

            $i = 0;
            foreach ($collection as $requested_item):
                // ROW CUSTOM PRICE START
                $tire_qty = $requested_item->getRequestQty();
                $line = $this->_getQtyValue($product, $tire_qty).'<input type="hidden" name="product[' . $requested_item->getId() . '][qty]" value="' . $requested_item->getRequestQty() . '">';
                $customPriceLineHtml[] = $line;
                // ROW CUSTOM PRICE END

                // ROW TOTAL START
                $requested_item->calcRowTotal();
                $rowTotalLine[] =
                    '<div  style="height:25px; font-weight:bold;" class="price">'.
                    Mage::app()->getStore($_qTotals->getQuoteStore())
                        ->formatPrice(
                            $requested_item->getRowTotal()
                        ).
                    '</div>';
                // ROW TOTAL END

                $showPrice = $requested_item->getOriginalCurPrice();
                $ownerPrice = $requested_item->getOwnerCurPrice();
                if ($ownerPrice !== null && $ownerPrice >= 0) {
                    $showPrice = $ownerPrice;
                }

                if (strlen(substr(strrchr($showPrice, "."), 1)) < 2) {
                    $showPrice = Mage::app()->getStore()->roundPrice($showPrice);
                } else {
                    $showPrice = round($showPrice, 4);
                }

                if (strlen(substr(strrchr($ownerPrice, "."), 1)) < 2) {
                    $ownerPrice = Mage::app()->getStore()->roundPrice($ownerPrice);
                } else {
                    $ownerPrice = round($ownerPrice, 4);
                }

                $priceLine = array();

                if ($hideRadio) {
                    $radioStates = array('unselected' => $disabledHtmlRadio, 'selected' => 'checked="checked"');
                    foreach ($radioStates as $state => $radioState) {
                        $priceLine[$state] = '&nbsp;<input ' . $radioState . ' class="rbt" type="radio" name="q2o[' . $q2qKey . ']" value="' . $requested_item->getId() . '">&nbsp;&nbsp;';
                    }
                }

                $url = $this->getUrl('*/*/deleteQtyField', array('request_id' => $requested_item->getId()));
                $priceLine['value'] = '<input type="text" name="product[' . $requested_item->getId() . '][price]"
                    value="' . $showPrice . '" size="3" class="required-entry validate-zero-or-greater required-entry input-text proposalprice sku-' . $product->getSku() . '"  style="width:70px;" id="price-' . $requested_item->getId() . '" ' . $disabledHtml . '>';

                if (!$inputDisabled) {
                    $priceLine['value'] .= '&nbsp;<a title="' . $this->__('Delete') . '" href="' . $url . '"><img align="absmiddle" src="' . $this->getSkinUrl('images/minus-icon.png') . '" width="16" height="16" alt="' . $this->__('Remove item') . '" /></a>';
                }

                $priceLine['req_qty'] = $requested_item->getRequestQty();

                $priceLine['quotePrice'] = (isset($ownerPrice)) ? $ownerPrice : $showPrice;

                $prices[] = $priceLine;

                $margins[] = '<div style="height:25px;" id="margin-' . $requested_item->getId() . '"></div>';
                $i++;
            endforeach;
        else:
            $request_item = null;
        endif;
        return array(
            'prices' => $prices,
            'margins' => $margins,
            'customPriceLineHtml' => $customPriceLineHtml,
            'hideRadio' => $hideRadio,
            'rowTotalLine' => $rowTotalLine
        );

    }

    /**
     * Get the cost html for a given collection of quote items
     *
     * @param $collection
     * @param $product
     * @param $_qTotals
     * @param $item
     * @return string
     */
    public function getCostHtml($collection, $product, $_qTotals, $item){
        $cost = null;
        $html = '';
        $qqadvproduct = Mage::getModel('qquoteadv/qqadvproduct');

        if (count($collection) > 0){
            foreach ($collection as $requested_item){
                if (isset($requested_item) and is_object($requested_item)) {
                    $requestId = $requested_item->getRequestId();
                    $cost = $qqadvproduct->getQuoteItemCost($product, $requested_item->getQuoteadvProductId(), $requestId);

                    $html .= '<div id="price-cost-'.$requested_item->getId().'" style="height: 25px;">';

                    if ($cost && $requested_item->getData('cost_price')) {
                        $html .= '<div class="cost-price-' . $requestId . '">';
                        $html .= Mage::app()->getStore($_qTotals->getQuoteStore())->formatPrice($cost);
                        $html .= $this->getChild('cart2quote.quotedetails.costprice')->setRequestId($requestId)->setCostPrice($cost * 1)->toHtml();
                        $html .= '</div>';
                    } elseif ($cost && !$requested_item->getData('cost_price')) {
                        $html .= '<div class="cost-price-' . $requestId . '">';
                        $html .= Mage::app()->getStore($_qTotals->getQuoteStore())->formatPrice($cost * $this->getRate());
                        $html .= $this->getChild('cart2quote.quotedetails.costprice')->setRequestId($requestId)->setCostPrice($cost * $this->getRate())->toHtml();
                        $html .= '</div>';
                    } else {
                        $html .= '<span class="cost-price-na">';
                        $html .= Mage::helper('sales')->__('N/A');
                        $html .= '</span>';
                        $html .= $this->getChild('cart2quote.quotedetails.costprice')->setRequestId($requestId)->setCostPrice($cost * 1)->toHtml();
                        $_qTotals->_costflag = true;
                    }

                    $html .= '</div>';

                    //check if this cost price is selected
                    if($requested_item->getRequestQty() == $item->getData('qty')) {
                        // Adding to quotetotals
                        $_quoteItems[$item->getId()]['productId'] = (int)$product->getId();

                        if ($cost && $requested_item->getData('cost_price')) {
                            $costPrice = $cost;
                        } elseif ($cost && !$requested_item->getData('cost_price')) {
                            $costPrice = $cost * $this->getRate();
                        } else {
                            $costPrice = 0;
                        }
                        $this->setQuoteItemValue($item->getId(), 'totalCost', $costPrice);
                    }
                }
            }
        }
        return $html;
    }

    /**
     * Sets item data on this block.
     * @param $itemId
     * @param $type
     * @param $value
     */
    public function setQuoteItemValue($itemId, $type, $value){
        $quoteItems = $this->getQuoteItems();
        if(!is_array($quoteItems)){
            $quoteItems = array();
        }

        $newData = array();
        $newData[$itemId][$type] = $value;

        $quoteItems = $quoteItems + $newData;

        $this->setQuoteItems($quoteItems);
    }

    /**
     * Get the item value of a give type
     *
     * @param $itemId
     * @param $type
     * @return bool
     */
    public function getQuoteItemValue($itemId, $type){
        $quoteItems = $this->getQuoteItems();
        if(is_array($quoteItems)) {
            if (array_key_exists($itemId, $quoteItems)) {
                if (array_key_exists($type, $quoteItems[$itemId])) {
                    return $quoteItems[$itemId][$type];
                }
            }
        }
        return false;
    }

    /**
     * Returns the customer for the given customer id
     *
     * @param $customerId
     * @return Mage_Core_Model_Abstract
     */
    public function getCustomer($customerId){
        if(!$this->customer){
            $this->customer = Mage::getModel('customer/customer')->load($customerId);
        }

        return $this->customer;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('qquoteadv')->__('Quote Request');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return 'Quote Request';
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Gets allowed websites for the customer
     *
     * @return array|Mage_Core_Model_Abstract
     */
    public function getQuoteWebsites()
    {
        if (Mage::getStoreConfig('customer/account_share/scope', $this->getStoreId()) == 0) {
            $websites = Mage::app()->getWebsites();
        } else {
            $websiteId = $this->customer->getWebsiteId();
            $websites = array(Mage::getModel('core/website')->load($websiteId));
        }
        return $websites;
    }

    /**
     * Counts the allowed store views for the customer
     *
     * @return int
     */
    public function countStoreViews()
    {
        $countStoreViews = 0;
        $websites = $this->getQuoteWebsites();
        foreach ($websites as $website) {
            $countStoreViews += $this->countStoresFromWebsite($website);
        }
        return $countStoreViews;
    }

    /**
     * Counts the Stores from Website
     *
     * @param $website
     * @return int
     */
    public function countStoresFromWebsite($website)
    {
        $storeViews = 0;
        $storeIds = $website->getStoreIds();
        if (in_array($this->quote->getStoreId(), $storeIds)) {
            $storeViews = count($website->getStores());
        }
        return $storeViews;
    }
}
