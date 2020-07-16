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
 * Class Ophirah_Qquoteadv_Model_Tiercost
 */
class Ophirah_Qquoteadv_Model_Tiercost extends Mage_Core_Model_Abstract
{
    /**
     * Construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/tiercost');
    }

    /**
     * Function for getting the tier cost for a give request id
     *
     * @param $requestId
     * @return null
     */
    public function getTierCost($requestId){
        $quoteadvProductRequest = Mage::getModel('qquoteadv/requestitem')->load($requestId);

        if(isset($quoteadvProductRequest) && !empty($quoteadvProductRequest)){
            $qquote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteadvProductRequest->getQuoteId());
            $customer = Mage::getModel("customer/customer")->load($qquote->getCustomerId());
            $groupId = $customer->getGroupId();
            $websiteId = Mage::getModel('core/store')->load($qquote->getStoreId())->getWebsiteId();
            $qty = $quoteadvProductRequest->getRequestQty();
            $productId = $quoteadvProductRequest->getProductId();

            /** @var \Ophirah_Qquoteadv_Model_Tiercost $tierCostItem */
            $tierCostItem = $this->getCollection()
                ->addFieldToFilter('entity_id', $productId)
                ->addFieldToFilter('qty' , array('lteq' => $qty))
                ->addFieldToFilter(
                    array('website_id', 'website_id'),
                    array(
                        array('eq'=>'0'),
                        array('eq'=> $websiteId)
                    )
                )
                ->addFieldToFilter(
                    array('all_groups', 'customer_group_id'),
                    array(
                        array('eq'=>'1'),
                        array('eq'=> $groupId)
                    )
                )
                ->setOrder('qty','DESC')
                ->setOrder('value','ASC')
                ->getFirstItem();

            if ($tierCostItem && $tierCostItem->getId()) {
                return $tierCostItem->getValue();
            }
        }

        return null;
    }
}
