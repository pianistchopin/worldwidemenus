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
 * Class Ophirah_Qquoteadv_Model_Customer_Customer
 */
class Ophirah_Qquoteadv_Model_Customer_Customer extends Mage_Customer_Model_Customer
{
    /**
     * Send email with new account specific information
     *
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @return $this
     * @throws Exception
     */
    public function sendNewQuoteAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
    {
        $quoteadv_new_account = Mage::getStoreConfig('qquoteadv_quote_emails/templates/new_account', $storeId);
        if ($quoteadv_new_account != Ophirah_Qquoteadv_Model_System_Config_Source_Email_Templatedisable::VALUE_DISABLED_EMAIL) {
            $types = array(
                'registered' => self::XML_PATH_REGISTER_EMAIL_TEMPLATE, // welcome email, when confirmation is disabled
                'confirmed' => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE, // welcome email, when confirmation is enabled
                'confirmation' => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE, // email with confirmation link
                'registered_qquoteadv' => $quoteadv_new_account, // welcome email, when confirmation is disabled and account was created from qquoteadv
            );
            if (!isset($types[$type])) {
                throw new Exception(Mage::helper('customer')->__('Wrong transactional account email type.'));
            }

            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            if (!$storeId) {
                $storeId = $this->_getWebsiteStoreId($this->getSendemailStoreId());
            }

            /* @var $quote Ophirah_Qquoteadv_Model_Qqadvcustomer */
            $session = $this->getCustomerSession();
            if ($session && ($quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($session->getQuoteadvId())) && $quote->getId()) {
                $sender = $quote->getEmailSenderInfo();
            } else {
                $sender = Mage::getStoreConfig(self::XML_PATH_REGISTER_EMAIL_IDENTITY);
            }

            if ($type == 'registered_qquoteadv') {
                $templateEmail = $types[$type];
            } else {
                $templateEmail = Mage::getStoreConfig($types[$type], $storeId);
            }

            /* @var $template Mage_Core_Model_Email_Template */
            $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel($storeId);

            $template->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
            $template->sendTransactional(
                $templateEmail,
                $sender,
                $this->getEmail(),
                $this->getName(),
                array('customer' => $this, 'back_url' => $backUrl));

            $translate->setTranslateInline(true);
        }

        return $this;
    }

    /**
     * Send email with new account specific information
     * Legacy function! (since 6.1.2)
     *
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param null $password
     * @return $this
     * @throws Exception
     */
    public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0', $password = null)
    {
        return $this->sendNewQuoteAccountEmail($type, $backUrl, $storeId);
    }
}
