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
 * Class Ophirah_Qquoteadv_Block_Qquoteadv_Email_Items
 */
class Ophirah_Qquoteadv_Block_Qquoteadv_Email_Items extends Mage_Sales_Block_Items_Abstract //Mage_Core_Block_Template
{
    /**
     * @var int
     */
    public $autoproposal = 0;

    /**
     * @var
     */
    public $quote;

    /**
     * Function that gets the quote model from the quote id that is available in the request parameters
     * Has a fallback to the current available quote object if none is available in the request parameters
     *
     * @return null
     */
    public function getQuote()
    {
        $quoteId = $this->getRequest()->getParam('id');
        if (!$quoteId) {
            $quoteId = $this->getRequest()->getParam('quote_id');
        }

        if (!$quoteId) {
            $quoteObj = $this->getData('quote');
            if ($quoteObj) {
                $quoteId = $quoteObj->getQuoteId();
            }
        }

        if ($quoteId) {
            $this->setQuoteId($quoteId);
            return $this->getQuotationQuote($quoteId);
        }
        return null;
    }

    /**
     * Get Product information from qquote_product table
     * @return quote object
     */
    public function getAllItems()
    {
        $collection = Mage::getModel('qquoteadv/qqadvproduct')->getQuoteProduct($this->getQuoteId());
        return $collection;
    }

    /**
     * Set value from template autoproposal
     *
     * @param $val
     * @return $this
     */
    public function setAutoproposal($val)
    {
        $this->autoproposal = $val;
        return $this;
    }

    /**
     * AutoProposal checker
     *
     * @return int
     */
    public function isSetAutoProposal()
    {
        return $this->autoproposal;
    }

    /**
     * Function that gets the quote based on the given quote id
     *
     * @param $quoteId
     * @return Mage_Core_Model_Abstract
     */
    public function getQuotationQuote($quoteId){
        if(!$this->quote){
            $this->quote = Mage::getSingleton('qquoteadv/qqadvcustomer')->load($quoteId);
            $this->quoteId = $quoteId;
            return $this->quote;
        }

        if($this->quoteId == $quoteId){
            return $this->quote;
        }

        return Mage::getSingleton('qquoteadv/qqadvcustomer')->load($quoteId);
    }
}
