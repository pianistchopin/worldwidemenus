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
 * Class Ophirah_Qquoteadv_Model_Email_Autoproposal
 * @todo extract generic email functions to an abstract email object and extend the email objects to this class.
 */
class Ophirah_Qquoteadv_Model_Email_Autoproposal extends Mage_Core_Model_Abstract
{
    CONST XML_PATH_QQUOTEADV_REQUEST_PROPOSAL_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal';
    CONST CHECK_TYPE_AND = 1;
    CONST CHECK_TYPE_OR  = 0;

    /** @var int */
    protected $_quoteId;

    /** @var Ophirah_Qquoteadv_Model_Qqadvcustomer */
    protected $_quote;

    /** @var Mage_Core_Model_Email_Template */
    protected $_template;

    /** @var array */
    protected $_vars = array();

    /** @var int */
    protected $_maxAmount = 0;

    /** @var int */
    protected $_currentAmount = 0;

    /** @var int */
    protected $_maxQty = 0;

    /** @var int */
    protected $_currentQty = 0;

    /** @var int */
    protected $_storeId;

    /**
     * Check type selector for auto proposal conditions
     * @var string
     */
    protected $_checkType = self::CHECK_TYPE_AND;

    /**
     * Depends on the (overwritten) email class.
     * @var bool|exception|string
     */
    protected $_emailResult;

    /**
     * Checks if auto proposal is allowed
     * Optional includes the limits
     *
     * @param bool $includeLimits
     * @param bool $forceAutoProposal
     * @return bool
     */
    public function isAllowed($includeLimits = true, $forceAutoProposal = false)
    {
        if ($forceAutoProposal) {
            return true;
        }

        $allowed = $this->isConfigAllowed() && $this->allowed();
        if ($includeLimits) {
            $allowed = $this->getAllowedByLimits($allowed);
        }
        return $allowed;
    }

    /**
     * Send email to client to informing about the quote proposition
     * @param bool $forceAutoProposal
     * @return boolean|string
     */
    public function sendEmail($forceAutoProposal = false)
    {
        $res = false;
        if ($this->isAllowed(true, $forceAutoProposal)) {
            Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $this->_template));
            $email = $this->getQuote()->getEmail();
            $name = $this->getCustomer()->getName();
            $vars = $this->getVars();
            Mage::helper('qquoteadv/logging')->sentAnonymousData('auto-proposal', 'f', $this->getQuoteId());

