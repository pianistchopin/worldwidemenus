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
 * Class Ophirah_Qquoteadv_Block_Qquoteadv_View
 */
class Ophirah_Qquoteadv_Block_Qquoteadv_View extends Ophirah_Qquoteadv_Block_Qquote_Abstract
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('qquoteadv/view.phtml');

        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('qquoteadv')->__('My Quotes'));
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
     * Returns the address of the customer based on a given type, if available.
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
        if($quote){
            return $quote;
        }

        return null;
    }

    /**
     * Function that returns if viewing is allowed based on the status
     *
     * @param $status
     * @return bool
     */
    public function isStatusToDisabled($status)
    {
        if ($status == Ophirah_Qquoteadv_Model_Status::STATUS_CANCELED
            || $status == Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN
            || $status == Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST
            || $status == Ophirah_Qquoteadv_Model_Status::STATUS_DENIED
            || $status == Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST_EXPIRED
            || $status == Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_EXPIRED
            || $status == Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED
            || $status == Ophirah_Qquoteadv_Model_Status::STATUS_PRINT_ONLY

        ) {
            return true;
        }

        return false;
    }

    /**
     * Checking status to hide not needed functionalities
     *
     * @param  int $quoteStatus
     * @return bool
     */
    public function isHideButtons($quoteStatus)
    {
        return $this->isStatusToDisabled($quoteStatus);
    }

    /**
     * Returns the back url
     *
     * @return mixed
     */
    public function getBackUrl()
    {
        return Mage::getUrl('*/view/history');
    }

    /**
     * Returns the print url
     *
     * @param $quoteadv
     * @return mixed
     */
    public function getPrintUrl($quoteadv)
    {
        return $this->getUrl('qquoteadv/view/pdfqquoteadv', array('id' => $quoteadv->getId()));
    }

    /**
     * Checks if a quote is allowed to be edited.
     * @param $status       // Quote status
     * @return bool         // Allowed to edit
     */
    public function allowToEdit($status){
        $allow = false;
        switch ($status) {
            case Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL:
            case Ophirah_Qquoteadv_Model_Status::STATUS_AUTO_PROPOSAL:
            case Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_ACTION_OWNER:
            case Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_ACTION_CUSTOMER:
                $allow = true;
                break;
            case Ophirah_Qquoteadv_Model_Status::STATUS_CONFIRMED:
            case Ophirah_Qquoteadv_Model_Status::STATUS_CONFIRMED_ALTERNATE:
                if((bool)Mage::getStoreConfig('qquoteadv_advanced_settings/checkout/confirmed_alternate')){
                    $allow = true;
                }
                break;
            default:
                // Do nothing: allow default false.
                break;
        }
        return $allow;
    }

    /**
     * Function that returns if the original price should be visible based on the quote status
     *
     * @param $status
     * @return bool
     */
    public function showOriginalPrice($status)
    {
        //always show when status is proposal or better
        if ($status >= Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL) {
            return true;
        }

        //use setting when status is below proposal
        $showOriginalPrice = Mage::getStoreConfig(
            'qquoteadv_advanced_settings/beta_features/originalprice_requeststatus'
        );
        return (bool)$showOriginalPrice;
    }
}
