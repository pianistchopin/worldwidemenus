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
 * Calculate items and address amounts including/excluding tax
 */
class Ophirah_Qquoteadv_Model_Tax_Sales_Total_Quote_Subtotal extends Mage_Tax_Model_Sales_Total_Quote_Subtotal
{
    /**
     * @var int
     */
    protected $_currentPrice = 0;

    /**
     * Recalculate row information for item based on children calculation
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _recalculateParent(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $price = 0;
        $basePrice = 0;
        $rowTotal = 0;
        $baseRowTotal = 0;
        $priceInclTax = 0;
        $basePriceInclTax = 0;
        $rowTotalInclTax = 0;
        $baseRowTotalInclTax = 0;
        foreach ($item->getChildren() as $child) {
            $price += $child->getPrice() * $child->getQty();
            $basePrice += $child->getBasePrice() * $child->getQty();
            $rowTotal += $child->getRowTotal();
            $baseRowTotal += $child->getBaseRowTotal();
            $priceInclTax += $child->getPriceInclTax() * $child->getQty();
            $basePriceInclTax += $child->getBasePriceInclTax() * $child->getQty();
            $rowTotalInclTax += $child->getRowTotalInclTax();
            $baseRowTotalInclTax += $child->getBaseRowTotalInclTax();
        }


        /**
         *
         * Customisation To make the custom price work with configurable items
         *
         **/

        if ($item->getCustomPrice()) {
            $customPrice = $item->getCustomPrice();
            $price = $customPrice;
            $basePrice = $customPrice;
            $rowTotal = $customPrice * $item->getQty();
            $baseRowTotal = $customPrice * $item->getQty();
            $priceInclTax = $customPrice;
            $basePriceInclTax = $customPrice;
            $rowTotalInclTax = $customPrice * $item->getQty();
            $baseRowTotalInclTax = $customPrice * $item->getQty();
        }

