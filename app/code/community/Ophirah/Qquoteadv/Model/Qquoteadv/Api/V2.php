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
 * Class Ophirah_Qquoteadv_Model_Qquoteadv_Api_V2
 */
class Ophirah_Qquoteadv_Model_Qquoteadv_Api_V2 extends Ophirah_Qquoteadv_Model_Qquoteadv_Api
{
    /**
     * Retrieve list of quotations using filters
     *
     * @param array $filters
     * @return array
     */
    public function items($filters)
    {
        /** @var \Ophirah_Qquoteadv_Model_Mysql4_Qqadvcustomer_Collection $_collection */
        $_collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
        $_collection->addFieldToFilter('is_quote', 1);

        $preparedFilters = array();
        if (isset($filters->filter)) {
            foreach ($filters->filter as $_filter) {
                $preparedFilters[$_filter->key] = $_filter->value;
            }
        }

        if (isset($filters->complex_filter)) {
            foreach ($filters->complex_filter as $_filter) {
                $_value = $_filter->value;
                $preparedFilters[$_filter->key] = array(
                    $_value->key => $_value->value
                );
            }
        }

        if (!empty($preparedFilters)) {
            try {
                foreach ($preparedFilters as $field => $value) {
//                    if (isset($this->_mapAttributes[$field])) {
//                        $field = $this->_mapAttributes[$field];
//                    }
                    $_collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $data = $_collection->toArray();
        return $data['items'];
    }

    /**
     * Function that sets a quote as imported
     *
     * @param array $quote_id
     * @param $value
     * @return bool
     */
    public function setimported($quote_id, $value)
    {
        $_quote = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quote_id);

        if (!$_quote->getId()) {
            $this->_fault('quote_not_exists');
        }

        try {
            $_quote->setImported((bool)$value);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quote));
            $_quote->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quote));
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
        }

        return true;
    }

    /**
     * Delete requested qty
     *
     * @param array $quoteId
     * @param $requestId
     * @return array
     * @throws Mage_Api_Exception
     * @internal param array $params int quote_idint quote_id
     * int request_id
     */
    public function delete_requested_qty($quoteId, $requestId)
    {
        $response = array(
            'success' => false
        );

        if (!isset($requestId) or !isset($quoteId)) {
            $this->_fault('data_invalid', "RequestId or QuoteId parameters didn't not received");
        }

        $requestId = (int)$requestId;
        $quoteId = (int)$quoteId;

        $_quoteadv = Mage::getModel('qquoteadv/requestitem')->load($requestId);
        if (!$_quoteadv->getId()) {
            $this->_fault('data_invalid', "RequestId not exists");
        } elseif ($_quoteadv->getId() && $_quoteadv->getQuoteId() != $quoteId) {
            $this->_fault('data_invalid', "RequestId is wrong by QuoteId");
        }

        $itemData = $_quoteadv->getData();
        $id = $itemData['quoteadv_product_id'];
        $_itemsCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
            ->addFieldToFilter('quoteadv_product_id', $id)
            ->addFieldToFilter('quote_id', $quoteId);

        if ($_itemsCollection->getSize() > 1) {
            try {
                $_quoteadv->delete();

                /** @var \Ophirah_Qquoteadv_Model_Qqadvcustomer $quote */
                $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                $quote->collectTotals();
                $quote->save();

                $response = array(
                    'success' => true
                );
            } catch (Exception $e) {
                $this->_fault('delete_error', $e->getMessage());
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
            }

            $this->_updateQuoteStatus($quoteId);
        } else {
            $this->_fault('data_invalid', 'Minimum of one Qty is required');
        }

        return $response;
    }

    /**
     * Delete requested item
     *
     * @param array $quoteId
     * @param $id
     * @return array
     * @throws Mage_Api_Exception
     * @internal param array $params int $params['quote_id']int $params['quote_id']
     * int $params['primary_key']
     */
    public function delete_requested_item($quoteId, $id)
    {
        $response = array(
            'success' => false
        );

        if (!isset($id) or !isset($quoteId)) {
            $this->_fault('data_invalid', "PrimaryKey or QuoteId didn't not received");
        }

        $_quoteadv = Mage::getModel('qquoteadv/qqadvproduct')->load((int)$id);
        if (!$_quoteadv->getId()) {
            $this->_fault('data_invalid', "Data by PrimaryKey not exists");
        } else {
            if ($_quoteadv->getData('quote_id') != $quoteId) {
                $this->_fault('data_invalid', 'PrimaryKey is wrong by QuoteId');
            }
        }

        $itemData = $_quoteadv->getData();
        if (count($itemData) > 0) {
            $_itemsCollection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection();
            $_itemsCollection->addFieldToFilter('quote_id', $quoteId);

            if ($_itemsCollection->getSize() > 1) {
                try {
                    $_quoteadv->delete();

                    /** @var \Ophirah_Qquoteadv_Model_Qqadvcustomer $quote */
                    $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                    $quote->collectTotals();
                    $quote->save();

                    $response = array(
                        'success' => true
                    );
                } catch (Exception $e) {
                    $this->_fault('data_invalid', $e->getMessage());
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                }
            } else {
                $this->_fault('data_invalid', 'Minimum of one Item is required');
            }
        }

        return $response;
    }

    /**
     * Function that allows for changing the requested qty
     *
     * @param array $request_id
     * @param $quote_id
     * @param $product_id
     * @param $request_qty
     * @param $owner_base_price
     * @param $original_price
     * @param $quoteadv_product_id
     * @return array
     */
    public function modify_requested_qty(
        $request_id,
        $quote_id,
        $product_id,
        $request_qty,
        $owner_base_price,
        $original_price,
        $quoteadv_product_id
    ) {
        $response = array(
            'success' => false
        );

        if (isset($request_id)
            && isset($quote_id)
            && isset($product_id)
            && isset($request_qty)
            && isset($owner_base_price)
            && isset($original_price)
            && isset($quoteadv_product_id)
        ) {

            $quote_id = (int)$quote_id;
            $key = (int)$quoteadv_product_id;

            $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                ->addFieldToFilter('quote_id', $quote_id)
                ->addFieldToFilter('quoteadv_product_id', $key);

            if ($_collection->getSize() > 0) {
                $_collection->clear();
                $_collection->addFieldToFilter('request_qty', $request_qty);

                $data = $_collection->getData();
                if (count($data) > 0) {
                    $this->_fault('dublicate_data', 'Duplicate qty value entered');
                }

                /** @var \Ophirah_Qquoteadv_Model_Requestitem $item */
                $item = Mage::getModel('qquoteadv/requestitem')->load((int)$request_id);
                if (!$item->getRequestId()) {
                    $this->_fault('data_invalid', 'Item not exists by RequestId');
                }

                // Update quote
                try {
                    //change selected product data in quoteadv_product
                    /** @var \Ophirah_Qquoteadv_Model_Qqadvproduct $productItem */
                    $productItem = Mage::getModel('qquoteadv/qqadvproduct')->load($item->getQuoteadvProductId());
                    try {
                        $orgQty = $productItem->getQty();
                        $requestQty = $item->getRequestQty();
                        if ($orgQty == $requestQty) {
                            $productItem->setQty($request_qty);
                            $productItem->save();
                        }
                    } catch (Exception $e) {
                        Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                    }

                    $item->setRequestQty($request_qty);
                    $item->setOwnerBasePrice($owner_base_price);
                    $item->setOriginalPrice($original_price);
                    $item->save();

                    /** @var \Ophirah_Qquoteadv_Model_Qqadvcustomer $_qquoteadv */
                    $_qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quote_id);
                    $_qquoteadv->collectTotals();
                    $_qquoteadv->save();
                } catch (Exception $e) {
                    $this->_fault('save_error', $e->getMessage());
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                }

                $response = array(
                    'success' => true
                );
            } else {
                $this->_fault('not_exists', 'Data by QuoteId and RequestId not exists');
            }
        } else {
            $this->_fault('data_invalid', "Initial parameters didn't not received");
        }

        return $response;
    }

    /**
     * Function that allows setting the item comment
     *
     * @param array $quoteadv_product_id
     * @param $comment
     * @return array
     */
    public function set_item_comment($quoteadv_product_id, $comment)
    {
        $limit = 400;

        if (!isset($quoteadv_product_id) && !isset($comment)) {
            $this->_fault('data_invalid', "Initial parameters didn't not received");
        }

        if (strlen($comment) > $limit) {
            $msg = sprintf("Quote comment length has limit %s characters", $limit);
            $this->_fault('data_invalid', $msg);
        }

        $_quoteadv = Mage::getModel('qquoteadv/qqadvproduct')->load((int)$quoteadv_product_id);
        if (!$_quoteadv->getId()) {
            $this->_fault('quote_not_exists');
        }

        try {
            $_quoteadv->setClientRequest($comment);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
            $_quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
        } catch (Exception $e) {
            $this->_fault('save_error', $e->getMessage());
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
        }

        $response = array(
            'success' => true
        );

        return $response;
    }

    /**
     * Function that allows to set the quote comment
     *
     * @param array $quote_id
     * @param $comment
     * @return array
     */
    public function set_proposal_comment($quote_id, $comment)
    {
        if (!isset($quote_id) or !isset($comment)) {
            $this->_fault('data_invalid', "QuoteId or Comment parameters didn't not received");
        }

        $quoteId = $quote_id;
        $comment = trim($comment);
        $len = strlen($comment);
        if ($len > $this->_limitComment) {
            $msg = sprintf("Comment length overlimit %s characters", $len - $this->_limitComment);
            $this->_fault('data_invalid', $msg);
        }

        $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);
        if (!$_quoteadv->getId()) {
            $this->_fault('quote_not_exists');
        }

        try {
            $_quoteadv->setClientRequest($comment);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
            $_quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
        } catch (Exception $e) {
            $this->_fault('save_error', $e->getMessage());
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
        }

        $response = array(
            'success' => true
        );

        return $response;
    }

    /**
     * Function to set the shipping method and price
     *
     * @param array $quote_id
     * @param $shipping_price
     * @param $shipping_type
     * @return array
     */
    public function set_shipping($quote_id, $shipping_price, $shipping_type)
    {
        if (!isset($quote_id) or !isset($shipping_type) or !isset($shipping_price)) {
            $this->_fault('data_invalid', "QuoteId or ShippingType or ShippingPrice parameters didn't not received");
        }
        $quoteId = (int)$quote_id;
        $type = (string)$shipping_type;
        $price = (float)$shipping_price;

        if (empty($type)) {
            $price = -1;
        } elseif (($type == "I" or $type == "O") && $price > 0) {
            //ok
        } else {
            $this->_fault('data_invalid');
        }

        $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        if (!$_quoteadv->getId()) {
            $this->_fault('quote_not_exists');
        }

        try {
            $_quoteadv->setShippingType($type);
            $_quoteadv->setShippingPrice($price);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
            $_quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
        } catch (Exception $e) {
            $this->_fault('save_error', $e->getMessage());
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
        }

        $response = array(
            'success' => true
        );

        return $response;
    }

    /**
     * Function to add a request item on a given quote with a given qty
     *
     * @param array $quote_id
     * @param $product_id
     * @param $quoteadv_product_id
     * @param $request_qty
     * @param $owner_base_price
     * @param $original_price
     * @return array
     */
    public function add_qtybyitem(
        $quote_id,
        $product_id,
        $quoteadv_product_id,
        $request_qty,
        $owner_base_price,
        $original_price
    ) {
        $response = array(
            'success' => false
        );

        if (isset($quote_id)
            && isset($product_id)
            && isset($quoteadv_product_id)
            && isset($request_qty)
            && isset($owner_base_price)
            && isset($original_price)
        ) {

            $key = $quoteadv_product_id;
            $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                ->addFieldToFilter('quote_id', (int)$quote_id)
                ->addFieldToFilter('product_id', (int)$product_id)
                ->addFieldToFilter('quoteadv_product_id', (int)$key);

            if ($_collection->getSize() > 0) {
                $_collection->clear();
                $_collection->addFieldToFilter('request_qty', $request_qty);

                $data = $_collection->getData();
                if (count($data) > 0) {
                    $this->_fault('dublicate_data', 'Duplicate qty value entered');
                }

                $params = array(
                    'quote_id'            => $quote_id,
                    'product_id'          => $product_id,
                    'quoteadv_product_id' => $quoteadv_product_id,
                    'request_qty'         => $request_qty,
                    'owner_base_price'    => $owner_base_price,
                    'original_price'      => $original_price,
                );

                try {
                    Mage::getModel('qquoteadv/requestitem')->setData($params)->save();

                    /** @var \Ophirah_Qquoteadv_Model_Qqadvcustomer $_qquoteadv */
                    $_qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quote_id);
                    $_qquoteadv->collectTotals();
                    $_qquoteadv->save();
                } catch (Exception $e) {
                    $this->_fault('save_error', $e->getMessage());
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                }

                $response = array(
                    'success' => true
                );
            } else {
                $this->_fault('not_exists', "Data by QuoteId / QuoteadvProductId / ProductId not found");
            }
        } else {
            $this->_fault('data_invalid', "Initial parameters didn't not received");
        }

        return $response;
    }
}
