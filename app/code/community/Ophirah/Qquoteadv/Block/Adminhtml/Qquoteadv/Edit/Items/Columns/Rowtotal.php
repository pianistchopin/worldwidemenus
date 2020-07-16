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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Rowtotal
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Rowtotal extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Abstract
{
    /**
     * Return Column label
     *
     * @return string
     */
    public function getColumnLabel()
    {
        return 'rowtotal';
    }

    /**
     * Return Column title
     *
     * @return string
     */
    public function getColumnTitle()
    {
        return Mage::helper('qquoteadv')->__('Row Total');
    }

    /**
     * Column is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return !Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_PRODUCT_INFORMATION_ROW_TOTAL);
    }

    /**
     * Get the with of the column
     * @return string
     */
    public function getWidth()
    {
        return '4%';
    }

    /**
     * Get the css classes
     * @return string
     */
    public function getCssHeaderClasses()
    {
        $classes = 'a-center';
        return $classes . parent::getCssHeaderClasses();
    }

    /**
     * Get the css classes of the content
     * @return string
     */
    public function getCssContentClasses()
    {
        $classes = 'a-right';
        return $classes . parent::getCssContentClasses();
    }

    /**
     * Returns the subtotal information in the table footer
     * @return String
     */
    public function getColumnTotal()
    {
        if (Mage::getStoreConfig('tax/calculation/price_includes_tax', $this->getQuote()->getStoreId()) == 1) {
            $subtotalInclTax = $this->getQuote()->getSubtotal() + $this->getQuote()->getTaxAmount();
            return '<strong>' . $this->getStore()->formatPrice($subtotalInclTax) . '</strong>';
        }
        return '<strong>' . $this->getStore()->formatPrice($this->getQuote()->getSubtotal()) . '</strong>';
    }

    /**
     * Returns the css class for the table footer column
     * @return String
     */
    public function getColumnTotalCssClass()
    {
        $classes = 'a-right';
        return $classes . parent::getColumnTotalCssClass();
    }
}