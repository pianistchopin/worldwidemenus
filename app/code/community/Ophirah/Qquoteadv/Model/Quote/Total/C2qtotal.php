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
 * Class Ophirah_Qquoteadv_Model_Quote_Total_C2qtotal
 */
class Ophirah_Qquoteadv_Model_Quote_Total_C2qtotal extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Totals collector for the cart2quote quotation totals
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $items = $this->_getAddressItems($address);
        $quote = $address->getQuote();

        if ($quote->getData('quote_id')){

            // Get custom quote prices for the products by quoteId
            $quoteCustomPrices = Mage::getModel('qquoteadv/qqadvproduct')->getQuoteCustomPrices($quote->getData('quote_id'));

            $optionCount = 0;
            $optionId = 0;
            $countMax = 0;

            // Clear original price information
            $orgFinalBasePrice = 0;
            $orgFinalBasePriceInclTax = 0;
            $orgBasePrice = 0;
            $quoteFinalBasePrice = 0;
            $quoteFinalPrice = 0;
            $quoteCostPrice = 0;
            $calcOrgPrice = true;

            //This optimisations results in discount calculation errors when qty of products are changed
//            // Only Calculate Original Prices Once
//            if ($quote->getOrgFinalBasePrice() > 0) {
//                $calcOrgPrice = false;
//            }

            // AW_Afptc warning:
            if (Mage::helper('core')->isModuleEnabled('AW_Afptc')){
                //this can happen with "aheadWorks Add Free Product to Cart"/"AW_Afptc"
                $message = "Cart2Quote does not has support for 'aheadWorks - Add Free Product to Cart' / 'AW_Afptc' ";
                Mage::log('Warning: ' . $message , null, 'c2q.log');
            }

            Mage::register('requests_handeled', array()); //needed for dynamic bundles
            foreach ($items as $item) {
                /** @var $item \Mage_Sales_Model_Quote_Item */
                if($item->getParentItem() == null){

                    // Counter for option products
                    if ($optionId != $item->getBuyRequest()->getData('product')) {
                        $countMax = Mage::getModel('qquoteadv/qqadvproduct')->getCountMax($item->getBuyRequest());
                    }
                    if ($optionCount == $countMax) {
                        $optionCount = $optionId = 0;
                    }
                    if ($optionId == $item->getBuyRequest()->getData('product') && $optionId != 0) {
                        $optionCount++;
                    }
                    $optionId = $item->getBuyRequest()->getData('product');

                    // Check if quote item has a custom price
                    $item = Mage::getModel('qquoteadv/qqadvproduct')->getCustomPriceCheck($quoteCustomPrices, $item, $optionCount);

                    // Reset Original Price
                    // And add new item original prices
                    $itemFinalPrice = 0;
                    $itemCostPrice = 0;
                    $itemBasePrice = 0;

                    if ($calcOrgPrice === true){
                        if (!$item->getData('parent_item_id')) {
                            $store = Mage::app()->getStore($item->getStoreId());

                            if ($item->getProductType() == "bundle") {
                                $itemProductQty = $item->getProduct()->getQty();
                                if ($itemProductQty === null) {
                                    $itemProductQty = $item->getQty();
                                }

                                if ($item->getData('quote_org_price') > 0 && $itemProductQty > 0) {
                                    // Item Original Price
                                    $itemFinalPrice = $item->getData('quote_org_price') * $itemProductQty;
                                    // Item Base Price
                                    $itemBasePrice = $itemFinalPrice;
                                }

                                /** @var Mage_Tax_Helper_Data $taxHelper */
                                $taxHelper = Mage::helper('tax');
                                // Item Original Price
                                if ($taxHelper->priceIncludesTax($store->getStoreId())) {
                                    //if price is filled including tax, get it excluding tax:
                                    $bundlePriceExcludingTax = $taxHelper->getPrice(
                                        $item->getProduct(),
                                        $itemFinalPrice,
                                        false,
                                        $address,
                                        null,
                                        null,
                                        $item->getStoreId(),
                                        true,
                                        false
                                    );
                                    $bundleProductFinalPrice = $bundlePriceExcludingTax;
                                    $bundleProductFinalPriceInclTax = $itemFinalPrice;

                                } else {
                                    $bundleProductFinalPrice = $itemFinalPrice;
                                    $bundleProductFinalPriceInclTax = $taxHelper->getPrice(
                                        $item->getProduct(),
                                        $itemFinalPrice,
                                        true,
                                        $address,
                                        null,
                                        null,
                                        $item->getStoreId(),
                                        false,
                                        false
                                    );
                                    $bundleProductPrice = $item->getProduct()->getPrice();
                                }

                                // prepare store item original price
                                $itemFinalPrice = $bundleProductFinalPrice;
                                $itemFinalPriceInclTax = $bundleProductFinalPriceInclTax;

                                if (!isset($bundleProductPrice) || $bundleProductPrice == 0) {
                                    $itemBasePrice = $bundleProductFinalPrice;
                                } else {
                                    $itemBasePrice = $bundleProductPrice;
                                }
                            } else {
                                //check if this product has options
                                if($item->getProduct()->getHasOptions() == 1){
                                    //$baseItemFinalPrice = $item->getProduct()->getFinalPrice();
                                    $getFinalPrice = $item->getProduct()->getFinalPrice();

                                    $requestItemCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                                        ->setQuote($quote)
                                        ->addFieldToFilter('quote_id', $quote->getId())
                                        ->addFieldToFilter('product_id', $item->getProduct()->getId())
                                        ->addFieldToFilter('request_qty', $item->getQty());

                                    if($item->getQuoteadvProductId()){
                                        $requestItemCollection->addFieldToFilter('quoteadv_product_id', $item->getQuoteadvProductId());
                                    }

                                    $requestItem = $this->_getFirstRequestItem($requestItemCollection);
                                    if($requestItem){
                                        if($requestItem->getOriginalCurPrice() != 0){
                                            //$baseItemFinalPrice = $requestItem->getOriginalPrice();
                                            //base is different from Magento base here.
                                            //$baseItemFinalPrice = $requestItem->getOriginalCurPrice();
                                            $getFinalPrice = $requestItem->getOriginalCurPrice();
                                        }
                                    }
                                } elseif (Mage::getModel('qquoteadv/qqadvproductdownloadable')->isDownloadable($item)){
                                    //$optionsinfo = $item->getBuyRequest();
                                    $item = Mage::getModel('qquoteadv/qqadvproductdownloadable')->prepareDownloadableProductFromBuyRequest($item);

                                    $getFinalPrice = $item->getProduct()->getFinalPrice();

                                    $requestItemCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                                        ->setQuote($quote)
                                        ->addFieldToFilter('quote_id', $quote->getId())
                                        ->addFieldToFilter('product_id', $item->getProduct()->getId())
                                        ->addFieldToFilter('request_qty', $item->getQty());

                                    if($item->getQuoteadvProductId()){
                                        $requestItemCollection->addFieldToFilter('quoteadv_product_id', $item->getQuoteadvProductId());
                                    }

                                    $requestItem = $this->_getFirstRequestItem($requestItemCollection);
                                    if($requestItem){
                                        if($requestItem->getOriginalCurPrice() != 0){
                                            $getFinalPrice = $requestItem->getOriginalCurPrice();
                                        }
                                    }
                                } else {
                                    //usually simple products
                                    $getFinalPrice = $item->getProduct()->getFinalPrice();

                                    $requestItemCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                                        ->setQuote($quote)
                                        ->addFieldToFilter('quote_id', $quote->getId())
                                        ->addFieldToFilter('product_id', $item->getProduct()->getId())
                                        ->addFieldToFilter('request_qty', $item->getQty());

                                    if($item->getQuoteadvProductId()){
                                        $requestItemCollection->addFieldToFilter('quoteadv_product_id', $item->getQuoteadvProductId());
                                    }

                                    $requestItem = $this->_getFirstRequestItem($requestItemCollection);
                                    if($requestItem){
                                        if($requestItem->getOriginalCurPrice() != 0){
                                            $getFinalPrice = $requestItem->getOriginalCurPrice();
                                        }
                                    }
                                }

                                /** @var Mage_Tax_Helper_Data $taxHelper */
                                $taxHelper = Mage::helper('tax');
                                // Item Original Price
                                if ($taxHelper->priceIncludesTax($store->getStoreId())) {
                                    //if price is filled including tax, get it excluding tax:
                                    $itemPriceExcludingTax = $taxHelper->getPrice(
                                        $item->getProduct(),
                                        $getFinalPrice,
                                        false,
                                        $address,
                                        null,
                                        null,
                                        $item->getStoreId(),
                                        true,
                                        false
                                    );
                                    $itemProductFinalPrice = $itemPriceExcludingTax;
                                    $itemProductFinalPriceInclTax = $getFinalPrice;

                                } else {
                                    $itemProductFinalPrice = $getFinalPrice;
                                    $itemProductFinalPriceInclTax = $taxHelper->getPrice(
                                        $item->getProduct(),
                                        $getFinalPrice,
                                        true,
                                        $address,
                                        null,
                                        null,
                                        $item->getStoreId(),
                                        false,
                                        false
                                    );
                                    $itemProductPrice = $item->getProduct()->getPrice();
                                }

                                $itemProductQty = $item->getQty();
                                $itemFinalPrice = $itemProductFinalPrice * $itemProductQty;
                                $itemFinalPriceInclTax = $itemProductFinalPriceInclTax * $itemProductQty;

                                if(!isset($itemProductPrice) || $itemProductPrice == 0){
                                    $itemBasePrice = $itemProductFinalPrice * $itemProductQty;
                                } else {
                                    $itemBasePrice = $itemProductPrice * $itemProductQty;
                                }
                                // Item Cost Price
                            }

                            $itemProductQty = $item->getProduct()->getQty();
                            if ($itemProductQty === null) {
                                $itemProductQty = $item->getQty();
                            }

                            // Store item cost price
                            $itemCostPrice = (float)$item->getData('quote_item_cost') * $itemProductQty;
                            if ($itemCostPrice > 0) {
                                $quoteCostPrice += $itemCostPrice;
                            }

                            // Store item original price
                            $orgFinalBasePrice += $itemFinalPrice;
                            $orgFinalBasePriceInclTax += $itemFinalPriceInclTax;
                            $orgBasePrice += $itemBasePrice;

                            // Store Original Total with address
                            $address->setOrgFinalBasePrice($orgFinalBasePrice);
                            $address->setOrgFinalBasePriceInclTax($orgFinalBasePriceInclTax);
                            $address->setOrgBasePrice($orgBasePrice);
                            $address->setQuoteBaseCostPrice($quoteCostPrice);
                        }
                    } else {
                        //set these prices from the last calculation
                        $address->setOrgFinalBasePrice($quote->getOrgFinalBasePrice());
                        $address->setOrgFinalBasePriceInclTax($quote->getOrgFinalBasePriceInclTax());
                        $address->setOrgBasePrice($quote->getOrgBasePrice());
                        $address->setQuoteBaseCostPrice($quote->getQuoteBaseCostPrice());
                    }

                    // set custom price, if available
                    if ($item->getData('custom_base_price') != null && $item->getData('custom_base_price') > 0) {
                        $itemCustomPrice = $item->getData('custom_price');

                        //Custom price is saved without website tax
                        if (Mage::helper('tax')->priceIncludesTax($item->getStoreId())) {
                            /** @var \Mage_Tax_Model_Calculation $taxCalculation */
                            $taxCalculation = Mage::getModel('tax/calculation');

                            /** @var \Ophirah_Qquoteadv_Model_Customer_Customer $customer */
                            $customer = $quote->getCustomer();
                            if ($customer) {
                                $taxCalculation->setCustomer($customer);
                            }

                            $request = $taxCalculation->getRateOriginRequest($item->getStore());

                            //get tax percent
                            $taxClassId = $item->getProduct()->getTaxClassId();
                            $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));

                            //get user tax
                            $customerTaxClass = null;
                            if ($quote->getCustomerTaxClassId()) {
                                $userRequest = $taxCalculation->getRateRequest(
                                    $quote->getShippingAddress(),
                                    $quote->getBillingAddress(),
                                    $quote->getCustomerTaxClassId(),
                                    $quote->getStore()
                                );

                                $percent = $taxCalculation->getRate($userRequest->setProductClassId($taxClassId));
                            }

                            if ($percent > 0) {
                                $itemCustomPrice = ($itemCustomPrice / 100) * (100 + $percent);
                            }
                        }

                        // New custom Price
                        $rowTotal = $itemCustomPrice;
                        $baseRowTotal = $item->getData('custom_base_price');

                        $itemProductQty = $item->getProduct()->getQty();
                        if ($itemProductQty === null) {
                            $itemProductQty = $item->getQty();
                        }

                        // Store item custom price
                        $itemQuoteBasePrice = $item->getData('custom_base_price') * $itemProductQty;
                        $quoteFinalBasePrice += $itemQuoteBasePrice;
                        $address->setQuoteFinalBasePrice($quoteFinalBasePrice);

                        $itemQuotePrice = $itemCustomPrice * $itemProductQty;
                        $quoteFinalPrice += $itemQuotePrice;
                        $address->setQuoteFinalPrice($quoteFinalPrice);

                        // remove original item price from subtotal
                        $address->setTotalAmount(
                            'subtotal', $address->getSubtotal() - $item->getRowTotal()
                        );
                        $address->setBaseTotalAmount(
                            'subtotal', $address->getBaseSubtotal() - $item->getBaseRowTotal()
                        );

                        // Set custom price for the product
                        $item->setPrice($baseRowTotal)
                            ->setConvertedPrice($rowTotal)
                            ->setBaseOriginalPrice($baseRowTotal)
                            ->calcRowTotal();
                    }

                    $item->setQtyToAdd(0);
                }
            }

            Mage::unregister('requests_handeled'); //needed for dynamic bundles
        }

        return $this;
    }

    /**
     * Function that cleans the request item collection of duplicated values
     *
     * @param $requestItemCollection Ophirah_Qquoteadv_Model_Mysql4_Requestitem_Collection
     * @return mixed
     */
    private function _getFirstRequestItem($requestItemCollection)
    {
        //no cleaning required if there is only one option
        if ($requestItemCollection->getSize() == 1) {
            return $requestItemCollection->getFirstItem();
        }

        $newestRequestItem = null;
        $highestPricedRequestItem = null;
        $bestRequestItem = null;

        $newestRequestItemId = 0;
        $highestRequestItemPrice = 0;

        //find best items
        foreach ($requestItemCollection as $requestItem) {
            //find the newest request item
            if ($newestRequestItemId < $requestItem->getId()) {
                $newestRequestItemId = $requestItem->getId();
                $newestRequestItem = $requestItem;
            }

            //find the highest priced request item
            if ($highestRequestItemPrice < $requestItem->getOriginalPrice()) {
                $highestRequestItemPrice = $requestItem->getOriginalPrice();
                $highestPricedRequestItem = $requestItem;
            }
        }

        //default best request item
        $bestRequestItem = $requestItemCollection->getFirstItem();

        //find better request item
        if ($newestRequestItem != null) {
            $bestRequestItem = $newestRequestItem;
            if (($highestPricedRequestItem != null) && !($newestRequestItem->getOriginalPrice() > 0)) {
                $bestRequestItem = $highestPricedRequestItem;
            }
        }

        //remove bad request items
        foreach ($requestItemCollection as $requestItem) {
            if ($requestItem->getId() != $bestRequestItem->getId()) {
                $message = 'Found duplicate request items, removed duplicate: ' . $requestItem->getId();
                Mage::log('Warning: ' . $message, null, 'c2q.log');
                $requestItem->delete();
            }
        }

        return $bestRequestItem;
    }
}
