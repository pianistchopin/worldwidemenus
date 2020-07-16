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
 * Class Ophirah_Qquoteadv_Model_Address
 */
class Ophirah_Qquoteadv_Model_Address extends Mage_Sales_Model_Quote_Address
{
    CONST DEFAULT_DEST_STREET = -1;

    /**
     * @var null
     */
    protected $_quote = null;

    /**
     * @var null
     */
    protected $_rates = null;

    /**
     * @var null
     */
    protected $_itemsQty = null;

    /**
     * @var null
     */
    public $_shippingRates = null;

    /**
     * Prefix of model events
     *
     * @var string
     */
    protected $_eventPrefix = 'ophirah_qquoteadv_address';

    /**
     * Name of event object
     *
     * @var string
     */
    protected $_eventObject = 'quoteadv_address';

    /**
     * Override resource as we are defining the field ourselves
     */
    protected function _construct()
    {
        $this->_init('qquoteadv/address');
    }

    /**
     * Init mapping array of short fields to its full names
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _initOldFieldsMap()
    {
        return $this;
    }

    /**
     * Initialize quote identifier before save
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        return $this;
    }

    /**
     * Declare adress quote model object
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Retrieve address items collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getItemsCollection()
    {
        if (is_null($this->_items)) {
            $items = $this->getAllItems();
            foreach ($items as $item) {
                $item->setAddress($this);
                $item->setQuote($this->getQuote());
            }
        }
        return $items;
    }

    /**
     * Get all available address items
     *
     * @param bool $forceReload
     * @return array
     */
    public function getAllItems($forceReload = false)
    {
        return $this->getQuote()->getAllRequestItems(true, $forceReload);
    }

    /**
     * Get combined weight of the
     * quote products
     *
     * @return float
     */
    public function getWeight()
    {
        if ($this->getQuote() instanceof Ophirah_Qquoteadv_Model_Qqadvcustomer) {
            $weight = $this->getQuote()->getWeight();
        }

        return $weight;
    }

    /**
     * Retrieve the sume of item quantity's of all shippable items on this address
     *
     * @param $itemId (required for declaration warning)
     * @return float|int
     */
    public function getItemQty($itemId = 0)
    {
        if ($this->_itemsQty == null) {
            $this->_itemsQty = 0;
            $items = $this->getAllItems();
            foreach ($items as $item) {
                // skip non visible items
                if ($item->getParentItem()) {
                    continue;
                }
                // If items get shipped seperatly
                if ($item->isShipSeparately() && $item->getData('qty_options')) {
                    foreach ($item->getData('qty_options') as $optionItem) {
                        $this->_itemsQty += $optionItem->getProduct()->getData('qty');
                    }
                } else {
                    $this->_itemsQty += $item->getData('qty');
                }
            }
        }

        return $this->_itemsQty;
    }


    /**
     * Add item to address
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract|Ophirah_Qquoteadv_Model_Requestitem $item
     * @param   int $qty
     * @return Mage_Sales_Model_Quote_Address
     */
    public function addItem(Mage_Sales_Model_Quote_Item_Abstract $item, $qty = null)
    {

        return $this;
    }

    /**
     * Getter for address id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getData('address_id');
    }

    /**
     * Overwrite for getCollectShippingRates to return always true
     *
     * @return bool
     */
    public function getCollectShippingRates()
    {
        return true;
    }

    /**
     * Clear $_rates to
     * rebuild shippingrates collection
     */
    public function clearRates()
    {
        $this->_rates = null;
    }

