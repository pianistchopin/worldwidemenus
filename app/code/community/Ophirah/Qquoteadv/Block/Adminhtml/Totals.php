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
 * Class Ophirah_Qquoteadv_Block_Totals
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Totals extends Mage_Core_Block_Template
{
    /**
     * Associated array of totals
     * array(
     *  $totalCode => $totalObject
     * )
     *
     * @var array
     */
    protected $_totals;

    /**
     * @var null
     */
    protected $_quote = null;

    /**
     * @var string
     */
    protected $_defaultRenderer = 'checkout/total_default';

    /**
     * @var Mage_Tax_Model_Config
     */
    protected $_tax;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * Initialize self totals and children blocks totals before html building
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _beforeToHtml()
    {
        $this->_initStoreId();
        $this->_initTaxHelper();
        $this->_initTotals();
        foreach ($this->getChild() as $child) {
            if (method_exists($child, 'initTotals')) {
                $child->initTotals();
            }
        }
        return parent::_beforeToHtml();
    }

    /**
     * Get order object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            if ($this->hasData('quote')) {
                $this->_quote = $this->_getData('quote');
            } elseif (Mage::registry('current_quote')) {
                $this->_quote = Mage::registry('current_quote');
            }
        }
        return $this->_quote;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder(){
        return $this->getQuote();
    }

    /**
     * Set a given order as the quote
     *
     * @param $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->_quote = $order;
        return $this;
    }

    /**
     * Get totals source object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getQuote();
    }

    /**
     * Overwrite for _initDiscount to just return $this
     *
     * @return $this
     */
    protected function _initDiscount()
    {
        return $this;
    }

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        $source = $this->getSource();
        if(!$source) {
            Mage::log('Message: No Quote available in Ophirah_Qquoteadv_Block_Totals::_initTotals', null, 'c2q.log', true);
            return $this;
        }
        if($this->getCollectTotals()){
            $source->collectTotals();
        }
        $totals = $source->getAddress();
        $storeId = $this->getQuote()->getStoreId();

        // Get Sort Order
        $sortOrder = array('subtotal' => Mage::getStoreConfig('sales/totals_sort/subtotal', $storeId),
            'discount' => Mage::getStoreConfig('sales/totals_sort/discount', $storeId),
            'shipping' => Mage::getStoreConfig('sales/totals_sort/shipping', $storeId),
            'tax' => Mage::getStoreConfig('sales/totals_sort/tax', $storeId),
            'grandtotal' => Mage::getStoreConfig('sales/totals_sort/grand_total', $storeId)
        );

        // Added Fooman Surcharge Total
        if(Mage::helper('core')->isModuleEnabled('Fooman_Surcharge')){
            $surchargeSort = Mage::getStoreConfig('sales/totals_sort/surcharge', $this->getQuote()->getStoreId());
            if($surchargeSort){
                $sortOrder['surcharge'] = $surchargeSort;
            }else{
                $sortOrder['surcharge'] = 25; // Default value
            }
        }

        $totalsOrder = array();
        // Ordering Totals
        foreach ($sortOrder as $k => $v) {
            if (!array_key_exists($v, $totalsOrder)) {
                $totalsOrder[$v] = $k;
            } else {
                $totalsOrder[] = $k;
            }
        }

        // Sorting Array
        ksort($totalsOrder);

        // Adding Totals in Order
        $this->_totals = array();

        $this->_addSubtotalOriginal();

        // Add Quote Reduction Totals
        if (Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/adjustment', $storeId) == 1) {
            if (Mage::helper('tax')->priceIncludesTax($storeId)) {
                $taxLabel = Mage::helper('qquoteadv')->__('Incl. default Tax');
                $label = Mage::helper('qquoteadv')->__('Adjustment Quote (%s)', $taxLabel);
            } else {
                $label = Mage::helper('qquoteadv')->__('Adjustment Quote');
            }

            $this->_totals['quote_reduction'] = new Varien_Object(array(
                'code' => 'quote_reduction',
                //'area' => 'body',
                'value' => -1 * $this->getQuote()->getQuoteReduction(),
                'label' => $label
            ));
        }

        $shippingType = $source->getShippingType();

        // Add other totals
        foreach ($totalsOrder as $order){
            switch ($order) {
                case 'subtotal':
                    $this->_addSubTotal($totals);
                    break;
                case 'discount':
                    $this->_addDiscount($totals);
                    break;
                // Added Fooman Surcharge Total
                case 'surcharge':
                    $this->_addSurcharge($totals);
                    break;
                case 'shipping':
                    if (!$source->getIsVirtual()
                        && ((float)$totals->getShippingAmount()
                            || $totals->getShippingDescription()
                            || (!empty($shippingType) && ($shippingType != 'none')))
                    ) {
                        $this->_addShipping($totals, $storeId);
                    }
                    break;
                case 'grandtotal':
                    $this->_addGrandTotal($totals, $source);
                    break;
            }
        }

        $this->_addQuoteProfit();

        return $this;
    }

    /**
     *  Add Subtotal to Totals
     *
     * @param Ophirah_Qquoteadv_Model_Address $totals
     */
    public function _addSubTotal($totals)
    {
        if ($this->_tax->displaySalesSubtotalExclTax($this->_storeId)){
            $this->_totals['subtotal'] = new Varien_Object(array(
                'code' => 'subtotal',
                'strong' => true,
                'value' => $totals->getSubtotal(),
                'label' => Mage::helper('tax')->__('Subtotal (Excl. Tax)')
            ));
        } elseif ($this->_tax->displaySalesSubtotalInclTax($this->_storeId)){
            $this->_totals['subtotal_incl'] = new Varien_Object(array(
                'code' => 'subtotal',
                'strong' => true,
                'value' => $totals->getSubtotalInclTax(),
                'label' => Mage::helper('tax')->__('Subtotal (Incl. Tax)')
            ));
        } elseif ($this->_tax->displaySalesSubtotalBoth($this->_storeId)){
            $this->_totals['subtotal_excl'] = new Varien_Object(array(
                'code' => 'subtotal',
                'strong' => true,
                'value' => $totals->getSubtotal(),
                'label' => Mage::helper('tax')->__('Subtotal (Excl. Tax)')
            ));
            $this->_totals['subtotal_incl'] = new Varien_Object(array(
                'code' => 'subtotal',
                'strong' => true,
                'value' => $totals->getSubtotalInclTax(),
                'label' => Mage::helper('tax')->__('Subtotal (Incl. Tax)')
            ));
        }
    }

    /**
     * Add Shipping to Totals
     *
     * @param Ophirah_Qquoteadv_Model_Address $totals
     * @param null $store
     */
    protected function _addShipping($totals, $store = null)
    {
        if($store !== null){
            if ($this->_tax->displaySalesShippingInclTax($store)) {
                $this->_totals['shipping'] = new Varien_Object(array(
                    'code' => 'shipping',
                    //'area' => 'body',
                    'field' => 'shipping_amount',
                    'value' => $totals->getShippingAmount(),
                    'label' => Mage::helper('tax')->__('Shipping & Handling (Incl.Tax)')
                ));
            }

            if ($this->_tax->displaySalesShippingExclTax($store)) {
                $this->_totals['shipping'] = new Varien_Object(array(
                    'code' => 'shipping',
                    //'area' => 'body',
                    'field' => 'shipping_amount',
                    'value' => $totals->getShippingAmount(),
                    'label' => Mage::helper('tax')->__('Shipping & Handling (Excl.Tax)')
                ));
            }

            if ($this->_tax->displaySalesShippingBoth($store)) {
                $this->_totals['shipping'] = new Varien_Object(array(
                    'code' => 'shipping',
                    //'area' => 'body',
                    'field' => 'shipping_amount',
                    'value' => $totals->getShippingAmount(),
                    'label' => Mage::helper('sales')->__('Shipping & Handling')
                ));
            }

        } else {
            $this->_totals['shipping'] = new Varien_Object(array(
                'code' => 'shipping',
                //'area' => 'body',
                'field' => 'shipping_amount',
                'value' => $totals->getShippingAmount(),
                'label' => Mage::helper('sales')->__('Shipping & Handling')
            ));
        }
    }

    /**
     * Add Surcharge to Totals
     *
     * @param Ophirah_Qquoteadv_Model_Address $totals
     */
    protected function _addSurcharge($totals){
        if ($totals->getFoomanSurchargeDescription()) {
            $surchargeLabel = $this->__($totals->getFoomanSurchargeDescription());
        } else {
            $surchargeLabel = $this->__('Surcharge');
        }

        $this->_totals['surcharge'] = new Varien_Object(array(
            'code'  => 'surcharge',
            'field' => 'surcharge_amount',
            'value' => $totals->getFoomanSurchargeAmount(),
            'label' => $surchargeLabel
        ));
    }

    /**
     * Add Discount to Totals
     *
     * @param Ophirah_Qquoteadv_Model_Address $totals
     */
    protected function _addDiscount($totals)
    {
        if ($totals->getDiscountAmount() != 0) {
            if ($totals->getDiscountDescription()) {
                $discountLabel = Mage::helper('sales')->__('Discount (%s)', $totals->getDiscountDescription());
            } else {
                $discountLabel = Mage::helper('sales')->__('Discount');
            }
            $this->_totals['discount'] = new Varien_Object(array(
                'code' => 'discount',
                'field' => 'discount_amount',
                //'area' => 'body',
                'value' => $totals->getDiscountAmount(),
                'label' => $discountLabel
            ));
        }

    }

    /**
     *  Add Tax to Totals
     *
     * @param Ophirah_Qquoteadv_Model_Address $totals
     */
    protected function _addTax($totals)
    {
        $this->_totals['tax'] = new Varien_Object(array(
            'code' => 'tax',
            'field' => 'tax',
            'area' => 'footer',
            'value' => $totals->getTaxAmount(),
            'label' => Mage::helper('tax')->__('Tax')
        ));
    }

    /**
     *  Add GrandTotal to Totals
     *
     * @param Ophirah_Qquoteadv_Model_Address $totals
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $source
     */
    protected function _addGrandTotal($totals, $source)
    {
        if ($this->_tax->displaySalesTaxWithGrandTotal($this->_storeId)){
            $this->_totals['grand_total'] = new Varien_Object(array(
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'area' => 'footer',
                'value' => $totals->getGrandTotal(),
                'label' => Mage::helper('sales')->__('Grand Total')
            ));
        } else {
            $this->_totals['grand_total_excl'] = new Varien_Object(array(
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'area' => 'footer',
                'value' => ($totals->getGrandTotal() - $totals->getTaxAmount()),
                'label' => Mage::helper('tax')->__('Grand Total (Excl.Tax)')
            ));
            $this->_totals['tax'] = new Varien_Object(array(
                'code' => 'tax',
                'field' => 'tax',
                'area' => 'footer',
                'value' => $totals->getTaxAmount(),
                'label' => Mage::helper('tax')->__('Tax')
            ));
            $this->_totals['grand_total_incl'] = new Varien_Object(array(
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'area' => 'footer',
                'value' => $totals->getGrandTotal(),
                'label' => Mage::helper('tax')->__('Grand Total (Incl.Tax)')
            ));
        }

        /**
         * Base grandtotal
         */
        if ($this->getQuote()->isCurrencyDifferent()) {
            $this->_totals['base_grandtotal'] = new Varien_Object(array(
                'code' => 'base_grandtotal',
                'area' => 'footer',
                'value' => $source->formatBasePrice($totals->getBaseGrandTotal()),
                'label' => Mage::helper('sales')->__('Grand Total to be Charged'),
                'is_formated' => true,
            ));
        }

    }

    protected function _addSubtotalOriginal(){
        // Subtotal (Original)
        $this->_totals['subtotal_original'] = new Varien_Object(array(
            'code' => 'subtotal_original',
            'label' => Mage::helper('qquoteadv')->__('Subtotal Original (%s)', $this->getTaxSetting(true)),
            'value' => 1 * $this->getQuote()->getSubtotalOriginal(),
        ));
    }

    protected function _addQuoteProfit(){
        $this->_totals['profit'] =  new Varien_Object(array(
            'label' => Mage::helper('qquoteadv')->__('Quote profit (%s)', $this->getTaxSetting(true)).'*',
            'value' => $this->getQuote()->getQuoteProfit(),
            'order' => 110,
            'strong'=> 0,
            'foot'  => 1,
            'name'  => 'profit',
            'code'  => 'profit'
        ));
    }


    /**
     * Add new total to totals array after specific total or before last total by default
     *
     * @param   Varien_Object $total
     * @param   null|string|last|first $after
     * @return  Mage_Sales_Block_Order_Totals
     */
    public function addTotal(Varien_Object $total, $after = null)
    {
        if ($after !== null && $after != 'last' && $after != 'first') {
            $totals = array();
            $added = false;
            foreach ($this->_totals as $code => $item) {
                $totals[$code] = $item;
                if ($code == $after) {
                    $added = true;
                    $totals[$total->getCode()] = $total;
                }
            }
            if (!$added) {
                $last = array_pop($totals);
                $totals[$total->getCode()] = $total;
                $totals[$last->getCode()] = $last;
            }
            $this->_totals = $totals;
        } elseif ($after == 'last') {
            $this->_totals[$total->getCode()] = $total;
        } elseif ($after == 'first') {
            $totals = array($total->getCode() => $total);
            $this->_totals = array_merge($totals, $this->_totals);
        } else {
            $last = array_pop($this->_totals);
            $this->_totals[$total->getCode()] = $total;
            $this->_totals[$last->getCode()] = $last;
        }
        return $this;
    }

    /**
     * Add new total to totals array before specific total or after first total by default
     *
     * @param   Varien_Object $total
     * @param null $before
     * @return Mage_Sales_Block_Order_Totals
     * @internal param null|string $after
     */
    public function addTotalBefore(Varien_Object $total, $before = null)
    {
        if ($before !== null) {
            if (!is_array($before)) {
                $before = array($before);
            }
            foreach ($before as $beforeTotals) {
                if (isset($this->_totals[$beforeTotals])) {
                    $totals = array();
                    foreach ($this->_totals as $code => $item) {
                        if ($code == $beforeTotals) {
                            $totals[$total->getCode()] = $total;
                        }
                        $totals[$code] = $item;
                    }
                    $this->_totals = $totals;
                    return $this;
                }
            }
        }
        $totals = array();
        $first = array_shift($this->_totals);
        $totals[$first->getCode()] = $first;
        $totals[$total->getCode()] = $total;
        foreach ($this->_totals as $code => $item) {
            $totals[$code] = $item;
        }
        $this->_totals = $totals;
        return $this;
    }

    /**
     * Get Total object by code
     *
     * @
     * @param $code
     * @return Varien_Object
     */
    public function getTotal($code)
    {
        if (isset($this->_totals[$code])) {
            return $this->_totals[$code];
        }
        return false;
    }

    /**
     * Delete total by specific
     *
     * @param   string $code
     * @return  Mage_Sales_Block_Order_Totals
     */
    public function removeTotal($code)
    {
        unset($this->_totals[$code]);
        return $this;
    }

    /**
     * Apply sort orders to totals array.
     * Array should have next structure
     * array(
     *  $totalCode => $totalSortOrder
     * )
     *
     *
     * @param   array $order
     * @return  Mage_Sales_Block_Order_Totals
     */
    public function applySortOrder($order)
    {
        return $this;
    }

    /**
     * get totals array for visualization
     *
     * @param null $area
     * @return array
     */
    public function getTotals($area = null)
    {
        $prefix = 'total-';
        $totals = array();
        if ($area === null) {
            $totals = $this->_totals;
        } else {
            $area = (string)$area;
            foreach ($this->_totals as $total) {
                $totalArea = (string)$total->getArea();
                if ($totalArea == $area) {
                    $totals[$prefix.$total->getCode()] = $total;
                }
            }
        }
        return $totals;
    }

    /**
     * Format total value based on order currency
     *
     * @param   Varien_Object $total
     * @return  string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->getQuote()->formatPrice($total->getValue());
        }
        return $total->getValue();
    }

    /**
     * Initialize configuration object
     * @return $this
     */
    protected function _initTaxHelper()
    {
        $this->_tax = Mage::getSingleton('tax/config');
        return $this;
    }

    /**
     * Initialize configuration object
     * @return $this
     */
    protected function _initStoreId()
    {
        $this->_storeId = $this->getQuote()->getStoreId();
        return $this;
    }

    /**
     * Get tax settings for store
     *
     * @param  boolean // for quote default tax
     * @return string
     */
    public function getTaxSetting($default = false)
    {
        $helper = Mage::helper('qquoteadv');
        if (Mage::getStoreConfig('tax/calculation/price_includes_tax', $this->getQuoteStore()) == 1){
            if ($default === true) {
                $tax = $helper->__('Incl. default Tax');
            } else {
                $tax = $helper->__('Incl. Tax');
            }
        } else {
            $tax = $helper->__('Excl. Tax');
        }

        return $tax;
    }
}


