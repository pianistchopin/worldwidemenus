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
 * Class Ophirah_Crmaddon_Model_Crmaddontemplates
 */
class Ophirah_Crmaddon_Model_Crmaddontemplates extends Mage_Core_Model_Abstract
{
    /**
     * Construct function
     */
    protected function _construct()
    {
        $this->_init('crmaddon/crmaddontemplates');
    }

    /**
     * Replace var data in message template with data
     * @param   String // $vars with variable data
     * @return  String      // String with template body in html
     */
    public function getBodyTemplate($vars)
    {

        // getting vars
        $customerName = $vars['customer']['name'];
        $senderName = $vars['sender']['name'];
        $message = (isset($vars['template'])) ? $vars['template'] : '';
        $qquote = $vars['qquote'];

        //get vars for template
        $admin = Mage::getModel('admin/user')->load($qquote->getUserId());
        $adminName = $admin->getFirstname() . ' ' . $admin->getLastname();
        $remark = Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/qquoteadv_remark', $qquote->getStoreId());
        $sender = Mage::getModel('qquoteadv/qqadvcustomer')->load($qquote->getId())->getEmailSenderInfo();

        //set vars for template
        $vars = array(
            'quote' => $this->_quoteadv,
            'customer' => Mage::getModel('customer/customer')->load($qquote->getCustomerId()),
            'quoteId' => $qquote->getId(),
            'storeId' => $qquote->getStoreId(),
            'adminname' => $adminName,
            'adminphone' => $admin->getTelephone(),
            'remark' => $remark,
            'link' => Mage::getUrl("qquoteadv/view/view/", array(
                    'id' => $qquote->getId(),
                    '_store' => $qquote->getStoreId()
            )),
            'sender' => $sender,
            'CRMcustomername' => $customerName,
            'CRMsendername' => $senderName
        );

        // replace text using Magento
        $replacedMessages = Mage::helper('crmaddon')->getEmailTemplateModel($qquote->getStoreId());
        $replacedMessages->setTemplateText($message);
        $message = $replacedMessages->getProcessedTemplate($vars);

        return $message;
    }

    /**
     * Retrieve template data from database
     *
     * @param   int // Template Id to load
     * @return  array()     // Template data
     */
    public function getCrmbodyTemplate($templateId)
    {
        $template = Mage::getModel('crmaddon/crmaddontemplates')->load($templateId);

        return $template->getData();
    }

}
