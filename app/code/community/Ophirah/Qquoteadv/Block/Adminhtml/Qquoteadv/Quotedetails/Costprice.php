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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Quotedetails_Costprice
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Quotedetails_Costprice extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Setter for request id
     *
     * @param $requestId
     * @return $this
     */
    public function setRequestId($requestId)
    {
        $this->setData('request_id', $requestId);
        return $this;
    }

    /**
     * Setter for cost price
     *
     * @param $costPrice
     * @return $this
     */
    public function setCostPrice($costPrice)
    {
        $this->setData('cost_price', $costPrice);
        return $this;
    }

    /**
     * Getter for request id
     *
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->getData('request_id');
    }

    /**
     * Getter for cost price
     *
     * @return mixed
     */
    public function getCostPrice()
    {
        return $this->getData('cost_price');
    }
}
