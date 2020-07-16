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
 * Class Ophirah_Qquoteadv_Model_Shipping_Carrier_Qquoteshiprate
 */
class Ophirah_Qquoteadv_Model_Shipping_Carrier_Qquoteshiprate extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * @var string
     */
    protected $_code = 'qquoteshiprate';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * Function that collects the shipping rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getFreeShipping() && !$item->getProduct()->isVirtual()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        $this->setFreeBoxes($freeBoxes);

        $quoteId = Mage::getSingleton('core/session')->proposal_quote_id;

        if($quoteId == null){
            //probably admin mode
            $allItems = $request->getAllItems();
            if(isset($allItems[0]) && is_object($allItems[0])){
                $quoteId = $allItems[0]->getQuoteId();
                $quote = $allItems[0]->getQuote();
            }
        } else {
            $quote =  Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
        }

        if (($quoteId && Mage::getSingleton('core/session')->proposal_showquoteship === true) || ($quoteId && Mage::app()->getRequest()->getControllerName() != "cart")) {
            $price = Mage::app()->getHelper('qquoteadv')->getQquoteBaseShipPriceById($quoteId);
            $result = Mage::getModel('shipping/rate_result');
            $type = Mage::app()->getHelper('qquoteadv')->getShipTypeByQuote($quoteId);

            if($type != 'O' && $type != 'I'){
                //check if type is quoteadv and overwrite
                if(isset($quote) && is_object($quote)){
                    $shippingType = $quote->getShippingType();

                    //it can be I or O from this point, but that us unlikely
                    if ($shippingType == "I" || $shippingType == "O") {
                        //Cart2Quote shippingrate
                    } else {
                        if (is_integer((int)$shippingType) && (int)$shippingType != 1 && $price > -1) {
                            //Check of quoteadv shipping method
                            $rateData = Mage::getModel('qquoteadv/quoteshippingrate')->load($shippingType);
                            if ($rateData) {
                                if($rateData->getCode() == "qquoteshiprate_qquoteshiprate"){
                                    //overwrite type
                                    $type = 'O';
                                }
                            }
                        } else {
                            //other shipping type is set, could be this type could be an other, don't change anything.
                            //potentially this could be an issue on the first quote.
                            $type = 'O';

                            //do some awesomeness to get the correct rate!
                            $shippingCode = $quote->getShippingCode();
                            if(empty($shippingCode)){
                                $shippingCode = $quote->getShippingMethod();
                            }

                            if($shippingCode == "qquoteshiprate_qquoteshiprate"){
                                $shippingAddress = $quote->getShippingAddress();
                                $shippingAddressId = $shippingAddress->getAddressId();

                                //Mage::getModel('qquoteadv/quoteshippingrate')->
                                $collection = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
                                    ->addFieldToFilter('address_id', $shippingAddressId)
                                    ->addFieldToFilter('code', $shippingCode);
                                $shippingRate = $collection->getFirstItem();

                                if(isset($shippingRate) && !empty($shippingRate)){
                                    $price = $shippingRate->getPrice();
                                }
                            }
                        }
                    }
                }
            }

            if ($type == 'O') { // per order
                $shippingPrice = $price;
            } elseif ($type == 'I') { // per item
                $shippingPrice = ($request->getPackageQty() * $price) - ($this->getFreeBoxes() * $price);
            } else {
                $shippingPrice = (float)0;
            }

            $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);
            if ($shippingPrice !== false) {
                $method = Mage::getModel('shipping/rate_result_method');
                $method->setCarrier('qquoteshiprate');
                $method->setCarrierTitle($this->getConfigData('title'));
                $method->setMethod('qquoteshiprate');

                if ($type == 'I'){
                    $method->setMethodTitle('Price per Item');
                } else {
                    $method->setMethodTitle($this->getConfigData('name'));
                }

                //disable free shipping check, other carriers don't have that either, if you wish to use it, set it by hand.
//                if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
//                    $shippingPrice = '0.00';
//                }

                if($shippingPrice == -1){
                    $shippingPrice = 0;
                }

                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);

                $result->append($method);
            }

            return $result;
        }

        return false;
    }

    /**
     * Returns the qquoteshiprate as allowed shipping method
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('qquoteshiprate' => $this->getConfigData('name'));
    }
}
