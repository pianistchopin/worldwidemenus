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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Sortable
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Sortable extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Abstract
{
    /**
     * Return Column label
     *
     * @return string
     */
    public function getColumnLabel()
    {
        return 'sort';
    }

    /**
     * Return Column title
     *
     * @return string
     */
    public function getColumnTitle()
    {
        return Mage::helper('qquoteadv')->__('#');
    }

    /**
     * Column is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return !Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_PRODUCT_INFORMATION_SKU);
    }

    /**
     * Get the with of the column
     * @return string
     */
    public function getWidth()
    {
        return '2%';
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
        $classes = 'a-center';
        return $classes . parent::getCssContentClasses();
    }

    /**
     * Returns the subtotal information in the table footer
     * @return String
     */
    public function getColumnTotal()
    {
        return '';
    }

    /**
     * Returns the css class for the table footer column
     * @return String
     */
    public function getColumnTotalCssClass()
    {
        $classes = '';
        return $classes . parent::getColumnTotalCssClass();
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return "<i class='drag drag-icon'></i>";
    }
}