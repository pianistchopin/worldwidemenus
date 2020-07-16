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
 * Class Ophirah_Qquoteadv_Model_Requestitem
 */
class Ophirah_Qquoteadv_Model_Requestitem extends Mage_Sales_Model_Quote_Address_Item
{
    /**
     * @var null
     */
    protected $_quote = null;

    /**
     * @var null
     */
    protected $_weight = null;

    /**
     * @var null
     */
    protected $_children = null;

    /**
     * @var null
     */
    protected $_hasChildren = null;

    /**
     * @var null
     */
    protected $_taxableAmount = null;

    /**
     * Construct function
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/requestitem');
    }

    /**
     * Add item to request for the particular quote
     * @param array $params array of field(s) to be inserted
     * @param null $productData
     * @return mixed
     * @throws Exception
     */
    public function addItem($params, $productData = null)
    {
        $this->setData($params);
        $id = $this->_getExistingQtyId($params, $productData);
        if ($id) {
            $this->setId($id);
        }

        $this->save();
        return $this;
    }

    /**
     * Add items to request for the particular quote
     * @param array $params array of field(s) to be inserted
     * @return mixed
     */
    public function addItems($params)
    {

        foreach ($params as $key => $values)
//            if(!$this->_isDublicatedData($values)){
            $this->addItem($values);
//           }
        return $this;
    }

    /**
     * Checking item / qty for blocking dublication request
     *
     * @param array $params array of field(s) should to be inserted
     * @param array|null $productData
     * @return mixed
     */
    protected function _getExistingQtyId($params, $productData = null)
    {
        $quoteProduct = Mage::getSingleton('qquoteadv/qqadvproduct')->load($params['quoteadv_product_id']);
        $identicalAttribute = false;
        if (isset($productData) && isset($quoteProduct)) {
            if (isset($productData['attribute'])) {
                $quoteProductAttribute = $quoteProduct->getAttribute();
                if ($quoteProductAttribute == $productData['attribute']) {
                    $identicalAttribute = true;
                }
            }
        }

        $qtyRequest = $params['request_qty'];
        $collection = Mage::getModel('qquoteadv/requestitem')
            ->getCollection()
            ->addFieldToFilter('request_qty', $qtyRequest)
            ->addFieldToFilter('quoteadv_product_id', $params['quoteadv_product_id']);

        if ($collection->getSize() && ($identicalAttribute || $productData == null)) {
            return $collection->getFirstItem()->getId();
        } else {
            return false;
        }
    }

    /**
     * Get the magento product (with buyrequest information) that is currently set
     *
     * @return mixed
     */
    public function getProduct()
    {
        $product = Mage::getSingleton('catalog/product')->load($this->getProductId());

        $qqadvproduct = Mage::getModel('qquoteadv/qqadvproduct')->load($this->getQuoteadvProductId());

        $product->setStoreId($qqadvproduct->getStoreId() ? $qqadvproduct->getStoreId() : 1);
        //$productOptions = unserialize($qqadvproduct->getAttribute());
        $buyRequest = new Varien_Object();
        $product->getTypeInstance($product)->processConfiguration($buyRequest, $product);
        return $product;
    }

    /**
     * Setter for quote
     *
     * @param $quote
     */
    public function setQuote($quote)
    {
        $this->_quote = $quote;
    }

    /**
     * Getter for quote
     * Loads the quote if it isn't already set
     *
     * @return null
     */
    public function getQuote()
    {

        if ($this->_quote == null) {
            $quote = Mage::getSingleton('qquoteadv/qqadvcustomer')->load((int)$this->getQuoteId());
            $this->_quote = $quote;
        }
        return $this->_quote;
    }

    /**
     * Delete tier quantity from quote
     *
     * @param $requestid
     * @return $this
     */
    public function deleteTierQty($requestid)
    {
        $this->setRequestId($requestid)->delete();
        return $this;
    }

    /**
     * Getter for the store that is associated with the current quote
     *
     * @return mixed
     */
    public function getStore()
    {
        return $this->getQuote()->getStore();
    }

