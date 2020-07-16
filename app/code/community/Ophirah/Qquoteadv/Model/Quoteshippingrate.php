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
 * Class Ophirah_Qquoteadv_Model_Quoteshippingrate
 */
class Ophirah_Qquoteadv_Model_Quoteshippingrate extends Mage_Core_Model_Abstract
{
    /**
     * from Mage_Shipping_Model_Rate_Abstract
     * @var
     */
    static protected $_instances;

    /**
     * @var
     */
    public $carrier_sort_order;

    /**
     * Basic Construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/quoteshippingrate');
    }

    /**
     * Modified from
     * Mage_Shipping_Model_Rate_Abstract
     *
     * @return string
     */
    public function getCarrierInstance()
    {
        $code = $this->getCarrier();
        if (!isset(self::$_instances[$code])) {
            self::$_instances[$code] = Mage::getModel('shipping/config')->getCarrierInstance($code);
        }
        return self::$_instances[$code];
    }

    /**
     * Retrieve all grouped shipping rates
     * Modified from Mage_Sales_Model_Quote_Address
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @return array
     */
    public function getGroupedAllShippingRates(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote)
    {
        $rates = array();

        $ratesObj = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
            ->addFieldToFilter('address_id', array('eq' => $quote->getAddress()->getAddressId()))
            ->addFieldToFilter('active', array('eq' => 1));

        if ($this->hasNominalItems(false)) {
            $ratesObj->setFixedOnlyFilter(true);
        }
        if ($quote->getAddress()->getAddressId()) {
            foreach ($ratesObj as $rate) {
                $rate->setAddress($this);

            }
        }

        // modified loop
        foreach ($ratesObj as $rate) {
            if (!$rate->isDeleted() && $rate->getCarrierInstance()) {
                if (!isset($rates[$rate->getCarrier()])) {
                    $rates[$rate->getCarrier()] = array();
                }

                $rates[$rate->getCarrier()][] = $rate;
                $rates[$rate->getCarrier()][0]->carrier_sort_order = $rate->getCarrierInstance()->getSortOrder();
            }
        }

        uasort($rates, array($this, '_sortRates'));

        return $rates;
    }

    /**
     * Sort rates recursive callback
     * Modified from Mage_Sales_Model_Quote_Address
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortRates($a, $b)
    {
        if ((int)$a[0]->carrier_sort_order < (int)$b[0]->carrier_sort_order) {
            return -1;
        } elseif ((int)$a[0]->carrier_sort_order > (int)$b[0]->carrier_sort_order) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Set current shipping methods for address id
     * to inactive
     *
     * @param string /integer $addressId
     * @return boolean / collection         // Ophirah_Qquoteadv_Model_Quoteshippingrate
     */
    public function resetQuoteRates($addressId)
    {
        $collection = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
            ->addFieldToFilter('address_id', array('eq' => $addressId))
            ->load();

        // Set Current rates to Inactive
        if ($collection) {
            foreach ($collection as $deleteRate) {
                $deleteRate->setData('active', 0);
                $deleteRate->save();
            }
            return $collection;
        }

        return false;
    }

    /**
     * Retrieve shippingrates for quote and create a list
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @return array
     */
    public function getShippingRatesList(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote)
    {

        $groupedShippingRates = $this->getGroupedAllShippingRates($quote);

        // Build list
        $shippingList = array();
        $itemCount = 0;
        foreach ($groupedShippingRates as $shippingRates){
            $carrierTitle = $shippingRates[0]->getData('carrier_title');
            $itemCount++;
            foreach ($shippingRates as $shiprate) {
                $shippingList[$carrierTitle][$itemCount]['code'] = $shiprate->getData('code');
                $shippingList[$carrierTitle][$itemCount]['method_list'] = $shiprate->getData('method_title');
                $shippingList[$carrierTitle][$itemCount]['price'] = $shiprate->getData('price');
                $itemCount++;
            }
        }

        return array('itemCount' => $itemCount, 'shippingList' => $shippingList);
    }

    /**
     * Create a shipping method list to be used
     * for a dropdown list
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @return array
     */
    public function buildOptions(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote)
    {

        // Get available shipping rates
        $ratesList = $this->getShippingRatesList($quote);

        $b2qrate = ($quote->getBase2QuoteRate()) ? $quote->getBase2QuoteRate() : 1;

        // Create option data
        $optionList = array();
        $option = array();
        if (count($ratesList['shippingList']) > 0) {
            foreach ($ratesList['shippingList'] as $carrier) {
                foreach ($carrier as $rate) {
                    if (isset($rate['code']) && isset($rate['method_list']) && isset($rate['price'])) {
                        $option['value'] = $rate['code'];
                        $option['option'] = '<span>' . ucwords($rate['method_list']) . ' - <br />' . $quote->formatPrice($rate['price'] * $b2qrate) . '<span>';

                        $optionList[] = $option;
                    } else {
                        if (isset($rate['code']) && isset($rate['price'])) {
                            $ratePieces = explode("_", $rate['code']);

                            if(isset($ratePieces[1])){
                                $title = $ratePieces[1];
                            } else {
                                if (!$title = Mage::getStoreConfig("carriers/" . $ratePieces[0] . "/title")) {
                                    $title = $rate['code'];
                                }
                            }

                            $option['value'] = $rate['code'];
                            $option['option'] = '<span>' . ucwords($title) . ' - <br />' . $quote->formatPrice($rate['price'] * $b2qrate) . '<span>';

                            $optionList[] = $option;
                        }
                    }
                }
            }
            return $optionList;
        }

        return false;
    }

    /**
     * Function that gets the shipping method from quoteshippingrate, based on quote id and shipping code
     *
     *
     * @param string / integer $quoteAddressId
     * @param string / integer $shippingCode
     * @return boolean / object         // Ophirah_Qquoteadv_Model_Quoteshippingrate
     */
    public function getShippingMethodByCode($quoteAddressId = null, $shippingCode = null)
    {
        $return = false;

        if ($quoteAddressId == null || $shippingCode == null) {
            return $return;
        }

        $collection = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
            ->addFieldToFilter('address_id', array('eq' => $quoteAddressId))
            ->addFieldToFilter('code', array('eq' => $shippingCode));

        $collection->getSelect()->limit(1);

        foreach ($collection as $shipmethod) {
            $return = $shipmethod;
        }

        return $return;
    }
}
