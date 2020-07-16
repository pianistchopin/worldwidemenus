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
 * @package     RequestNotification
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_RequestNotification_Model_Observer
 */
class Ophirah_RequestNotification_Model_Observer
{
    const XML_PATH_QQUOTEADV_REQUEST_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/request';

    /**
     * Function that sends the notification email to the salesrep on a new quote request
     *
     * @param $observer
     */
    public function sendQuoteRequestNotification($observer)
    {
        $qquoteadvQuote = $observer->getQuote();
        $emailAddresses = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_quote_request_notification_to', $qquoteadvQuote->getStoreId());
        if (!empty($emailAddresses)) {
            $emailAddresses = array_filter(explode(';', $emailAddresses));

            $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel($qquoteadvQuote->getStoreId());

            $disabledEmail = Ophirah_Qquoteadv_Model_System_Config_Source_Email_Templatedisable::VALUE_DISABLED_EMAIL;
            $quoteadv_param = Mage::getStoreConfig('qquoteadv_quote_emails/templates/quote_request_notification', $qquoteadvQuote->getStoreId());

            if ($quoteadv_param != $disabledEmail && count($emailAddresses) > 0) {

                //Vars into email templates
                $vars = array(
                    'quoteUrl' => Mage::helper("adminhtml")->getUrl("adminhtml/redirect/quoteEdit", array('id' => $qquoteadvQuote->getId())),
                    'quote' => Mage::getModel('qquoteadv/qqadvcustomer')->load($qquoteadvQuote->getId()),
                    'customer' => Mage::getModel('customer/customer')->load($qquoteadvQuote->getCustomerId()),
                    'quoteId' => $qquoteadvQuote->getId()
                );


                if ($quoteadv_param) {
                    $templateId = $quoteadv_param;
                } else {
                    $templateId = self::XML_PATH_QQUOTEADV_REQUEST_EMAIL_TEMPLATE;
                }
                if (is_numeric($templateId)) {
                    $template->load($templateId);
                } else {
                    $template->loadDefault($templateId);
                }

                $sender = $qquoteadvQuote->getEmailSenderInfo();
                $template->setSenderName($sender['name']);
                $template->setSenderEmail($sender['email']);

                if ((bool)Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc', $qquoteadvQuote->getStoreId())
                    && Mage::helper('qquoteadv/licensechecks')->isAllowedSalesBcc()) {
                    $template->addBcc(Mage::getModel('admin/user')->load($qquoteadvQuote->getUserId())->getEmail());
                }

                //getProcessedTemplate is called inside  $template->send
                //$template->getProcessedTemplate($vars);

                Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $template));
                $res = $template->send($emailAddresses, null, $vars);
                Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $template, 'result' => $res));
                if (empty($res)) {
                    $message = Mage::helper('Ophirah_RequestNotification')->__("Qquote request email notification was't sent quote #%s", $qquoteadvQuote->getId());
                    Mage::log('Exception: RequestNotification: ' . $message, null, 'c2q_exception.log', true);
                }
            }
        }
    }
}
