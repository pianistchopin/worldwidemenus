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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Costprice
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Costprice extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Abstract
{
    /**
     * Return Column label
     *
     * @return string
     */
    public function getColumnLabel()
    {
        return 'costprice';
    }

    /**
     * Return Column title
     *
     * @return string
     */
    public function getColumnTitle()
    {
        return Mage::helper('qquoteadv')->__('Cost Price');
    }

    /**
     * Column is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return !Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_PRODUCT_INFORMATION_COST_PRICE);
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
     * Get the cost html for a given collection of quote items
     *
     * @return string
     */
    public function getCostHtml(){
        $cost = null;
        $html = '';
        $qqadvproduct = Mage::getModel('qquoteadv/qqadvproduct');
        $collection = $this->getTierQty();

        if (count($collection) > 0){
            foreach ($collection as $requested_item){
                if (isset($requested_item) and is_object($requested_item)) {
                    $requestId = $requested_item->getRequestId();
                    $cost = $qqadvproduct->getQuoteItemCost($this->getProduct(), $requested_item->getQuoteadvProductId(), $requestId);

                    $html .= '<div id="price-cost-'.$requested_item->getId().'" style="height: 25px;">';

                    if ($cost) {
                        $html .= '<div class="cost-price-'.$requestId.'">';
                        $html .= $this->getStore()->formatPrice($cost * $this->getRate());
                        $html .= $this->getCostPriceBlock()->setRequestId($requestId)->setCostPrice($cost * $this->getRate())->toHtml();
                        $html .= '</div>';
                    } else {
                        $html .= '<span class="cost-price-na">';
                        $html .= Mage::helper('sales')->__('N/A');
                        $html .= '</span>';
                        $html .= $this->getCostPriceBlock()->setRequestId($requestId)->setCostPrice($cost * $this->getRate())->toHtml();
                        $this->getQuoteTotal()->_costflag = true;
                    }

                    $html .= '</div>';

                    //check if this cost price is selected
                    if($requested_item->getRequestQty() == $this->getItem()->getData('qty')) {
                        // Adding to quotetotals
                        $_quoteItems[$this->getItem()->getId()]['productId'] = (int)$this->getProduct()->getId();

                        if ($cost) {
                            $costPrice = $cost * $this->getRate();
                        } else {
                            $costPrice = 0;
                        }
                        $this->setQuoteItemValue($this->getItem()->getId(), 'totalCost', $costPrice);
                    }
                }
            }
        }
        return $html;
    }

    /**
     * Get the cost price block
     * Todo: refactor the block into this blok
     *
     * @return Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Quotedetails_Costprice
     */
    public function getCostPriceBlock(){
        return $this->getRenderedBlock()->getParentBlock()->getChild('cart2quote.quotedetails.costprice');
    }

    /**
     * Returns the subtotal information in the table footer
     * @return String
     */
    public function getColumnTotal()
    {
        return '<strong>'.$this->getStore()->formatPrice($this->getQuoteTotal()->getTotalCost()).'</strong>';
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