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
 * @license     https://www.cart2quote.com/ordering-licenses
 */

/**
 * Class Ophirah_Qquoteadv_Model_Observer_Quote_Save
 */
class Ophirah_Qquoteadv_Model_Observer_Quote_Save
{
    /**
     * Set product group price when saving quote
     *
     * @param $observer $observerProducts
     */
    public function setGroupPrices(Varien_Event_Observer $observer)
    {
        /**
         * @var Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
         */
        $quote = $observer->getQuote();
        if (!$quote instanceof Ophirah_Qquoteadv_Model_Qqadvcustomer) {
            Mage::throwException(
                Mage::helper('qquoteadv')->__(
                    'Passed quote is not an instance of Ophirah_Qquoteadv_Model_Qqadvcustomer'
                )
            );
        }

        /**
         * @var Ophirah_Qquoteadv_Model_Mysql4_Requestitem_Collection $requestItemCollection
         */
        $requestItemCollection = Mage::getModel('qquoteadv/requestitem')
            ->getCollection()
            ->addFieldToSelect(
                array(
                    'product_id',
                    'owner_base_price',
                    'request_qty',
                    'quoteadv_product_id'
                )
            )
            ->addFieldToFilter('quote_id', $quote->getQuoteId());

        /**
         * @var Mage_Catalog_Model_Resource_Product_Collection $productCollection
         */
        $productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addIdFilter($requestItemCollection->getColumnValues('product_id'));

        /**
         * @var Mage_Catalog_Model_Product $product
         */
        foreach ($productCollection->getItems() as $product) {
            /** @var \Ophirah_Qquoteadv_Model_Grouppriceinfo $groupPriceInfo */
            $groupPriceInfo = Mage::getModel('qquoteadv/grouppriceinfo');
            $item = $requestItemCollection->getItemsByColumnValue('product_id', $product->getId());
            if (isset($item[0], $item[0]['owner_base_price'])) {
                $groupPriceInfo->saveGroupColumn($quote, $item);
            }
        }
    }
}