    /**
     * Retrieve collection of quote shipping rates
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getShippingRatesCollection()
    {
        if ($this->_rates == null) {
            $this->_rates = array();
            if ($this->getQuote()->getIsCustomShipping()) {

                $price = $this->getQuote()->getShippingBasePrice();

                if ($this->getQuote()->getShippingType() == "I") {
                    $price = ($price * $this->getQuote()->getItemsQty());
                }

                //$this->getQuote()->getStoreId();
                $carrier = Mage::getStoreConfig("carriers/qquoteshiprate/title", $this->getQuote()->getStoreId());
                $method = Mage::getStoreConfig("carriers/qquoteshiprate/name", $this->getQuote()->getStoreId());
                $methodDescription = $carrier . " - " . $method;

                $rate = Mage::getModel('qquoteadv/shippingrate');
                $rate->setData('carrier', 'qquoteshiprate');
                $rate->setData('carrier_title', $carrier);
                $rate->setData('price', $price);
                $rate->setData('cost', $price);
                $rate->setData('method', 'qquoteshiprate');
                $rate->setData('method_title', $method);
                $rate->setData('method_description', $methodDescription);
                $quoteRate = Mage::getModel('sales/quote_address_rate')->importShippingRate($rate);
                $this->_rates = array($quoteRate);
            } else {
                // Note: we cant use $this->collectShippingRates(); here,
                // that would cause an infinite loop
                $this->_rates = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
                    ->addFieldToFilter('address_id', array('eq' => $this->getData('address_id')))
                    ->addFieldToFilter('active', array('eq' => 1));

                if ($this->hasNominalItems(false)) {
                    $this->_rates->setFixedOnlyFilter(true);
                }
                if ($this->getId()) {
                    foreach ($this->_rates as $rate) {
                        $rate->setAddress($this);

                    }
                }
            }
        }

        return $this->_rates;
    }

    /**
     * Function that collects/calculates all shipping rates for this address
     *
     * @return $this
     */
    public function collectShippingRates()
    {
        if (!$this->getCollectShippingRates()) {
            return $this;
        }

        $this->removeAllShippingRates();

        if (!$this->getCountryId()) {
            return $this;
        }
        $found = $this->requestShippingRates();
        if (!$found) {
            $this->setShippingAmount(0)
                ->setBaseShippingAmount(0)
                ->setShippingMethod('')
                ->setShippingDescription('');
        }
        return $this;
    }

    /**
     * Request shipping rates for entire address or specified address item
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return bool
     */
    public function requestShippingRates(Mage_Sales_Model_Quote_Item_Abstract $item = null)
    {
        /** @var $request Mage_Shipping_Model_Rate_Request */
        $request = Mage::getModel('shipping/rate_request');
        $request->setAllItems($item ? array($item) : $this->getAllItems());
        $request->setDestCountryId($this->getCountryId());
        $request->setDestRegionId($this->getRegionId());
        $request->setDestRegionCode($this->getRegionCode());

        //need to call getStreet with -1
        //to get data in string instead of array
        $request->setDestStreet($this->getStreet(self::DEFAULT_DEST_STREET));
        $request->setDestCity($this->getCity());
        $request->setDestPostcode($this->getPostcode());
        $request->setPackageValue($item ? $item->getBaseRowTotal() : $this->getBaseSubtotal());
        $packageValueWithDiscount = $item
            ? $item->getBaseRowTotal() - $item->getBaseDiscountAmount()
            : $this->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageValueWithDiscount);
        $request->setPackageWeight($item ? $item->getRowWeight() : $this->getWeight());