    /**
     * Getter for the address that is associated with the current quote
     *
     * @return mixed
     */
    public function getAddress()
    {
        return $this->getQuote()->getAddress();
    }

    /**
     * Calculate item row total price
     *
     * @return Ophirah_Qquoteadv_Model_Requestitem
     */
    public function calcRowTotal()
    {
        $qty = $this->getRequestQty();
        // Round unit price before multiplying to prevent losing 1 cent on subtotal
        $total = $this->getOwnerCurPrice() * $qty;
        $baseTotal = $this->getOwnerBasePrice() * $qty;

        $this->setRowTotal($this->getStore()->roundPrice($total));
        $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
        return $this;
    }

    /**
     * Get the quote request item id of the current item
     *
     * @return int
     */
    public function getQuoteItemId()
    {
        return $this->getId();
    }

    /**
     * Get the original price of the current item
     *
     * @return mixed
     */
    public function getOriginalPrice()
    {
        return $this->getOwnerCurPrice();
    }

    /**
     * Get the base original price of the current item
     *
     * @return mixed
     */
    public function getBaseOriginalPrice()
    {
        return $this->getOwnerBasePrice();
    }

    /**
     * Get the quantity of the current item
     *
     * @return mixed
     */
    public function getTotalQty()
    {
        return $this->getRequestQty();
    }

    /**
     * Get the quantity of the current item
     *
     * @return mixed
     */
    public function getQty()
    {
        return $this->getRequestQty();
    }

    /**
     * Get the calculation price of the current item
     *
     * @return mixed
     */
    public function getCalculationPrice()
    {
        return $this->getOwnerCurPrice();
    }

    /**
     * Get the base calculation price of the current item
     *
     * @return mixed
     */
    public function getBaseCalculationPrice()
    {
        return $this->getOwnerBasePrice();
    }

    /**
     * Get the calculation price of the current item
     *
     * @return mixed
     */
    public function getCalculationPriceOriginal()
    {
        return $this->getOwnerCurPrice();
    }

    /**
     * Get the base calculation price of the current item
     *
     * @return mixed
     */
    public function getBaseCalculationPriceOriginal()
    {
        return $this->getOwnerBasePrice();
    }

    /**
     * Get the row total (quantity * price)  of the current item
     *
     * @return float
     */
    public function getRowTotal()
    {
        $qty = $this->getRequestQty();
        $total = $this->getStore()->roundPrice($this->getOwnerCurPrice() * $qty);
        return $total;
    }

    /**
     * Get the base row total (quantity * price)  of the current item
     *
     * @return float
     */
    public function getBaseRowTotal()
    {
        $qty = $this->getRequestQty();
        $baseTotal = $this->getOwnerBasePrice() * $qty;
        return $baseTotal;
    }

    /**
     * Get the weight of the current item
     *
     * @return float|null
     */
    public function getWeight()
    {
        if ($this->_weight == null) {
            $this->_weight = $this->getProduct()->getWeight();
        }

        return $this->_weight;
    }

    /**
     * Get the simple product from a configured configurable product
     *
     * @deprecated since v6.2.3
     * @return mixed
     */
    public function getConfChildProduct()
    {
        return Mage::helper('qquoteadv/catalog_product_data')
            ->getConfChildProduct($this->getData('quoteadv_product_id'));
    }

    /**
     * Adds data by the Magento tier price.
     * @param array $tierPrice
     * @return $this
     * @throws Exception
     */
    public function mapTierPrice(array $tierPrice)
    {
        $validateFields = array(
            'price',
            'price_qty'
        );

        foreach ($validateFields as $validate) {
            if (!isset($tierPrice[$validate])) {
                throw new Exception(
                    Mage::helper('qquoteadv')->__(sprintf('Cannot map tier price, undefined field: %s', $validate))
                );
            }
        }

        $this->setRequestQty($tierPrice['price_qty'])
            ->setOwnerBasePrice($tierPrice['price'])
            ->setOriginalPrice($tierPrice['price'])
            ->setOwnerCurPrice($tierPrice['price'])
            ->setOriginalCurPrice($tierPrice['price']);
        return $this;
    }
}