        $item->setConvertedPrice($price);
        $item->setPrice($basePrice);
        $item->setRowTotal($rowTotal);
        $item->setBaseRowTotal($baseRowTotal);
        $item->setPriceInclTax($priceInclTax);
        $item->setBasePriceInclTax($basePriceInclTax);
        $item->setRowTotalInclTax($rowTotalInclTax);
        $item->setBaseRowTotalInclTax($baseRowTotalInclTax);
        return $this;
    }

    /**
     * Calculate item price including/excluding tax, row total including/excluding tax
     * and subtotal including/excluding tax.
     * Determine discount price if needed
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_store = $address->getQuote()->getStore();
        $this->_address = $address;

        $this->_subtotalInclTax = 0;
        $this->_baseSubtotalInclTax = 0;
        $this->_subtotal = 0;
        $this->_baseSubtotal = 0;
        $this->_roundingDeltas = array();

        $address->setSubtotalInclTax(0);
        $address->setBaseSubtotalInclTax(0);
        $address->setTotalAmount('subtotal', 0);
        $address->setBaseTotalAmount('subtotal', 0);

        $items = $this->_getAddressItems($address);
        if (!$items) {
            return $this;
        }

        $addressRequest = $this->_getAddressTaxRequest($address);
        $storeRequest = $this->_getStoreTaxRequest($address);
        $this->_calculator->setCustomer($address->getQuote()->getCustomer());
        if ($this->_config->priceIncludesTax($this->_store)) {
            $classIds = array();
            foreach ($items as $item) {
                $classIds[] = $item->getProduct()->getTaxClassId();
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $classIds[] = $child->getProduct()->getTaxClassId();
                    }
                }
            }
            $classIds = array_unique($classIds);
            $storeRequest->setProductClassId($classIds);
            $addressRequest->setProductClassId($classIds);
            $this->_areTaxRequestsSimilar = $this->_calculator->compareRequests($storeRequest, $addressRequest);
        }

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }


            $origItem = clone $item;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_processItem($child, $addressRequest);
                    if ($child->getTaxPercent() > 0) {
                        $item->setTaxPercent($child->getTaxPercent());
                    }
                }

                $this->_recalculateParent($item);

            } else {
                $this->_processItem($item, $addressRequest);
            }

            if ($origItem->getCustomPrice()) {
                $taxPercent = $item->getTaxPercent();
                $customPrice = $origItem->getCustomPrice();
                $price = $customPrice;
                try {
                    $storeId = Mage::getSingleton('adminhtml/session_quote')->getStore()->getId();
                } catch (Exception $e) {
                    $storeId = Mage::app()->getStore()->getId();
                    Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                }
                $currencyCode = Mage::app()->getStore($storeId)->getCurrentCurrencyCode();
                $baseCurrency = Mage::app()->getStore($storeId)->getBaseCurrencyCode();//Mage::app()->getBaseCurrencyCode();
                if ($currencyCode != $baseCurrency) {
                    $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrency, $currencyCode);
                    $rate = $rates[$currencyCode];
                } else {
                    $rate = 1;
                }

                // Get selected tier Qty
                self::getCurrentQty($item);
                if ($item->_currentPrice > 0) {
                    $price = $customPrice = $item->_currentPrice;
                }

                $baseCustomPrice = $customPrice / $rate;
                $taxAmount = ($customPrice * ($taxPercent / 100));
                $baseTaxAmount = ($baseCustomPrice * ($taxPercent / 100));

                $basePrice = $customPrice;
                $rowTotal = $customPrice * $item->getQty();
                $baseRowTotal = $baseCustomPrice * $item->getQty();
                $priceInclTax = $customPrice + $taxAmount;
                $basePriceInclTax = $baseCustomPrice + $baseTaxAmount;
                $rowTotalInclTax = $priceInclTax * $item->getQty();
                $baseRowTotalInclTax = $basePriceInclTax * $item->getQty();

                $item->setCustomPrice($origItem->getCustomPrice());
                $item->setConvertedPrice($price);
                $item->setPrice($basePrice);
                $item->setRowTotal($rowTotal);
                $item->setBaseRowTotal($baseRowTotal);
                $item->setTaxableAmount($rowTotal);
                $item->setBaseTaxableAmount($baseRowTotal);
                $item->setPriceInclTax($priceInclTax);
                $item->setBasePriceInclTax($basePriceInclTax);
                $item->setRowTotalInclTax($rowTotalInclTax);
                $item->setBaseRowTotalInclTax($baseRowTotalInclTax);

                $count = 0;
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        if ($count === 0) {
                            $child->setTaxableAmount($rowTotal);
                            $child->setBaseTaxableAmount($baseRowTotal);
                        } else {
                            $child->setTaxableAmount(0);
                            $child->setBaseTaxableAmount(0);
                        }

                        $count++;
                    }
                }
            }
            $this->_addSubtotalAmount($address, $item);
        }

        $address->setRoundingDeltas($this->_roundingDeltas);
        return $this;
    }

    /**
     * Sets the requested quantity on an item
     *
     * @param $item
     * @return mixed
     */
    public function getCurrentQty($item)
    {
        $newProd = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $item->getData('quote_id'))
            ->addFieldToFilter('product_id', $item->getData('product_id'));

        if (count($newProd) > 1) {
            $buyRequest = $item->getBuyRequest()->getData();

            $attTypes = array('bundle_option', 'super_attribute', 'option', 'super_product_config');
            foreach ($attTypes as $attType) {
                if (isset($buyRequest[$attType])) {
                    $type = $attType;
                }
            }

            foreach ($newProd as $key => $value) {
                $prodAttribute = unserialize($value->getAttribute());
                if (isset($type)) {

                    if ($prodAttribute[$type] == $buyRequest[$type]) {
                        $item->setQty($value->getData('qty'));
                        $itemPrices = Mage::getModel('qquoteadv/requestitem')->getCollection()
                            ->addFieldToFilter('quoteadv_product_id', $value->getData('id'))
                            ->addFieldToFilter('request_qty', $value->getData('qty'));
                        foreach ($itemPrices as $itemPrice) {
                            $item->_currentPrice = $itemPrice->getData('owner_cur_price');

                        }
                    }
                } else {
                    $simple = true;
                    foreach ($attTypes as $type) {
                        if (isset($prodAttribute[$type])) {
                            $simple = false;
                        }
                    }

                    if ($simple === true) {
                        $item->setQty($value->getData('qty'));
                        $itemPrices = Mage::getModel('qquoteadv/requestitem')->getCollection()
                            ->addFieldToFilter('quoteadv_product_id', $value->getData('id'))
                            ->addFieldToFilter('request_qty', $value->getData('qty'));
                        foreach ($itemPrices as $itemPrice) {
                            $item->_currentPrice = $itemPrice->getData('owner_cur_price');
                        }

                    }

                }

            }
        }

        return $item;
    }
}
