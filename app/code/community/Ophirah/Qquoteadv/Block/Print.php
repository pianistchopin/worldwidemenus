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
 * Class Ophirah_Qquoteadv_Block_Print
 */
class Ophirah_Qquoteadv_Block_Print extends Mage_Core_Block_Template
{
    /**
     * Construct the Print Quote page
     */
    public function __construct()
    {
        parent::__construct();
        $title = Mage::helper('qquoteadv')->__('Print Quote ');
        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle($title);
    }

    /**
     * Get customer session data
     * @return session data
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get product Information
     * @param integer $productId
     * @return session data
     */
    public function getProduct($productId)
    {
        return Mage::getModel('catalog/product')->load($productId);
    }

    /**
     * Get Product information from qquote_request_item table
     * @param $productId
     * @param $quoteId
     * @return object
     */
    public function getRequestedProductData($productId, $quoteId)
    {
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        //todo: check quoteadv_product_id?
        $product = Mage::getModel('qquoteadv/requestitem')->getCollection()->setQuote($quote)
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('product_id', $productId)
            //->order('request_qty', 'asc')
            //->load(true)
        ;
        $product->getSelect()->order('request_qty asc');
        return $product;
    }

    /**
     * Get delete url
     * @param object product information
     * @return string url
     */
    public function getDeleteUrl($item)
    {
        $deleteUrl = $this->getUrl('qquoteadv/index/delete',
            array('id' => $this->getProduct($item->getProductId())->getId())
        );
        return $deleteUrl;
    }


    /**
     * Get Product information from qquote_product table
     * @return quote object
     */
    public function getQuoteProducts()
    {

        if (!Mage::registry('quote') && $this->getQuoteId()) {
            $quote = Mage::getModel('qquoteadv/qqadvproduct')->load($this->getQuoteId());
            Mage::register('quote', $quote);
        }
        return Mage::registry('quote');
    }

    /**
     * Get total number of items in quote
     * @return integer total number of items
     */
    public function getTotalQty()
    {
        $totalQty = 0;
        $products = $this->getQuote();
        foreach ($products as $key => $value) {
            $totalQty += $value['qty'];
        }
        return $totalQty;
    }

    /**
     * Get customer address
     */
    public function getCustomer()
    {
        $id = $this->getCustomerSession()->getId();
        $cust = Mage::getModel('customer/customer')->load($id);

        return $cust;
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

        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
            $superAttribute = Mage::helper('qquoteadv')->getSimpleOptions($product, unserialize($attribute));
        }
        return $superAttribute;
    }

    /**
     * Returns the value of a given fieldname and type
     *
     * @param $fieldname
     * @param $type
     * @return null|string
     */
    public function getValue($fieldname, $type)
    {
        $value = $this->getRegisteredValue($type);
        if ($value) {
            if ($fieldname == "street1") {
                $street = $value->getData('street');
                if (is_array($street)) {
                    $street = explode("\n", $street);
                    return $street[0];
                } else {
                    return "";
                }
            }

            if ($fieldname == "street2") {
                $street = $value->getData('street');

                if (is_array($street)) {
                    $street = explode("\n", $street);
                    return $street[1];
                } else {
                    return "";
                }
            }

            if ($fieldname == "email") {
                return Mage::getSingleton('customer/session')->getCustomer()->getEmail();
            }

            if ($fieldname == "country") {
                $countryCode = $value->getData("country_id");
                return $countryCode;
            }
            return $value->getData($fieldname);
        }

        return null;
    }

    /**
     * Returns the primary address of the customer if available based on a given type
     *
     * @param $type
     * @return null
     */
    public function getRegisteredValue($type)
    {
        if ($type == 'billing') {
            return Mage::getSingleton('customer/session')->getCustomer()->getPrimaryBillingAddress();
        }

        if ($type == 'shipping') {
            return Mage::getSingleton('customer/session')->getCustomer()->getPrimaryShippingAddress();
        }

        return null;
    }


    /**
     * Get Quote information from qquote_customer table
     * @return object
     */
    public function getQuoteData()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        if($quote) {
            return $quote;
        }

        return null;
    }

    /**
     * Get the SKU of an product, including support for configurables and bundles
     *
     * @param $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku')) {
            return $item->getProductOptionByCode('simple_sku');
        }

        return $item->getSku();
    }
}
