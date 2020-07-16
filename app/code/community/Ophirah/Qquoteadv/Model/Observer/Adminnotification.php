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
 * Class Ophirah_Qquoteadv_Model_Observer_Adminnotification
 */
class Ophirah_Qquoteadv_Model_Observer_Adminnotification
{
    protected $_user;

    /**
     * Sets the notification message
     * @param $observer
     */
    public function notify ($observer){
        $user = $observer->getEvent()->getUser();
        $this->setUser($user);

        if($this->getTotalQuoteCountMessage() || $this->getNewQuoteCountMessage()){
            $message = $this->getNotificationMessage();
            Mage::getSingleton('core/session')->addNotice($message);
        }
    }

    /**
     * Gets the amount of personal quotes
     * @return mixed
     */
    public function getPersonalQuotes(){
        $personalQuotes = $this->getQuoteCollection()
            ->addFieldToFilter('status',    array('eq' => Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST))
            ->addFieldToFilter('is_quote',  array('eq' => 1))
            ->addFieldToFilter('user_id',   array('eq' => $this->getUser()->getId()));
        return $personalQuotes;
    }

    /**
     * Sets the personal quote text
     * @return string
     */
    public function getPersonalQuoteCountMessage()
    {
        $personalMessage = '';
        $personalQuoteCollection = $this->getPersonalQuotes();
        $personalQuotesCount = $personalQuoteCollection->getSize();

        if ($personalQuotesCount > 1) {
            $link = Mage::helper('adminhtml')->getUrl('adminhtml/qquoteadv');
            $personalMessage = Mage::helper('qquoteadv')->__(
                "You have <a href='%s'> %s unanswered</a> quotation requests that require your action.",
                $link,
                $personalQuotesCount
            );
        } elseif ($personalQuotesCount == 1) {
            $link = Mage::helper('adminhtml')->getUrl(
                'adminhtml/qquoteadv/edit',
                array('id' => $personalQuoteCollection->getFirstItem()->getId())
            );
            $personalMessage = Mage::helper('qquoteadv')->__(
                "You have <a href='%s'> %s unanswered</a> quote request that require your action.",
                $link,
                $personalQuotesCount
            );
        }

        return $personalMessage;
    }

    /**
     * Gets unanswered quotes
     * @return mixed
     */
    public function getUnansweredQuotes(){
        $unansweredQuotes = $this->getQuoteCollection()
            ->addFieldToFilter('status', array('eq' => Ophirah_Qquoteadv_Model_Status::STATUS_REQUEST));
        return $unansweredQuotes;
    }

    /**
     * Sets the text of unanswered quotes
     * @return string
     */
    public function getTotalQuoteCountMessage()
    {
        $totalMessage = '';
        $unansweredQuoteCollection = $this->getUnansweredQuotes();
        $unansweredQuoteCount = $unansweredQuoteCollection->getSize();

        if ($unansweredQuoteCount > 1) {
            $link = Mage::helper('adminhtml')->getUrl('adminhtml/qquoteadv');
            $totalMessage = Mage::helper('qquoteadv')->__(
                "(<a href='%s'>%s unanswered requests</a> in total).",
                $link,
                $unansweredQuoteCount
            );
        } elseif ($unansweredQuoteCount == 1) {
            $link = Mage::helper('adminhtml')->getUrl(
                'adminhtml/qquoteadv/edit',
                array('id' => $unansweredQuoteCollection->getFirstItem()->getId())
            );
            $totalMessage = Mage::helper('qquoteadv')->__(
                "(<a href='%s'>%s unanswered request</a> in total).",
                $link,
                $unansweredQuoteCount
            );
        }

        return $totalMessage;
    }

    /**
     * Gets the new quotes since last login
     * @return mixed
     */
    public function getNewQuotes(){
        $fromDate = date('Y-m-d H:i:s', strtotime($this->getUser()->getLogdate()));
        $toDate = now();

        $newQuotes = $this->getQuoteCollection()
            ->addFieldToFilter('created_at',    array('from' => $fromDate, 'to' => $toDate))
            ->addFieldToFilter('is_quote',      array('eq' => 1));
        return $newQuotes;
    }

    /**
     * Sets the text of new quotes
     * @return string
     */
    public function getNewQuoteCountMessage()
    {
        $newMessage = '';
        $newQuoteCollection = $this->getNewQuotes();
        $newQuotesCount = $newQuoteCollection->getSize();

        if ($newQuotesCount > 1) {
            $link = Mage::helper('adminhtml')->getUrl('adminhtml/qquoteadv');
            $newMessage = Mage::helper('qquoteadv')->__(
                "There are <a href='%s'>%s new</a> requests since your last login.",
                $link,
                $newQuotesCount
            );
        } elseif ($newQuotesCount == 1) {
            $link = Mage::helper('adminhtml')->getUrl(
                'adminhtml/qquoteadv/edit',
                array('id' => $newQuoteCollection->getFirstItem()->getId())
            );
            $newMessage = Mage::helper('qquoteadv')->__(
                "There is <a href='%s'>%s new</a> request since your last login.",
                $link,
                $newQuotesCount
            );
        }

        return $newMessage;
    }

    /**
     * Generates the Cart2Quote notification text
     * @return string
     */
    public function getNotificationMessage(){
        $message = Mage::helper('qquoteadv')->__("Cart2Quote notice:")." ";
        $message .= $this->getPersonalQuoteCountMessage()." ";
        $message .= $this->getNewQuoteCountMessage()." ";
        $message .= $this->getTotalQuoteCountMessage();
        return $message;
    }

    /**
     * @return object
     */
    public function getQuoteCollection(){
        return Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
    }

    /**
     * @param $user
     */
    public function setUser($user){
        $this->_user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser(){
        return $this->_user;
    }
}