        $request->setPackageQty($item ? $item->getQty() : $this->getItemQty());

        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $packagePhysicalValue = $item
            ? $item->getBaseRowTotal()
            : $this->getBaseSubtotal() - $this->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);

        // Makes sure that free method weight is never NULL
        // Partly free shipping is not supported!
        $altFreeMethodWeight = $this->getData('free_method_weight');
        if($altFreeMethodWeight === null || $altFreeMethodWeight == 0){
            if($this->getFreeShipping()){
                $altFreeMethodWeight = 0;
            } else {
                $altFreeMethodWeight = $this->getWeight();
            }
        }

        $request->setFreeMethodWeight($item ? 0 : $altFreeMethodWeight);

        /**
         * Store and website identifiers need specify from quote
         */
        $request->setStoreId($this->getQuote()->getStore()->getId());
        $request->setWebsiteId($this->getQuote()->getStore()->getWebsiteId());
        $request->setFreeShipping($this->getFreeShipping());

        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($this->getQuote()->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->getQuote()->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($this->getLimitCarrier());

        $request->setBaseSubtotalInclTax($this->getBaseSubtotalInclTax() + $this->getBaseExtraTaxAmount());

        //support for MageWorx_DeliveryZone
        if(Mage::helper('core')->isModuleEnabled('MageWorx_DeliveryZone')){
            $message = 'Make sure to enable the session overwrites in config.xml to be compatible with MageWorx_DeliveryZone';
            Mage::log('Note: ' .$message, null, 'c2q.log');

            Mage::getSingleton('adminhtml/session_quote')->setQuote($this->getQuote());
            Mage::getSingleton('checkout/session')->setQuote($this->getQuote());
        }

        $result = Mage::getModel('shipping/shipping')->collectRates($request)->getResult();

        //support for MageWorx_DeliveryZone
        if(Mage::helper('core')->isModuleEnabled('MageWorx_DeliveryZone')){
            Mage::getSingleton('adminhtml/session_quote')->unsetQuote();
            Mage::getSingleton('checkout/session')->unsetQuote();
        }

        $found = false;
        if ($result) {
            $shippingRates = $result->getAllRates();

            // Reset existing rates
            if ($shippingRates) {
                // Load QuoteRate Collection
                $collection = Mage::getModel('qquoteadv/quoteshippingrate')->resetQuoteRates($this->getData('address_id'));
            }

            foreach ($shippingRates as $shippingRate) {
                $rate = Mage::getModel('sales/quote_address_rate')
                    ->importShippingRate($shippingRate);

                if (!$item) {
                    $this->addQuoteShippingRate($rate, $collection);
                }

                if ($this->getShippingMethod() == $rate->getCode()) {
                    if ($item) {
                        $item->setBaseShippingAmount($rate->getPrice());
                    } else {
                        /**
                         * possible bug: this should be setBaseShippingAmount(),
                         * see Mage_Sales_Model_Quote_Address_Total_Shipping::collect()
                         * where this value is set again from the current specified rate price
                         * (looks like a workaround for this bug)
                         */

                        $this->setBaseShippingAmount($rate->getPrice());
                    }

                    $found = true;
                }
            }
            /**
             * Make Magento load shipping prices from the quoteadv_shipping_rate table instead of old stored data
             */
            if ($found) {
                $this->_rates = null;
            }
        }

        return $found;
    }

    /**
     * Add / Update Quote Shipping rate table
     *
     * @param Mage_Sales_Model_Quote_Address_Rate $rate
     * @param null $collection
     * @return $this
     * @throws Exception
     */
    public function addQuoteShippingRate(Mage_Sales_Model_Quote_Address_Rate $rate, $collection = null)
    {
        if ($collection == null) {
            $collection = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
                ->addFieldToFilter('address_id', array('eq' => $this->getData('address_id')))
                ->load();
        }

        // update existing shipping data
        if (!$collection === false) {
            foreach ($collection as $updateRate) {
                if ($updateRate->getData('code') == $rate->getData('code')) {
                    $updateRate->addData($rate->getData());
                    $updateRate->setData('updated_at', now());
                    $updateRate->setData('active', 1);
                    $updateRate->save();

                    return $this;
                }
            }
        }

        // Add new shippingdata
        $newRate = Mage::getModel('qquoteadv/quoteshippingrate');
        $newRate->addData($rate->getData());
        $newRate->setData('address_id', $this->getData('address_id'));
        $newRate->setData('created_at', now());
        $newRate->setData('updated_at', now());
        $newRate->save();

        return $this;
    }

    /**
     * Retrieve all address shipping rates
     *
     * @return array
     */
    public function getAllShippingRates()
    {
        $rates = array();
        foreach ($this->getShippingRatesCollection() as $rate) {
            $rates[] = $rate;
        }
        return $rates;
    }

    /**
     * Get totals collector model
     *
     * @return Mage_Sales_Model_Quote_Address_Total_Collector
     */
    public function getTotalCollector()
    {
        if ($this->_totalCollector === null) {
            $this->_totalCollector = Mage::getSingleton(
                'sales/quote_address_total_collector',
                array('store' => $this->getQuote()->getStore())
            );
        }
        return $this->_totalCollector;
    }

    /**
     * Retrieve total models
     *
     * @deprecated
     * @return array
     */
    public function getTotalModels()
    {
        return $this->getTotalCollector()->getRetrievers();
    }

    /**
     * Collect address totals
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function collectTotals()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_before', array($this->_eventObject => $this));

        $qquote = $this->getQuote();
        $storeId = $qquote->getStoreId();
        $store = Mage::app()->getStore($storeId);
        Mage::getSingleton('customer/session')->setCustomerId($this->getCustomerId());

        if ($qquote->getData('currency')) {
            $store->getCurrentCurrency()->setCurrencyCode($qquote->getData('currency'));
            $store->getBaseCurrency()->setRates(array(
                $qquote->getData('currency') => $qquote->getData('base_to_quote_rate')
            ));
        }

        $collectors = $this->getTotalCollector()->getCollectors();
        $collectors['c2qtotal']->collect($this);

        //Check if freeshipping is enabled before calculating it
        $freeShipping = Mage::getStoreConfig('carriers/freeshipping/active', $store);
        if(!empty($freeShipping) && $freeShipping == 1){
            $collectors['freeshipping']->collect($this);
        }

        $collectors['tax_subtotal']->collect($this);

        //Check if weee is enabled before calculating it
        $weee = Mage::getStoreConfig('tax/weee/enable', $store);
        if(!empty($weee) && $weee == 1){
            $collectors['weee']->collect($this);
        }

        $collectors['shipping']->collect($this);
        $collectors['tax_shipping']->collect($this);
        $collectors['discount']->collect($this);
        $collectors['tax']->collect($this);

        //surcharge, after tax calculation and using calculate or collect based on version number
        if(Mage::helper('core')->isModuleEnabled('Fooman_Surcharge')){
            $version = Mage::getConfig()->getNode()->modules->Fooman_Surcharge->version;

            //for version 2 use calculate and request cart2quote for a customisation
            if ((version_compare($version, '1.0.0') >= 0) && version_compare($version, '3.0.0') < 0) {
                if(array_key_exists('surcharge', $collectors)){
                    $collectors['surcharge']->calculate($this);
                }
            }

            //for version 3 to 3.0.29 use 'surcharge' collector
            if ((version_compare($version, '3.0.0') >= 0) && version_compare($version, '3.1.0') < 0) {
                if(array_key_exists('surcharge', $collectors)){
                    $collectors['surcharge']->collect($this);
                }
            }

            //for version >= 3.1.0 'fooman_surcharge' collector
            if ((version_compare($version, '3.1.0') >= 0) && version_compare($version, '4.0.0') < 0) {
                if(array_key_exists('fooman_surcharge', $collectors)){
                    $collectors['fooman_surcharge']->collect($this);
                }
            }
        }

        $collectors['grand_total']->collect($this);

        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
        // update address table
        if ($this->getAddressId()) {
            $addresses = Mage::helper('qquoteadv/address')->getAddressCollection($this->getData('quote_id'));
            if ($addresses) {
                foreach ($addresses as $address) {
                    if ($address->getData('address_type') == $this->getData('address_type')) {
                        $address->addData($this->getData());
                        $address->save();
                    }
                }
            }
        }

        Mage::getSingleton('customer/session')->unsetData('customer_id');
        return $this;
    }

    /**
     * Validator for the required minimum order amount (if enabled)
     *
     * @return bool
     */
    public function validateMinimumAmount()
    {
        $storeId = $this->getQuote()->getStoreId();
        if (!Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId)) {
            return true;
        }

        if ($this->getQuote()->getIsVirtual() && $this->getAddressType() == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING) {
            return true;
        } elseif (!$this->getQuote()->getIsVirtual() && $this->getAddressType() != Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING) {
            return true;
        }

        $amount = Mage::getStoreConfig('sales/minimum_order/amount', $storeId);
        if ($this->getBaseSubtotalWithDiscount() < $amount) {
            return false;
        }
        return true;
    }

    /**
     * Get subtotal amount with applied discount in base currency
     *
     * @return float
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getBaseSubtotal() + $this->getBaseDiscountAmount();
    }

    /**
     * Get subtotal amount with applied discount
     *
     * @return float
     */
    public function getSubtotalWithDiscount()
    {
        return $this->getSubtotal() + $this->getDiscountAmount();
    }

    /**
     * Getter for the shipping description
     *
     * @return string
     */
    public function getShippingDescription()
    {
        return $this->getQuote()->getAddressShippingDescription();
    }

    /**
     * Setter for the shipping description
     *
     * @param string $desc
     * @return string
     */
    public function setShippingDescription($desc)
    {
        return $this->getQuote()->setAddressShippingDescription($desc);
    }

    /**
     * Function to remove all shipping rates from this address
     *
     * @return $this
     */
    public function removeAllShippingRates()
    {
        foreach ($this->getShippingRatesCollection() as $rate) {
            $rate->isDeleted(true);
        }
        return $this;
    }

    /**
     * Get all total amount values
     * Make sure total amounts are calculated
     *
     * @return array
     */
    public function getAllTotalAmounts()
    {
        if (empty($this->_totalAmounts)) {
            $this->collectTotals();
        }

        return $this->_totalAmounts;
    }

    /**
     * Getter for all non-nominal items
     * Has a fallback for 'Amasty_Conf'
     *
     * @return array
     */
    public function getAllNonNominalItems()
    {
        $items = parent::getAllNonNominalItems();
        if(Mage::helper('core')->isModuleEnabled('Amasty_Conf')){
            $message = 'Cart2Quote is not compatible with the option "Use Price of Simple Products" of Amasty - Color Swatches Pro (Amasty_Conf)';
            Mage::log('Warning: ' .$message, null, 'c2q.log');

            //force non children calculated price on configurables
            foreach($items as $item){
                if($item->getProductType() == "configurable"){
                    $item->getProduct()->setPriceType(null);
                }
            }
        }

        //force non children calculated price on configurables
        foreach($items as $key => $item){
            if(Mage::getModel('qquoteadv/qqadvproductdownloadable')->isDownloadable($item)) {
                $items[$key] = Mage::getModel('qquoteadv/qqadvproductdownloadable')->prepareDownloadableProductFromBuyRequest($item);
            }
        }

        return $items;
    }

    /**
     * checks if an address has a shipping method set
     * @return bool
     */
    public function hasShipping()
    {
        if ($this->getShippingMethod()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Todo: create a collector for Subtotal Original.
     */
    public function getSubtotalOriginal()
    {
        //$rate = $this->getQuote()->getBase2QuoteRate();
        $totalSubtotalOriginal = 0;
        foreach ($this->getQuote()->getAllRequestItems() as $item) {
            $requestItem = Mage::getModel('qquoteadv/requestitem')->load($item->getRequestItemId());

            $qty = $requestItem->getQty();
            $originalCurPrice = $requestItem->getOriginalCurPrice();
            $totalSubtotalOriginal += $originalCurPrice * $qty;
        }

        return $totalSubtotalOriginal;
    }

    /**
     * Retrieve all visible items
     *
     * @param bool $forceReload
     * @return array
     */
    public function getAllVisibleItems($forceReload = false)
    {
        $items = array();
        foreach ($this->getAllItems($forceReload) as $item) {
            if (!$item->getParentItemId()) {
                $items[] = $item;
            }
        }
        return $items;
    }
}
