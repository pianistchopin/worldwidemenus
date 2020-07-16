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
 * Class Ophirah_Qquoteadv_Model_Mysql4_Requestitem_Collection
 */
class Ophirah_Qquoteadv_Model_Mysql4_Requestitem_Collection extends Mage_Sales_Model_Resource_Quote_Item_Collection
{
    /**
     * Construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/requestitem');
    }

    /**
     * Function to add qty from postdata
     *
     * @param $paramsProduct
     * @param $quoteadvProductId
     * @param $paramsQuoteId
     * @param array $qtys
     * @return $this
     */
    public function addPostDataQty($paramsProduct, $quoteadvProductId, $paramsQuoteId, array $qtys)
    {
        $quoteProductId = (int)$quoteadvProductId;
        //Remove the item by quoteadv_product_id before adding again
        Mage::getModel('qquoteadv/requestitem')->getResource()->removeByQuoteadvProductId($quoteProductId);

        foreach ($qtys as $qty) {
            if (!isset($paramsProduct[$quoteProductId]) || !isset($paramsProduct[$quoteProductId]['product_id'])) {
                continue;
            }

            if (!empty($qty)) {
                $this->_addItem(
                    Mage::getModel('qquoteadv/requestitem')
                        ->setRequestQty($qty)
                        ->setQuoteadvProductId($quoteProductId)
                        ->setQuoteId($paramsQuoteId)
                        ->setProductId($paramsProduct[$quoteProductId]['product_id'])
                );
            }
        }

        $this->_setIsLoaded(true);
        $this->save();
        return $this;
    }

    /**
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $qqadvproduct
     * @return $this
     */
    public function loadByProductTierQty(Ophirah_Qquoteadv_Model_Qqadvproduct $qqadvproduct)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($qqadvproduct->getProductId());
        $qqadvproductQty = $qqadvproduct->getQty();
        $tierPriceQty = $product->getTierPrice($qqadvproductQty);
        $attribute = unserialize($qqadvproduct->getAttribute());
        $infoBuyRequest = new Varien_Object($attribute);
        $cartCandidates = $product->getTypeInstance(true)->processConfiguration($infoBuyRequest, $product);

        foreach ($cartCandidates as $product) {
            $tierPrices = $product->getTierPrice();
            if (is_array($tierPrices) && !empty($tierPrices)) {
                foreach ($tierPrices as $tierPrice) {
                    if ($tierPriceQty != $tierPrice['price']) {
                        //reload simple of configurable to get the correct price data
                        if (!$product->getPrice()) {
                            $product = Mage::getModel('catalog/product')->load($product->getId());
                        }

                        $tierPrice['price'] = $product->getFinalPrice($tierPrice['price_qty']);
                        $product->setFinalPrice(null);
                        $this->_addItem(
                        /** @var Ophirah_Qquoteadv_Model_Requestitem */
                            Mage::getModel('qquoteadv/requestitem')
                                ->mapTierPrice($tierPrice)
                                ->setQuoteadvProductId($qqadvproduct->getId())
                                ->setQuoteId($qqadvproduct->getQuoteId())
                                ->setProductId($qqadvproduct->getProductId())
                        );
                    }
                }
            }
        }

        $this->_setIsLoaded(true);
        return $this;
    }

    /**
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $qqadvproduct
     * @return $this
     */
    public function saveByProductTierQty(Ophirah_Qquoteadv_Model_Qqadvproduct $qqadvproduct)
    {
        $this->loadByProductTierQty($qqadvproduct)
            ->save();
        return $this;
    }

    /**
     * Overwrite for _assignOptions
     *
     * @return $this
     */
    protected function _assignOptions()
    {
        foreach ($this as $item) {
            $item->setOptions(array());
        }
        return $this;

    }

    /**
     * Overwrite for _afterLoad
     */
    protected function _afterLoad()
    {
        //do nothing
    }
}
