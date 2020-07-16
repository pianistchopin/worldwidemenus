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
 * Class Ophirah_Qquoteadv_Model_Totals
 */
class Ophirah_Qquoteadv_Model_Totals extends Varien_Object
{
    /**
     * @var null
     */
    protected $_quote = null;

    /**
     * @var
     */
    protected $_subTotal;

    /**
     * @var
     */
    protected $_grandTotal;

    /**
     * @var
     */
    protected $_shipping;

    /**
     * @var
     */
    protected $_taxAmount;

    /**
     * @var
     */
    protected $_baseSubTotal;

    /**
     * @var
     */
    protected $_baseGrandTotal;

    /**
     * @var
     */
    protected $_baseShipping;

    /**
     * @var
     */
    protected $_baseTaxAmount;

    /**
     * If the quote is not available on this object, get it from the registry
     *
     * @return null
     */
    public function getQuote()
    {
        if ($this->_quote == null) {
            $this->_quote = Mage::registry('current_quote');
        }

        return $this->_quote;
    }

    /**
     * Setter for quote
     *
     * @param $_quote
     */
    public function setQuote($_quote)
    {
        $this->_quote = $_quote;
    }

    /**
     * Getter for sub total
     *
     * @return mixed
     */
    public function getSubTotal()
    {
        return $this->_subTotal;
    }

    /**
     * Getter for shipping (total)
     *
     * @return mixed
     */
    public function getShipping()
    {
        return $this->_shipping;
    }

    /**
     * Getter for grand total
     *
     * @return mixed
     */
    public function getGrandTotal()
    {
        return $this->_grandTotal;
    }

    /**
     * Getter for grand total excluding tax
     *
     * @return mixed
     */
    public function getGrandTotalExclTax()
    {
        return $this->_grandTotalExlTax;
    }

    /**
     * Getter for base sub total
     *
     * @return mixed
     */
    public function getBaseSubTotal()
    {
        return $this->_baseSubTotal;
    }

    /**
     * Getter for base shipping (total)
     *
     * @return mixed
     */
    public function getBaseShipping()
    {
        return $this->_baseShipping;
    }

    /**
     * Getter for base grand total
     *
     * @return mixed
     */
    public function getBaseGrandTotal()
    {
        return $this->_baseGrandTotal;
    }

    /**
     * Getter for base grand total excluding tax
     *
     * @return mixed
     */
    public function getBaseGrandTotalExclTax()
    {
        return $this->_baseGrandTotalExclTax;
    }

    /**
     * Getter for tax amount
     *
     * @return mixed
     */
    public function getTaxAmount()
    {
        return $this->_taxAmount;
    }

    /**
     * Getter for base tax amount
     *
     * @return mixed
     */
    public function getBaseTaxAmount()
    {
        return $this->_baseTaxAmount;
    }

    /**
     * Setter for sub total
     *
     * @param $total
     * @return $this
     */
    public function setSubTotal($total)
    {
        $this->_subTotal = $total;
        return $this;
    }

    /**
     * Setter for shipping (total)
     *
     * @param $shipping
     * @return $this
     */
    public function setShipping($shipping)
    {
        $this->_shipping = $shipping;
        return $this;
    }

    /**
     * Setter for grand total
     *
     * @param $total
     * @return $this
     */
    public function setGrandTotal($total)
    {
        $this->_grandTotal = $total;
        return $this;
    }

    /**
     * Setter for grand total excluding tax
     *
     * @param $total
     * @return $this
     */
    public function setGrandTotalExclTax($total)
    {
        $this->_grandTotalExlTax = $total;
        return $this;
    }

    /**
     * Setter for base sub total
     *
     * @param $total
     * @return $this
     */
    public function setBaseSubTotal($total)
    {
        $this->_baseSubTotal = $total;
        return $this;
    }

    /**
     * Setter for base shipping (total)
     *
     * @param $shipping
     * @return $this
     */
    public function setBaseShipping($shipping)
    {
        $this->_baseShipping = $shipping;
        return $this;
    }

    /**
     * Setter for base grand total
     *
     * @param $total
     * @return $this
     */
    public function setBaseGrandTotal($total)
    {
        $this->_baseGrandTotal = $total;
        return $this;
    }

    /**
     * Setter for base grand total excluding tax
     *
     * @param $total
     * @return $this
     */
    public function setBaseGrandTotalExclTax($total)
    {
        $this->_baseGrandTotalExclTax = $total;
        return $this;
    }

    /**
     * Setter for tax amount
     *
     * @param $tax
     * @return $this
     */
    public function setTaxAmount($tax)
    {
        $this->_taxAmount = $tax;
        return $this;
    }

    /**
     * Setter for base tax amount
     *
     * @param $tax
     * @return $this
     */
    public function setBaseTaxAmount($tax)
    {
        $this->_baseTaxAmount = $tax;
        return $this;
    }

    /**
     * Init function
     * Checks if the quote is set, if not a exception is thrown
     *
     * @throws Exception
     */
    public function _initTotals()
    {
        if ($this->getQuote() == null) throw new Exception('Quote not set in ' . get_class($this));
    }

    /**
     * Get Totals of a quote
     * @return float/int if a total
     *
     * or
     *
     * @return false if tier pricing is used
     */
    public function _calculateSubtotal()
    {
        $total = 0;

        $requestedProducts = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($this->getQuote());
        $requestedProducts->getSelect()->order(array('product_id ASC', 'request_qty ASC'));

        foreach ($requestedProducts as $line) {
            $productQty = $line->getRequestQty() * 1;
            $priceProposal = $line->getOwnerBasePrice();
            $lineTotal = $productQty * $priceProposal;
            $total += $lineTotal;
        }

        return $total;
    }

    /**
     * Get Shipping total of a quote
     * @return float/int if a total
     *
     * or
     *
     * @return false if varuable pricing is used
     */
    public function _calculateShippingtotal()
    {
        $sPrice = $this->getQuote()->getShippingPrice();
        $shippingType = $this->getQuote()->getShippingType();

        if ($shippingType == 'I') {
            $qty = 0;
            $requestedProducts = Mage::getModel('qquoteadv/requestitem')
                ->getCollection()
                ->addFieldToFilter('quote_id', $this->getQuoteId());

            $requestedProducts->getSelect()->order(array('product_id ASC', 'request_qty ASC'));
            foreach ($requestedProducts as $line) $qty += $line->getRequestQty() * 1;

            $sTotal = $sPrice * $qty;
        } elseif ($shippingType == 'O') {
            $sTotal = $sPrice;
        } else {
            $sTotal = false;
        }

        return $sTotal;

    }

    /**
     * Get Totals of a quote
     * @return float
     */
    public function _calculateGrandtotalExclTax()
    {
        $subtotal = $this->getSubTotal();
        $shipping = $this->getShipping();
        return $subtotal + $shipping;
    }
}