            $res = $this->_template->send($email, $name, $vars);
            Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $this->_template, 'result' => $res));
        }
        $this->_emailResult = $res;
        return $this;
    }

    /**
     * Prepares the max qty and current qty fields.
     * @return $this
     * @throws Exception
     */
    protected function prepareMaxQty()
    {
        $this->_currentQty = $this->getQuote()->getItemsQty();

        if ($this->getUseMaxQty()) {
            $maxQty = $this->getMaxQty();
            if (!is_null($maxQty) && is_double($maxQty)) {
                $this->_maxQty = $maxQty;
            } else {
                throw new Exception($this->helper()->__('The maximal auto proposal qty is not an number.'));
            }
        }

        return $this;
    }

    /**
     * Prepares the max amount and current amount fields.
     * @return $this
     * @throws Exception
     */
    protected function prepareMaxAmount()
    {
        $this->_currentAmount = $this->getQuote()->getGrandTotal();

        if ($this->getUseMaxAmount()) {
            $maxAmount = $this->getMaxAmount();
            if (!is_null($maxAmount) && is_double($maxAmount)) {
                $this->_maxAmount = $maxAmount;
            } else {
                throw new Exception($this->helper()->__('The maximal auto proposal amount is not an number.'));
            }
        }
        return $this;
    }

    /**
     * Checks if the quote exceeds the max qty of the auto proposal setting.
     * @return bool
     */
    public function underMaxQty()
    {
        $underMaxQty = true;
        if ($this->getUseMaxQty()) {
            if ($this->_maxQty < $this->_currentQty) {
                $underMaxQty = false;
            }
        }
        return $underMaxQty;
    }

    /**
     * Checks if the quote exceeds the max amount of the auto proposal setting.
     * @return bool
     */
    public function underMaxAmount()
    {
        $underMaxAmount = true;
        if ($this->getUseMaxAmount()) {
            if ($this->_maxAmount < $this->_currentAmount) {
                $underMaxAmount = false;
            }
        }
        return $underMaxAmount;
    }

    /**
     * Prepares the email variables.
     * @return $this
     */
    protected function prepareEmail()
    {
        $this->loadTemplate()
            ->setEmailVar('attach_pdf', false)
            ->setEmailVar('attach_doc', false)
            ->setEmailVar('quote', $this->getQuote())
            ->setEmailVar('store', $this->getStore())
            ->setEmailVar('customer', $this->getCustomer())
            ->setEmailVar('link', Mage::getUrl("qquoteadv/view/view/", array('id' => $this->getQuote())))
            ->setDoc()
            ->setPdfAttachment()
            ->addBcc();

        $this->_template
            ->setSenderName($this->getSenderName())
            ->setSenderEmail($this->getSenderEmail())
            ->setData(
                'c2qParams',
                array(
                    'email' => $this->getQuote()->getEmail(),
                    'name' => $this->getCustomer()->getName()
                )
            );

        return $this;
    }

    /**
     * Adds the BCC addresses to the email.
     * @return $this
     */
    protected function addBcc()
    {
        $bcc = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/bcc', $this->getStoreId());
        if ($bcc) {
            $bccData = explode(";", $bcc); // todo check for valid emails.
            $this->_template->addBcc($bccData);
        }

        if ((bool)Mage::getStoreConfig(
                'qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc',
                $this->getStoreId()
            )
            && Mage::helper('qquoteadv/licensechecks')->isAllowedSalesBcc()
        ) {
            $this->_template->addBcc(
                Mage::getModel('admin/user')
                    ->load($this->getQuote()->getUserId())
                    ->getEmail()
            );
        }

        return $this;
    }

    /**
     * Retrieves the store id from the quote.
     * If none is set, the fallback will be current store id.
     * @return int
     */
    protected function getStoreId()
    {
        if (!isset($this->_storeId)) {
            $storeId = $this->getQuote()->getStoreId();
            if (!$storeId) {
                $storeId = Mage::app()->getStore()->getStoreId();
            }
            $this->_storeId = $storeId;
        }

        return $this->_storeId;
    }

    /**
     * This function sets the PDF to the email.
     * @return $this
     */
    protected function setPdfAttachment()
    {
        //Create pdf to attach to email
        if (Mage::getStoreConfig('qquoteadv_quote_emails/attachments/pdf', $this->getStoreId())) {
            $pdf = Mage::getModel('qquoteadv/pdf_qquote')->getPdf($this->getQuote());
            $realQuoteadvId = $this->getQuote()->getIncrementId() ? $this->getQuote()->getIncrementId() : $this->getQuote()->getId();
            try {
                $file = $pdf->render();
                $name = Mage::helper('qquoteadv')->__('Price_proposal_%s', $realQuoteadvId);
                $this->_template->getMail()->createAttachment($file, 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $name . '.pdf');
                $this->setEmailVar('attach_pdf', true);
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
        return $this;
    }

    /**
     * This function sets a general document on the email.
     * @return $this
     */
    protected function setDoc()
    {
        $doc = $this->getDocPath();
        if ($doc) {
            $pathDoc = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'quoteadv' . DS . $doc;
            try {
                $file = file_get_contents($pathDoc);
                $info = pathinfo($pathDoc);
                $basename = $info['basename'];
                $this->_template->getMail()->createAttachment($file, '', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $basename);
                $vars['attach_doc'] = true;
                $this->setEmailVar('attach_doc', true);
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
        return $this;
    }

    /**
     * Loads the template to the class variable _template
     * @see _template
     * @return $this
     */
    protected function loadTemplate()
    {
        $this->_template = Mage::helper('qquoteadv/email')->getEmailTemplateModel($this->getStoreId());
        $templateId = $this->getTemplateId();
        if (is_numeric($templateId)) {
            $this->_template->load($templateId);
        } else {
            $this->_template->loadDefault($templateId);
        }
        return $this;
    }

    /**
     * Retrieves the template id for the email template.
     * @return mixed
     */
    protected function getTemplateId()
    {
        $quoteadv_param = Mage::getStoreConfig('qquoteadv_quote_emails/templates/proposal', $this->getStoreId());
        if ($quoteadv_param) {
            $templateId = $quoteadv_param;
            return $templateId;
        } else {
            $templateId = self::XML_PATH_QQUOTEADV_REQUEST_PROPOSAL_EMAIL_TEMPLATE;
            return $templateId;
        }
    }

    /**
     * Sets the quote on this object.
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @param $forceAutoProposal bool
     * @return $this
     */
    public function setQuote(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote, $forceAutoProposal = false)
    {
        $this->_quote = $quote;
        $this->prepareMaxQty();
        $this->prepareMaxAmount();

        if ($this->isAllowed(true, $forceAutoProposal)) {
            $this->prepareEmail();
        }
        return $this;
    }

    /**
     * Retrieves the quote.
     * @see setQuote
     * @return Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    protected function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Retrieves the quote id.
     * @see setQuote
     * @return int
     */
    protected function getQuoteId()
    {
        if (!isset($this->_quoteId)) {
            $quoteId = $this->getQuote()->getId();
            if (!$quoteId) {
                $quoteId = Mage::app()->getStore()->getId();
            }
            $this->_quoteId = $quoteId;
        }

        return $this->_quoteId;
    }

    /**
     * @param $quote
     * @param bool $forceAutoProposal
     * @return Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function setQuoteStatus($quote = null, $forceAutoProposal = false)
    {
        if (!isset($quote)) {
            $quote = $this->getQuote();
        }
        if ($this->isAllowed(true, $forceAutoProposal) && $quote instanceof Ophirah_Qquoteadv_Model_Qqadvcustomer) {
            $quote->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_AUTO_PROPOSAL);
        }
        return $quote;
    }

    /**
     * @param $type
     * @param $value
     * @return $this
     */
    public function setEmailVar($type, $value)
    {
        $this->_vars[$type] = $value;
        return $this;
    }

    /**
     * @param $allowed
     * @return bool
     */
    public function getAllowedByLimits($allowed)
    {
        if ($this->isCheckTypeAnd()) {
            $allowed = $allowed
                && $this->underMaxQty()
                && $this->underMaxAmount();
            return $allowed;
        } elseif ($this->isCheckTypeOr()) {
            $allowed = $allowed
                && ($this->underMaxQty() || $this->underMaxAmount());
            return $allowed;
        }
        return $allowed;
    }

    /**
     * @return string
     */
    public function getConditionCheckType(){
        return $this->_checkType;
    }

    /**
     * @return bool
     */
    public function isCheckTypeOr(){
        return $this->_checkType == self::CHECK_TYPE_OR;
    }

    /**
     * @return bool
     */
    public function isCheckTypeAnd(){
        return $this->_checkType == self::CHECK_TYPE_AND;
    }

    /**
     *  Add the general remark to the vars array.
     */
    protected function setRemark()
    {
        $remark = $this->getRemark();
        if ($remark) {
            $this->setEmailVar('remark', $remark);
        }
    }

    /**
     * Retrieves the customer that is set on the quote.
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return Mage::getModel('customer/customer')->load($this->getQuote()->getCustomerId());
    }

    /**
     * Retrieves the senders name.
     * @return string
     */
    protected function getSenderName()
    {
        $sender = $this->getSenderInfo();
        return $sender['name'];
    }

    /**
     * Retrieves information about the sender.
     * @return array
     */
    protected function getSenderInfo()
    {
        $sender = $this->getQuote()->getEmailSenderInfo();
        return $sender;
    }

    /**
     * Retrieves the senders email.
     * @return string
     */
    protected function getSenderEmail()
    {
        $sender = $this->getSenderInfo();
        return $sender['email'];
    }

    /**
     * Retrieves the vars for the email.
     * The vars are being set by the function setEmailVar
     * @see setEmailVar
     * @return array
     */
    protected function getVars()
    {
        return $this->_vars;
    }

    /**
     * Retrieves the qquoteadv helper.
     * @return Ophirah_Qquoteadv_Helper_Data
     */
    protected function helper()
    {
        return Mage::helper('qquoteadv');
    }

    /**
     * @return Mage_Core_Model_Store
     */
    protected function getStore()
    {
        return Mage::app()->getStore($this->getStoreId());
    }

    /**
     * Retrieves the path to the general email attachment from the config.
     * @return string
     */
    protected function getDocPath()
    {
        return Mage::getStoreConfig('qquoteadv_quote_emails/attachments/doc', $this->getStoreId());
    }

    /**
     * Retrieves the configuration setting if the max amount is used. (True if used, false if not)
     * @return bool
     */
    protected function getUseMaxAmount()
    {
        return (bool)Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/auto_proposal_depend_on_quote_total');
    }

    /**
     * Retrieves the max amount for proposal use in the config.
     * @return double
     */
    protected function getMaxAmount()
    {
        return (double)Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/auto_proposal_quote_total');
    }

    /**
     * Retrieves the general remark from the config.
     * @return string
     */
    protected function getRemark()
    {
        return Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/qquoteadv_remark', $this->getStoreId());
    }

    /**
     * Retrieves the configuration setting if the max qty is used. (True if used, false if not)
     * @return bool
     */
    protected function getUseMaxQty()
    {
        return (bool)Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/auto_proposal_depend_on_quote_qty');
    }

    /**
     * Retrieves the max qty for proposal use in the config.
     * @return double
     */
    protected function getMaxQty()
    {
        return (double)Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/auto_proposal_quote_qty');
    }

    /**
     * Retrieves the configuration setting if the auto proposal is used. (True if used, false if not)
     * @return bool
     */
    public function isConfigAllowed()
    {
        return (bool)Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/auto_proposal');
    }

    /**
     * Checks if auto proposal is allowed.
     */
    private function allowed()
    {
        return Mage::helper('qquoteadv/licensechecks')->isAllowAutoProposal($this->getQuote());
    }
}
