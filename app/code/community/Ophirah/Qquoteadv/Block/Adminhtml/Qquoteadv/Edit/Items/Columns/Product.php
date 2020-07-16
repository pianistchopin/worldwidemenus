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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Product
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Product extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Abstract
{

    /**
     * Return Column label
     *
     * @return string
     */
    public function getColumnLabel()
    {
        return 'product';
    }

    /**
     * Return Column title
     *
     * @return string
     */
    public function getColumnTitle()
    {
        return Mage::helper('qquoteadv')->__('Product Name');
    }

    /**
     * Column is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return !Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_PRODUCT_INFORMATION_PRODUCT);
    }

    /**
     * Get the with of the column
     * @return string
     */
    public function getWidth()
    {
        return '25%';
    }

    /**
     * Get the css classes
     * @return string
     */
    public function getCssHeaderClasses()
    {
        $classes = '';
        return $classes . parent::getCssHeaderClasses();
    }

    /**
     * Get the css classes of the content
     * @return string
     */
    public function getCssContentClasses()
    {
        $classes = 'a-left';
        return $classes . parent::getCssContentClasses();
    }

    /**
     * Retrieves the product url
     * @return string
     */
    public function getProductUrl(){
        return Mage::helper("adminhtml")
            ->getUrl(
                "adminhtml/catalog_product/edit/",
                array("id" => $this->getProduct()->getId())
            );
    }

    /**
     * Returns the out of stock error for a give product
     *
     * @return string
     */
    public function getOutOfStockError(){
        $html = '';
        if (!$this->getProduct()->getStockItem()->getIsInStock()){
            $html .= '<div class="error">';
            $html .=    '<div style="font-size:95%;">';
            $html .=        Mage::helper('cataloginventory')->__('This product is currently out of stock.');
            $html .=    '</div>';
            $html .= '</div>';
        }
        return $html;
    }

    /**
     * Returns the product disabled error for a give product
     *
     * @return string
     */
    public function getProductStatsError(){
        $html = '';
        if ($this->getProduct()->getStatus() == 2){
            $html .= '<div class="error">';
            $html .=     '<div style="font-size:95%;">';
            $html .=       Mage::helper('adminhtml')->__('This product is currently disabled.');
            $html .=       '<br/>';
            $html .=       '<em>'. Mage::helper('adminhtml')->__('To sent a proposal enable this product.').'</em>';
            $html .=    '</div>';
            $html .=  '</div>';
        }
        return $html;
    }

    /**
     * Get the HTML of a specific product type.
     *
     * @return string
     */
    public function getItemHtml(){
        return $this->getRenderedBlock()->getItemHtml($this->getItem());
    }

    /**
     * Returns the subtotal information in the table footer
     * @return String
     */
    public function getColumnTotal()
    {
        return $this->helper('sales')->__('Total %d product(s)', $this->getProductCount());
    }

    /**
     * Returns the css class for the table footer column
     * @return String
     */
    public function getColumnTotalCssClass()
    {
        $classes = 'a-left';
        return $classes . parent::getColumnTotalCssClass();
    }
}