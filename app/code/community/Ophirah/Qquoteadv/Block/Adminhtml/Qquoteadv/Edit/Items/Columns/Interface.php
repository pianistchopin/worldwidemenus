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
 * Interface Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Interface
 */
interface Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Interface
{
    /**
     * Return Column label
     *
     * @return string
     */
    public function getColumnLabel();

    /**
     * Return Column title
     *
     * @return string
     */
    public function getColumnTitle();

    /**
     * Column is hidden
     *
     * @return boolean
     */
    public function isHidden();

    /**
     * Get the with of the column
     * @return string
     */
    public function getWidth();

    /**
     * Get the css classes for the header
     * @return string
     */
    public function getCssHeaderClasses();

    /**
     * Get the css classes for the content
     * @return string
     */
    public function getCssContentClasses();

    /**
     * Get the row id of the column.
     * Normally label+productId.
     * @return mixed
     */
    public function getRowId();

    /**
     * The Magento Product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct();

    /**
     * The Cart2Quote Product
     * @return Ophirah_Qquoteadv_Model_Qqadvproduct
     */
    public function getItem();

    /**
     * The Cart2Quote Quote
     * @return Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function getQuote();

    /**
     * The Magento Product
     * @param Mage_Catalog_Model_Product $product
     * @return void
     */
    public function setProduct(Mage_Catalog_Model_Product $product);

    /**
     * Sets a product
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $item
     * @return void
     */
    public function setItem(Ophirah_Qquoteadv_Model_Qqadvproduct $item);

    /**
     * Sets a quote
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @return void
     */
    public function setQuote(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote);

    /**
     * Returns the subtotal information in the table footer
     * @return String
     */
    public function getColumnTotal();

    /**
     * Returns the css class for the table footer column
     * @return String
     */
    public function getColumnTotalCssClass();

    /**
     * Get the class last if this column is the last column
     * @return string
     */
    public function getLastCss();
}
