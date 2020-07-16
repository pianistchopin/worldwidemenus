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
 * Class Ophirah_Qquoteadv_Model_Core_Email_Templatemandrill
 */
class Ophirah_Qquoteadv_Model_Core_Email_Templatemandrill extends Ebizmarts_Mandrill_Model_Email_Template
{
    //    protected $_bcc = array();
    protected $_mail = null;
    public $storeId = null;

    /**
     * Overwrite the store id
     * Otherwise email in the backend are not rendered corectly
     *
     * @param null $storeId
     */
    public function setStoreId($storeId = null){
        if($storeId == null){
            $storeId = Mage::app()->getStore()->getId();
        }
        $this->storeId = $storeId;
    }

    /**
     * @param array|string $email
     * @param null $name
     * @param array $variables
     * @return bool
     */
    public function send($email, $name = null, array $variables = array())
    {
        $storeId = $this->storeId;
        if (!Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE, $storeId)) {
            return parent::send($email, $name, $variables);
        }
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }
        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        // Get message
        $this->setUseAbsoluteLinks(true);
        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);
        $message = $this->getProcessedTemplate($variables, true);
        $email = array('subject' => $this->getProcessedTemplateSubject($variables), 'to' => array());
        $mail = $this->getMail();
        $emailCount = count($emails);

        for ($i = 0; $i < $emailCount; $i++) {
            if (isset($names[$i])) {
                $email['to'][] = array(
                    'email' => $emails[$i],
                    'name'  => $names[$i]
                );
            } else {
                $email['to'][] = array(
                    'email' => $emails[$i],
                    'name'  => ''
                );
            }
        }

        foreach ($mail->getBcc() as $bcc) {
            $email['to'][] = array(
                'email' => $bcc,
                'type'  => 'bcc'
            );
        }

        $email['from_name'] = $this->getSenderName();
        $email['from_email'] = $this->getSenderEmail();
        $email['headers'] = $mail->getHeaders();
        if (isset($variables['tags']) && count($variables['tags'])) {
            $email ['tags'] = $variables['tags'];
        }

        if (isset($variables['tags']) && count($variables['tags'])) {
            $email ['tags'] = $variables['tags'];
        } else {
            $templateId = (string)$this->getId();
            $templates = parent::getDefaultTemplates();
            if (isset($templates[$templateId]) && isset($templates[$templateId]['label'])) {
                $email ['tags'] = array(substr($templates[$templateId]['label'], 0, 50));
            } else {
                if ($this->getTemplateCode()) {
                    $email ['tags'] = array(substr($this->getTemplateCode(), 0, 50));
                } else {
                    if ($templateId) {
                        $email ['tags'] = array(substr($templateId, 0, 50));
                    } else {
                        $email['tags'] = array('default_tag');
                    }
                }
            }
        }

        $att = $mail->getAttachments();
        if ($att) {
            $email['attachments'] = $att;
        }

        if ($this->isPlain()) {
            $email['text'] = $message;
        } else {
            $email['html'] = $message;
        }

        try {
            $mail->messages->send($email);
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return true;
    }

    /**
     * @return Mandrill_Message|Zend_Mail
     */
    public function getMail()
    {
        $storeId = $this->storeId;
        if (!Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE, $storeId)) {
            return parent::getMail();
        }
        if ($this->_mail) {
            return $this->_mail;
        } else {
            $storeId = $this->storeId;
            Mage::log("store: $storeId API: " . Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::APIKEY, $storeId));
            $this->_mail = new Ophirah_Qquoteadv_Model_Core_Email_Mandrillmessage(Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::APIKEY, $storeId));
            return $this->_mail;
        }
    }
}
