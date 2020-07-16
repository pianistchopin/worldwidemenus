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
 * Tax totals modification block. Can be used just as subblock of Mage_Sales_Block_Order_Totals
 */
class Ophirah_Qquoteadv_Block_Tax extends Mage_Core_Block_Template
{
    /**
     * Tax configuration model
     *
     * @var Mage_Tax_Model_Config
     */
    protected $_config;

    /**
     * @var
     */
    protected $_quote;

    /**
     * @var
     */
    protected $_source;

    /**
     * Initialize configuration object
     */
    protected function _construct()
    {
        $this->_config = Mage::getSingleton('tax/config');
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return $this->_config->displaySalesFullSummary($this->getQuote()->getStore());
    }

    /**
     * Get data (totals) source model
     *
     * @return Varien_Object
     */
    public function getSource()
    {
        return $this->_source;
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
     * Initialize all order totals relates with tax
     *
     * @return Mage_Tax_Block_Sales_Order_Tax
     */
    public function initTotals()
    {
        /** @var $parent Mage_Adminhtml_Block_Sales_Order_Invoice_Totals */
        $parent = $this->getParentBlock();
        $this->_quote = $parent->getQuote();
        if(!$this->_quote) {
            Mage::log('Message: No Quote available in Ophirah_Qquoteadv_Block_Tax::initTotals', null, 'c2q.log', true);
            return $this;
        }
        $this->_source = $parent->getSource();

        $store = $this->getStore();
        $allowTax = ($this->_source->getTaxAmount() > 0) || ($this->_config->displaySalesZeroTax($store));
        $grandTotal = (float)$this->_source->getGrandTotal();
        if (!$grandTotal || ($allowTax && !$this->_config->displaySalesTaxWithGrandTotal($store))) {
            $this->_addTax();
        }

        $this->_initSubtotal();
        $this->_initShipping();
        $this->_initDiscount();
        $this->_initGrandTotal();
        return $this;
    }

    /**
     * Add tax total string
     *
     * @param string $after
     * @return Mage_Tax_Block_Sales_Order_Tax
     */
    protected function _addTax($after = 'discount')
    {
        $taxTotal = new Varien_Object(array(
            'code' => 'tax',
            'block_name' => $this->getNameInLayout()
        ));
        $this->getParentBlock()->addTotal($taxTotal, $after);
        return $this;
    }

    /**
     * Get order store object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->_quote->getStore();
    }

    /**
     * Function to initialise the subtotal
     *
     * @return $this
     */
    protected function _initSubtotal()
    {
        $store = $this->getStore();
        $parent = $this->getParentBlock();
        $subtotal = $parent->getTotal('subtotal');
        if (!$subtotal) {
            return $this;
        }
        if ($this->_config->displaySalesSubtotalBoth($store)) {
            $subtotal = (float)$this->_source->getSubtotal();
            $baseSubtotal = (float)$this->_source->getBaseSubtotal();
            $subtotalIncl = (float)$this->_source->getSubtotalInclTax();
            $baseSubtotalIncl = (float)$this->_source->getBaseSubtotalInclTax();

            if (!$subtotalIncl) {
                $subtotalIncl = $subtotal + $this->_source->getTaxAmount()
                    - $this->_source->getShippingTaxAmount();
            }
            if (!$baseSubtotalIncl) {
                $baseSubtotalIncl = $baseSubtotal + $this->_source->getBaseTaxAmount()
                    - $this->_source->getBaseShippingTaxAmount();
            }
            $subtotalIncl = max(0, $subtotalIncl);
            $baseSubtotalIncl = max(0, $baseSubtotalIncl);
            $totalExcl = new Varien_Object(array(
                'code' => 'subtotal_excl',
                'value' => $subtotal,
                'base_value' => $baseSubtotal,
                'label' => Mage::helper('tax')->__('Subtotal (Excl.Tax)')
            ));
            $totalIncl = new Varien_Object(array(
                'code' => 'subtotal_incl',
                'value' => $subtotalIncl,
                'base_value' => $baseSubtotalIncl,
                'label' => Mage::helper('tax')->__('Subtotal (Incl.Tax)')
            ));
            $parent->addTotal($totalExcl, 'subtotal');
            $parent->addTotal($totalIncl, 'subtotal_excl');
            $parent->removeTotal('subtotal');
        } elseif ($this->_config->displaySalesSubtotalInclTax($store)) {
            $subtotalIncl = (float)$this->_source->getSubtotalInclTax();
            $baseSubtotalIncl = (float)$this->_source->getBaseSubtotalInclTax();

            if (!$subtotalIncl) {
                $subtotalIncl = $this->_source->getSubtotal()
                    + $this->_source->getTaxAmount()
                    - $this->_source->getShippingTaxAmount();
            }
            if (!$baseSubtotalIncl) {
                $baseSubtotalIncl = $this->_source->getBaseSubtotal()
                    + $this->_source->getBaseTaxAmount()
                    - $this->_source->getBaseShippingTaxAmount();
            }

            $total = $parent->getTotal('subtotal');
            if ($total) {
                $total->setValue(max(0, $subtotalIncl));
                $total->setBaseValue(max(0, $baseSubtotalIncl));
            }
        }
        return $this;
    }

    /**
     * Function to initialise the shipping cost
     *
     * @return $this
     */
    protected function _initShipping()
    {
        $store = $this->getStore();
        $parent = $this->getParentBlock();
        $shipping = $parent->getTotal('shipping');
        if (!$shipping) {
            return $this;
        }

        if ($this->_config->displaySalesShippingBoth($store)) {
            $shipping = (float)$this->_source->getShippingAmount();
            $baseShipping = (float)$this->_source->getBaseShippingAmount();
            $shippingIncl = (float)$this->_source->getShippingInclTax();
            if (!$shippingIncl) {
                $shippingIncl = $shipping + (float)$this->_source->getShippingTaxAmount();
            }
            $baseShippingIncl = (float)$this->_source->getBaseShippingInclTax();
            if (!$baseShippingIncl) {
                $baseShippingIncl = $baseShipping + (float)$this->_source->getBaseShippingTaxAmount();
            }

            $totalExcl = new Varien_Object(array(
                'code' => 'shipping',
                'value' => $shipping,
                'base_value' => $baseShipping,
                'label' => Mage::helper('tax')->__('Shipping & Handling (Excl.Tax)')
            ));
            $totalIncl = new Varien_Object(array(
                'code' => 'shipping_incl',
                'value' => $shippingIncl,
                'base_value' => $baseShippingIncl,
                'label' => Mage::helper('tax')->__('Shipping & Handling (Incl.Tax)')
            ));
            $parent->addTotal($totalExcl, 'shipping');
            $parent->addTotal($totalIncl, 'shipping');
        } elseif ($this->_config->displaySalesShippingInclTax($store)) {
            $shippingIncl = $this->_source->getShippingInclTax();
            if (!$shippingIncl) {
                $shippingIncl = $this->_source->getShippingAmount()
                    + $this->_source->getShippingTaxAmount();
            }
            $baseShippingIncl = $this->_source->getBaseShippingInclTax();
            if (!$baseShippingIncl) {
                $baseShippingIncl = $this->_source->getBaseShippingAmount()
                    + $this->_source->getBaseShippingTaxAmount();
            }
            $total = $parent->getTotal('shipping');
            if ($total) {
                $total->setValue($shippingIncl);
                $total->setBaseValue($baseShippingIncl);
            }
        }
        return $this;
    }

    /**
     * Function to initialise the grand total
     *
     * @return $this
     */
    protected function _initGrandTotal()
    {
        $store = $this->getStore();
        $parent = $this->getParentBlock();
        $grandototal = $parent->getTotal('grand_total');
        if (!$grandototal || !(float)$this->_source->getGrandTotal()) {
            return $this;
        }

        if ($this->_config->displaySalesTaxWithGrandTotal($store)) {
            $grandtotal = $this->_source->getGrandTotal();
            $baseGrandtotal = $this->_source->getBaseGrandTotal();
            $grandtotalExcl = $grandtotal - $this->_source->getTaxAmount();
            $baseGrandtotalExcl = $baseGrandtotal - $this->_source->getBaseTaxAmount();
            $grandtotalExcl = max($grandtotalExcl, 0);
            $baseGrandtotalExcl = max($baseGrandtotalExcl, 0);
            $totalExcl = new Varien_Object(array(
                'code' => 'grand_total',
                'strong' => true,
                'value' => $grandtotalExcl,
                'base_value' => $baseGrandtotalExcl,
                'label' => Mage::helper('tax')->__('Grand Total (Excl.Tax)')
            ));
            $totalIncl = new Varien_Object(array(
                'code' => 'grand_total_incl',
                'strong' => true,
                'value' => $grandtotal,
                'base_value' => $baseGrandtotal,
                'label' => Mage::helper('tax')->__('Grand Total (Incl.Tax)')
            ));
            $parent->addTotal($totalExcl, 'grand_total');
            $this->_addTax('grand_total');
            $parent->addTotal($totalIncl, 'tax');
        }
        return $this;
    }

    /**
     * Getter frot the quote
     *
     * @return mixed
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Get label from the parent block
     *
     * @return mixed
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get value from the parent block
     *
     * @return mixed
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Overwrite that replaces getOrder for getQuote
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->getQuote();
    }
}
