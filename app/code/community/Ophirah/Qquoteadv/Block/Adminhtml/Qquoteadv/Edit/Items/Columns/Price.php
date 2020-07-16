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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Price
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Price extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Abstract
{
    /**
     * Return Column label
     *
     * @return string
     */
    public function getColumnLabel()
    {
        return 'price';
    }

    /**
     * Return Column title
     *
     * @return string
     */
    public function getColumnTitle()
    {
        return Mage::helper('qquoteadv')->__('Price');
    }

    /**
     * Column is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return !Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_PRODUCT_INFORMATION_PRICE);
    }

    /**
     * Get the with of the column
     * @return string
     */
    public function getWidth()
    {
        return '10%';
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
        $classes = 'a-left';
        return $classes . parent::getCssContentClasses();
    }

    /**
     * Get the Qty html for a given collection of quote items
     * Todo: Refactor and split this in smaller functions
     *
     * @return array
     */
    public function getQtyHtml(){
        $hideRadio = true;
        $prices = array();
        $margins = array();
        $customPriceLineHtml = array();
        $rowTotalLine = array();
        $collection = $this->getItem()->getRequestedProductData();
        if (count($collection) > 0):

            $i = 0;
            foreach ($collection as $requested_item):
                // ROW CUSTOM PRICE START
                $tire_qty = $requested_item->getRequestQty();
                $line = number_format($tire_qty, 2).'<input type="hidden" name="product[' . $requested_item->getId() . '][qty]" value="' . $requested_item->getRequestQty() . '">';
                $customPriceLineHtml[$tire_qty] = $line;
                // ROW CUSTOM PRICE END

                // ROW TOTAL START
                $requested_item->calcRowTotal();
                $rowTotalLine[] =
                    '<div  style="height:25px; font-weight:bold;" class="price">'.
                    $this->getStore()->formatPrice(
                            $requested_item->getRowTotal()
                        ).
                    '</div>';
                // ROW TOTAL END

                $showPrice = $requested_item->getOriginalCurPrice();
                $ownerPrice = $requested_item->getOwnerCurPrice();
                if ($ownerPrice !== null && $ownerPrice >= 0) {
                    $showPrice = $ownerPrice;
                }

                if (strlen(substr(strrchr($showPrice, "."), 1)) < 2) {
                    $showPrice = Mage::app()->getStore()->roundPrice($showPrice);
                } else {
                    $showPrice = round($showPrice, 4);
                }

                if (strlen(substr(strrchr($ownerPrice, "."), 1)) < 2) {
                    $ownerPrice = Mage::app()->getStore()->roundPrice($ownerPrice);
                } else {
                    $ownerPrice = round($ownerPrice, 4);
                }

                $priceLine = array();

                if ($hideRadio) {
                    $radioStates = array('unselected' => $this->getRenderedBlock()->isQuoteReadOnly('radio'), 'selected' => 'checked="checked"');
                    foreach ($radioStates as $state => $radioState) {
                        $priceLine[$state] = '&nbsp;<input ' . $radioState . ' class="rbt" type="radio" name="q2o[' . $this->getItem()->getCounter() . ']" value="' . $requested_item->getId() . '">&nbsp;&nbsp;';
                    }
                }

                $url = $this->getUrl('*/*/deleteQtyField', array('request_id' => $requested_item->getId()));
                $priceLine['value'] = '<input type="text" name="product[' . $requested_item->getId() . '][price]"
                    value="' . $showPrice . '" size="3" class="required-entry validate-zero-or-greater required-entry input-text proposalprice proposalprice-quote-item-'.$this->getItem()->getId().' sku-' . $this->getProduct()->getSku() . '"  style="width:70px;" id="price-' . $requested_item->getId() . '" ' . $this->getRenderedBlock()->isQuoteReadOnly('input') . '>';

                if (!$this->getRenderedBlock()->isQuoteReadOnly()) {
                    $priceLine['value'] .= '&nbsp;<a title="' . Mage::helper('qquoteadv')->__('Delete') . '" href="' . $url . '"><img align="absmiddle" src="' . $this->getSkinUrl('images/minus-icon.png') . '" width="16" height="16" alt="' . Mage::helper('qquoteadv')->__('Remove item') . '" /></a>';
                }

                $priceLine['req_qty'] = $requested_item->getRequestQty();

                $priceLine['quotePrice'] = (isset($ownerPrice)) ? $ownerPrice : $showPrice;

                $prices[] = $priceLine;

                $margins[] = '<div style="height:25px;" id="margin-' . $requested_item->getId() . '"></div>';
                $i++;
            endforeach;
        else:
            $request_item = null;
        endif;
        return array(
            'prices' => $prices,
            'margins' => $margins,
            'customPriceLineHtml' => $customPriceLineHtml,
            'hideRadio' => $hideRadio,
            'rowTotalLine' => $rowTotalLine
        );

    }

    /**
     * Returns the subtotal information in the table footer
     * @return String
     */
    public function getColumnTotal()
    {
        return '--';
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