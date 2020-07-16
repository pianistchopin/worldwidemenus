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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Crmaddon_Model_Observer_Status
 */
class Ophirah_Crmaddon_Model_Observer_Status {
    /**
     * Changes the action status based on the reply
     * @param $observer
     */
    public function setReadStatus($observer){
        $crmAddonMessagesModel =$observer->getEvent()->getCrmAddonMessagesModel();
        $crmAddonMessagesModel = Mage::getModel('crmaddon/crmaddonmessages')->load($crmAddonMessagesModel->getMessageId());
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($crmAddonMessagesModel->getQuoteId());
        if(isset($quote) && $quote->getId() && $crmAddonMessagesModel->getSendFromFrontend() == 0) {
            $newStatus = $this->getNewStatus($quote->getStatus());
        }else{
            $newStatus = $this->getNewStatus($quote->getStatus(), true);
        }
        if($newStatus > Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN){
            $quote->setStatus($newStatus);
            try{
                $quote->save();
            }catch (Exception $e){
                Mage::log('Exception: CRMAddon: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
    }

    /**
     * Filter to get a new status for the quote
     * @param $status
     * @param bool $frontend
     * @return int
     */
    private function getNewStatus($status, $frontend = false){
        switch($status){
            case Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN:
            case Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN_ACTION_OWNER:
            case Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN_ACTION_CUSTOMER:
                if($frontend){
                    return Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN_ACTION_OWNER;
                }else{
                    return Ophirah_Qquoteadv_Model_Status::STATUS_BEGIN_ACTION_CUSTOMER;
                }
            break;
            case Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST:
            case Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST_ACTION_OWNER:
            case Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST_ACTION_CUSTOMER:
                if($frontend){
                    return Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST_ACTION_OWNER;
                }else{
                    return Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST_ACTION_CUSTOMER;
                }
            break;
            case Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL:
            case Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_ACTION_OWNER:
            case Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_ACTION_CUSTOMER:
            case Ophirah_Qquoteadv_Model_Status::STATUS_AUTO_PROPOSAL:
                if($frontend){
                    return Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_ACTION_OWNER;
                }else{
                    return Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_ACTION_CUSTOMER;
                }
            break;
            default:
                return -1;
        }
    }
}
