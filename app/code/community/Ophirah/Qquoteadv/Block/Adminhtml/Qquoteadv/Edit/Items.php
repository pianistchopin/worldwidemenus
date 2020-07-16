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
 * Adminhtml order items grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Abstract
{
    /**
     * Retrieve required options from parent
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Invalid parent block for this block'));
        }
        $quote = $this->getParentBlock()->getQuoteModel();
        $this
            ->setQuote($quote)
            ->setQuoteId($quote->getId());
        parent::_beforeToHtml();
    }

    /**
     * Retrieve quote items collection
     *
     * @return Ophirah_Qquoteadv_Model_Mysql4_Qqadvproduct_Collection
     */
    public function getItemsCollection()
    {
        return $this->getItems();
    }

    /**
     * Get the edit products button
     * 
     * @return Mage_Core_Block_Abstract
     */
    public function getEditProductsButton()
    {
        $btnEditProducts = $this->getLayout()->createBlock('adminhtml/widget_button');
        $btnEditProducts->setLabel(Mage::helper('qquoteadv')->__('Edit products'));
        $onclick = "if(isCheckRadio()){ $('redirect2neworder').value=1;" .
            " $('loading-mask').show(); save(); }else{ return false;}";

        if (intval($this->getQuote()->getStatus()) >= 50) {
            $btnEditProducts->setClass('add disabled');
        } else {
            $btnEditProducts->setClass('add');
            $btnEditProducts->setOnclick($onclick);
        }

        return $btnEditProducts;
    }
}
