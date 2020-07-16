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
 * Class Ophirah_Qquoteadv_Model_Qquoteadv_Api
 */
class Ophirah_Qquoteadv_Model_Qquoteadv_Api extends Mage_Api_Model_Resource_Abstract
{
    CONST XML_PATH_QQUOTEADV_REQUEST_PROPOSAL_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal';

    //NOTE: for PDF quote proposal fields we have text limitation
    public $_limitComment = 400;

    /**
     * Field name in session for saving store id
     *
     * @var string
     */
    protected $_storeIdSessionField = 'store_id';

    /**
     * Retrieve list of quotations using filters
     *
     * @param array $filters
     * @return array
     */
    public function items($filters)
    {
        $_collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection();
        $_collection->addFieldToFilter('is_quote', 1);

        if (is_array($filters)) {
            try {
                foreach ($filters as $filter => $value) {
                    $_collection->addFieldToFilter("$filter", $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $data = $_collection->toArray();
        return $data['items'];
    }

    /**
     * Set quotation as exported
     *
     * @param array $params
     * int     $params['quote_id']
     * string  $params['value']
     * @return array|bool
     */
    public function setimported($params)
    {

        if (isset($params['quote_id']) && isset($params['value'])) {
            $quote_id = $params['quote_id'];
            $value = $params['value'];

            $_quote = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quote_id);
            if (!$_quote->getId()) {
                $this->_fault('quote_not_exists');
            }

            try {
                $_quote->setImported((bool)$value);
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quote));
                $_quote->save();
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quote));
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                $this->_fault('data_invalid', $e->getMessage());
            }

            return true;
        } else {
            $this->_fault('data_invalid', "QuoteId or Value didn't not received");
        }

        return null;
    }

    /**
     * Retrieve an quotation's information
     *
     * @param int $quote_id
     * @return array
     */
    public function info($quote_id)
    {
        $_quoteCollection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', (int)$quote_id);

        if ($_quoteCollection->getSize() > 0) {
            $response = $_quoteCollection->toArray();
            foreach ($response['items'] as $index => $row) {
                $key = $row['id'];
                $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                    ->addFieldToFilter('quote_id', (int)$quote_id)
                    ->addFieldToFilter('quoteadv_product_id', $key);

                if ($_collection->getSize() > 0) {
                    $requested = $_collection->toArray();

                    $row['primary_key'] = $row['id'];
                    $storeId = $row['store_id'];
                    $productId = $row['product_id'];

                    try {
                        $row['sku'] = Mage::getModel('catalog/product')
                            ->setStoreId($storeId)
                            ->load($productId)
                            ->getSku();
                    } catch (Exception $e) {
                        Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                        $this->_fault('data_invalid', $e->getMessage());
                    }

                    //#add attributes/options
                    foreach ($requested['items'] as $key => $request) {
                        $request['attribute'] = $row['attribute'];
                        $request['has_options'] = $row['has_options'];
                        $request['options'] = $row['options'];
                        $requested['items'][$key] = $request;
                    }
                    $row['data'] = $requested;

                    unset($row['id']);
                    unset($row['qty']);

                    $response['items'][$index] = $row;
                }
            }
            return $response;
        } else {
            $this->_fault('quote_not_exists', 'Quotation is not found');
        }

        return null;
    }

    /**
     * Retrieve list of quotation's states
     *
     * @return array
     */
    public function status_list()
    {
        return Mage::getSingleton('qquoteadv/status')->getOptionArray();
    }

    /** Add qty by requested item with owner proposal price
     *
     * @param array $params
     * $params = array(
     * 'quote_id'        =>int,
     * 'product_id'      =>int,
     * 'request_qty'     =>int,
     * 'owner_base_price'=>float,
     * 'original_price'  =>float,
     * 'quoteadv_product_id'=>int
     * );
     * @return array
     */
    public function add_qtybyitem($params)
    {
        $response = array(
            'success' => false
        );

        if (isset($params['quote_id'])
            && isset($params['product_id'])
            && isset($params['request_qty'])
            && isset($params['owner_base_price'])
            && isset($params['original_price'])
            && isset($params['quoteadv_product_id'])
        ) {

            $quote_id = $params['quote_id'];
            $key = $params['quoteadv_product_id'];
            $request_qty = $params ['request_qty'];

            $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                ->addFieldToFilter('quote_id', (int)$quote_id)
                ->addFieldToFilter('product_id', (int)$params['product_id'])
                ->addFieldToFilter('quoteadv_product_id', (int)$key);

            if ($_collection->getSize() > 0) {
                $_collection->clear();
                $_collection->addFieldToFilter('request_qty', $request_qty);
                $data = $_collection->getData();

                if (count($data) > 0) {
                    $this->_fault('dublicate_data', 'Duplicate qty value entered');
                }

                try {
                    if (isset($params['request_id'])) {
                        unset($params['request_id']);
                    }

                    Mage::getModel('qquoteadv/requestitem')->setData($params)->save();

                    $_qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quote_id);
                    $_qquoteadv->collectTotals();
                    $_qquoteadv->save();
                } catch (Exception $e) {
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                    $this->_fault('save_error', $e->getMessage());
                }

                $response = array(
                    'success' => true
                );
            } else {
                $this->_fault('not_exists', "Data by QuoteId / QuoteadvProductId / ProductId not found");
            }
        } else {
            $this->_fault('data_invalid', "Initial parameters didn't not received");
        }

        return $response;
    }

    /**
     * Send email proposal to client
     *
     * @param int $quoteId
     * @return array
     */
    public function send_proposal($quoteId)
    {
        if ($quoteId) {
            $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);
            if (!$_quoteadv->getId()) {
                $this->_fault('quote_not_exists');
            }

            //#send Proposal email
            $customerId = $_quoteadv->getCustomerId();
            if ($customerId) {

                $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                    ->addFieldToFilter('quote_id', (int)$quoteId);

                if ($_collection->getSize() > 0) {

                    $res = $this->_sendProposalEmail($quoteId);
                    if (empty($res)) {
                        $message = sprintf(
                            "Qquote proposal email was't sent to the client for quote #%s",
                            $_quoteadv->getId()
                        );
                        $this->_fault('data_invalid', $message);
                    } else {
                        try {
                            $_quoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL);
                            Mage::dispatchEvent(
                                'qquoteadv_qqadvcustomer_beforesafe_final',
                                array('quote' => $_quoteadv)
                            );
                            $_quoteadv->save();
                            Mage::dispatchEvent(
                                'qquoteadv_qqadvcustomer_aftersafe_final',
                                array('quote' => $_quoteadv)
                            );
                        } catch (Exception $e) {
                            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                            $this->_fault('save_error', $e->getMessage());
                        }

                        return true;
                    }
                } else {
                    $this->_fault('not_exists');
                }
            } else {
                $this->_fault('quote_not_exists');
            }
        } else {
            $this->_fault('data_invalid', "QuoteId parameter didn't received");
        }

        return false;
    }

    /**
     * Send email proposal to client
     *
     * @param int $quoteId
     * @return array
     */
    private function _sendProposalEmail($quoteId)
    {
        //Create an array of variables to assign to template
        $vars = array();

        /* @var $_quoteadv Ophirah_Qquoteadv_Model_Qqadvcustomer */
        $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);

        $vars['quote'] = $_quoteadv;
        $vars['store'] = Mage::app()->getStore($_quoteadv->getStoreId());

        $customer = Mage::getModel('customer/customer')->load($_quoteadv->getCustomerId());
        $vars['customer'] = $customer;
        $params['email'] = $customer->getEmail();
        $params['name'] = $customer->getName();

        $template = Mage::helper('qquoteadv/email')->getEmailTemplateModel($_quoteadv->getStoreId());

        $quoteadv_param = Mage::getStoreConfig('qquoteadv_quote_emails/templates/proposal', $_quoteadv->getStoreId());
        if ($quoteadv_param) {
            $templateId = $quoteadv_param;
        } else {
            $templateId = self::XML_PATH_QQUOTEADV_REQUEST_PROPOSAL_EMAIL_TEMPLATE;
        }

        if (is_numeric($templateId)) {
            $template->load($templateId);
        } else {
            $template->loadDefault($templateId);
        }

        $vars['attach_pdf'] = $vars['attach_doc'] = false;

        //Create pdf to attach to email

        if (Mage::getStoreConfig('qquoteadv_quote_emails/attachments/pdf', $_quoteadv->getStoreId())) {
            $pdf = Mage::getModel('qquoteadv/pdf_qquote')->getPdf($_quoteadv);
            $realQuoteadvId = $_quoteadv->getIncrementId() ? $_quoteadv->getIncrementId() : $_quoteadv->getId();
            try {
                $file = $pdf->render();
                $name = Mage::helper('qquoteadv')->__('Price_proposal_%s', $realQuoteadvId);
                $template->getMail()->createAttachment($file, 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $name . '.pdf');
                $vars['attach_pdf'] = true;
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
            }
        }

        $doc = Mage::getStoreConfig('qquoteadv_quote_emails/attachments/doc', $_quoteadv->getStoreId());
        if ($doc) {
            $pathDoc = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'quoteadv' . DS . $doc;
            try {
                $file = file_get_contents($pathDoc);

                $info = pathinfo($pathDoc);
                //$extension = $info['extension'];
                $basename = $info['basename'];
                $template->getMail()->createAttachment($file, '', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $basename);
                $vars['attach_doc'] = true;
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
            }
        }
        $remark = Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/qquoteadv_remark', $_quoteadv->getStoreId());
        if ($remark) {
            $vars['remark'] = $remark;
        }

        $vars['link'] = Mage::getUrl("qquoteadv/view/view/", array('id' => $quoteId));

        $sender = $_quoteadv->getEmailSenderInfo();
        $template->setSenderName($sender['name']);
        $template->setSenderEmail($sender['email']);

        $bcc = Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/bcc', $_quoteadv->getStoreId());
        if ($bcc) {
            $bccData = explode(";", $bcc);
            $template->addBcc($bccData);
        }

        if ((bool)Mage::getStoreConfig('qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc', $_quoteadv->getStoreId())
            && Mage::helper('qquoteadv/licensechecks')->isAllowedSalesBcc()) {
            $template->addBcc(Mage::getModel('admin/user')->load($_quoteadv->getUserId())->getEmail());
        }

        $template->setDesignConfig(array('store' => $_quoteadv->getStoreId()));

        /**
         * Opens the qquote_request.html, throws in the variable array
         * and returns the 'parsed' content that you can use as body of email
         */
        //emulate quote store for corret email design
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($_quoteadv->getStoreId());

        /*
         * getProcessedTemplate is called inside send()
         */
        $template->setData('c2qParams', $params);
        Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_before', array('template' => $template));
        $res = $template->send($params['email'], $params['name'], $vars);
        Mage::dispatchEvent('ophirah_qquoteadv_addSendMail_after', array('template' => $template, 'result' => $res));

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $res;
    }

    /**
     * Set shipping type
     *
     * @param array $params
     * float  $params['shipping_price']
     * string $params['shipping_type']
     * @return array
     */
    public function set_shipping($params)
    {
        if (!isset($params['quote_id']) or !isset($params['shipping_type']) or !isset($params['shipping_price'])) {
            $this->_fault('data_invalid', "QuoteId or ShippingType or ShippingPrice parameters didn't not received");
        }

        $quoteId = (int)$params['quote_id'];
        $type = (string)$params['shipping_type'];
        $price = (float)$params['shipping_price'];

        if (empty($type)) {
            $price = -1;
        } elseif (($type == "I" or $type == "O") && $price > 0) {
            //ok
        } else {
            $this->_fault('data_invalid');
        }

        $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);
        if (!$_quoteadv->getId()) {
            $this->_fault('quote_not_exists');
        }

        try {
            $_quoteadv->setShippingType($type);
            $_quoteadv->setShippingPrice($price);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
            $_quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
            $this->_fault('save_error', $e->getMessage());
        }

        $response = array(
            'success' => true
        );

        return $response;
    }

    /**
     * Send owner comment for proposal
     *
     * @param array $params
     * int    $params['quote_id']
     * string $params['comment']
     * @return array
     */
    public function set_proposal_comment($params)
    {
        if (!isset($params['quote_id']) or !isset($params['comment'])) {
            $this->_fault('data_invalid', "QuoteId or Comment parameters didn't not received");
        }

        $quoteId = $params['quote_id'];
        $comment = trim($params['comment']);
        $len = strlen($comment);
        if ($len > $this->_limitComment) {
            $msg = sprintf("Comment length overlimit %s characters", $len - $this->_limitComment);
            $this->_fault('data_invalid', $msg);
        }

        $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);
        if (!$_quoteadv->getId()) {
            $this->_fault('quote_not_exists');
        }

        try {
            $_quoteadv->setClientRequest($comment);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
            $_quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
            $this->_fault('save_error', $e->getMessage());
        }

        $response = array(
            'success' => true
        );

        return $response;
    }

    /**
     * Send comment for item
     *
     * @param array $params
     * int    $params['quoteadv_product_id']
     * string $params['comment']
     * @return array
     */
    public function set_item_comment($params)
    {
        if (!isset($params['quoteadv_product_id']) or !isset($params['comment'])) {
            $this->_fault('data_invalid', "QuoteadvProductId  or Comment parameters didn't not received");
        }

        $id = (int)$params['quoteadv_product_id'];
        $comment = trim($params['comment']);

        $len = strlen($comment);
        if ($len > $this->_limitComment) {
            $msg = sprintf("Comment length overlimit %s characters", $len - $this->_limitComment);
            $this->_fault('data_invalid', $msg);
        }

        $_quoteadv = Mage::getModel('qquoteadv/qqadvproduct')->load($id);
        if (!$_quoteadv->getId()) {
            $this->_fault('quote_not_exists');
        }

        try {
            $_quoteadv->setClientRequest($comment);
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
            $_quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
            $this->_fault('save_error', $e->getMessage());
        }

        $response = array(
            'success' => true
        );

        return $response;
    }

    /**
     * Delete requested qty
     *
     * @param array $params
     * int $params['request_id']
     * int $params['quote_id']
     * @return array
     */
    public function delete_requested_qty($params)
    {
        $response = array(
            'success' => false
        );

        if (!isset($params['request_id']) or !isset($params['quote_id'])) {
            $this->_fault('data_invalid', "RequestId or QuoteId parameters didn't not received");
        }

        $requestId = (int)$params['request_id'];
        $quoteId = (int)$params['quote_id'];

        $_quoteadv = Mage::getModel('qquoteadv/requestitem')->load($requestId);
        if (!$_quoteadv->getId()) {
            $this->_fault('data_invalid', "RequestId not exists");
        } elseif ($_quoteadv->getId() && $_quoteadv->getQuoteId() != $quoteId) {
            $this->_fault('data_invalid', "RequestId is wrong by QuoteId");
        }

        $itemData = $_quoteadv->getData();
        $id = $itemData['quoteadv_product_id'];
        $_itemsCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
            ->addFieldToFilter('quoteadv_product_id', $id)
            ->addFieldToFilter('quote_id', $quoteId);

        if ($_itemsCollection->getSize() > 1) {
            try {
                $_quoteadv->delete();

                /** @var \Ophirah_Qquoteadv_Model_Qqadvcustomer $quote */
                $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                $quote->collectTotals();
                $quote->save();

                $response = array(
                    'success' => true
                );
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                $this->_fault('delete_error', $e->getMessage());
            }

            $this->_updateQuoteStatus($quoteId);
        } else {
            $this->_fault('data_invalid', 'Minimum of one Qty is required');
        }

        return $response;
    }

    /**
     * Delete requested item
     *
     * @param array $params
     * int $params['quote_id']
     * int $params['primary_key']
     * @return array
     */
    public function delete_requested_item($params)
    {
        $response = array(
            'success' => false
        );

        if (!isset($params['primary_key']) or !isset($params['quote_id'])) {
            $this->_fault('data_invalid', "PrimaryKey or QuoteId didn't not received");
        }

        $id = (int)$params['primary_key'];
        $quoteId = (int)$params['quote_id'];

        $_quoteadv = Mage::getModel('qquoteadv/qqadvproduct')->load($id);
        if (!$_quoteadv->getId()) {
            $this->_fault('data_invalid', "Data by PrimaryKey not exists");
        } else {
            if ($_quoteadv->getData('quote_id') != $quoteId) {
                $this->_fault('data_invalid', 'PrimaryKey is wrong by QuoteId');
            }
        }

        $itemData = $_quoteadv->getData();
        if (count($itemData) > 0) {
            $_itemsCollection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection();
            $_itemsCollection->addFieldToFilter('quote_id', $quoteId);

            if ($_itemsCollection->getSize() > 1) {
                try {
                    $_quoteadv->delete();

                    $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                    $quote->collectTotals();
                    $quote->save();

                    $response = array(
                        'success' => true
                    );
                } catch (Exception $e) {
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                    $this->_fault('data_invalid', $e->getMessage());
                }
            } else {
                $this->_fault('data_invalid', 'Minimum of one Item is required');
            }
        }

        return $response;
    }

    /**
     * Modify requested item's qty
     *
     * @param array $params
     * int $params['request_id']
     * int $params['quote_id']
     * int $params['product_id']
     * int $params['request_qty']
     * float $params['owner_base_price']
     * float $params['original_price']
     * int $params['quoteadv_product_id']
     * @return array
     */
    public function modify_requested_qty($params)
    {
        $response = array(
            'success' => false
        );

        if (isset($params['request_id'])
            && isset($params['quote_id'])
            && isset($params['product_id'])
            && isset($params['request_qty'])
            && isset($params['owner_base_price'])
            && isset($params['original_price'])
            && isset($params['quoteadv_product_id'])
        ) {

            $quote_id = (int)$params['quote_id'];
            $key = (int)$params['quoteadv_product_id'];
            $request_qty = $params ['request_qty'];

            $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                ->addFieldToFilter('quote_id', $quote_id)
                ->addFieldToFilter('quoteadv_product_id', $key);

            if ($_collection->getSize() > 0) {
                $_collection->clear();
                $_collection->addFieldToFilter('request_qty', $request_qty);
                $data = $_collection->getData();
                if (count($data) > 0) {
                    $this->_fault('dublicate_data', 'Duplicate qty value entered');
                }

                /** @var \Ophirah_Qquoteadv_Model_Requestitem $item */
                $item = Mage::getModel('qquoteadv/requestitem')->load((int)$params['request_id']);
                if (!$item->getRequestId()) {
                    $this->_fault('data_invalid', 'Item not exists by RequestId');
                }

                // Update quote
                try {
                    unset($params['request_id']);

                    //change selected product data in quoteadv_product
                    /** @var \Ophirah_Qquoteadv_Model_Qqadvproduct $productItem */
                    $productItem = Mage::getModel('qquoteadv/qqadvproduct')->load($item->getQuoteadvProductId());
                    try {
                        if (array_key_exists('request_qty', $params)) {
                            $orgQty = $productItem->getQty();
                            $requestQty = $item->getRequestQty();
                            if ($orgQty == $requestQty) {
                                $productItem->setQty($params['request_qty']);
                                $productItem->save();
                            }
                        }
                    } catch (Exception $e) {
                        Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                    }

                    //change item data in quoteadv_request_item
                    $item->addData($params);
                    $item->save();

                    $_qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quote_id);
                    $_qquoteadv->collectTotals();
                    $_qquoteadv->save();
                } catch (Exception $e) {
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                    $this->_fault('save_error', $e->getMessage());
                }

                $response['success'] = true;
            } else {
                $this->_fault(
                    'not_exists',
                    "QuoteId " . $quote_id . "REQUEST: " . $key . 'Data by QuoteId and RequestId not exists'
                );
            }
        } else {
            $this->_fault('data_invalid', "Initial parameters didn't not received");
        }

        return $response;
    }

    /**
     * Sets the quote status to proposal saved
     *
     * @param $quoteId
     */
    protected function _updateQuoteStatus($quoteId)
    {
        $quote = Mage::getSinglton('qquoteadv/qqadvcustomer')->load((int)$quoteId);
        if ($quote->getId()) {
            try {
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $quote));
                $quote->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED)->save();
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $quote));
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
            }
        }
    }

    /**
     * Add the products in $params to the quote in $params
     *
     * @param $params
     * @return array
     */
    public function add_products_to_quote($params)
    {
        $errors = array();
        if (!isset($params['quote_id'])) {
            $this->_fault('data_invalid', 'Quotation ID missing');
        } else {
            $quoteId = $params['quote_id'];
            $_qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
            if (!count($_qquoteadv->getData())) {
                $this->_fault('not_exists', 'Quotation ID (' . $quoteId . ') does not exist');
            }
        }

        if (!isset($params['products']) || !is_array($params['products']) || !count($params['products'])) {
            $this->_fault('data_invalid', 'Products missing');
        } else {
            //check that the proucts exist
            $productIds = $params['products'];
            foreach ($productIds as $productId) {
                $product = Mage::getModel('catalog/product')->load($productId);
                if (!$product->getData('entity_id')) {
                    $this->_fault('not_exists', 'Product ID (' . $productId . ') does not exist');
                }
            }
        }

        if (!count($errors)) {
            $hasOptions = false;
            $options = false;
            $storeId = $_qquoteadv->getStoreId();
            $modelProduct = Mage::getModel('qquoteadv/qqadvproduct');
            $qty = 1;
            foreach ($productIds as $productId) {
                $productsCollection = $modelProduct->getCollection()
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->addFieldToFilter('product_id', $productId);

                $attribute = serialize(array('product' => $productId, 'qty' => $qty));

                // don't add if it has already been added
                if (!$productsCollection->getSize()) {
                    $qproduct = array(
                        'quote_id'    => $quoteId,
                        'product_id'  => $productId,
                        'qty'         => $qty,
                        'attribute'   => $attribute,
                        'has_options' => $hasOptions,
                        'options'     => $options,
                        'store_id'    => $storeId
                    );

                    $checkQty = $modelProduct->addProduct($qproduct);
                    if (is_null($checkQty)) { // product has not been added redirect with error
                        $checkQty = new Varien_Object();
                        $checkQty->setHasError(true);
                        $checkQty->setMessage(Mage::helper('qquoteadv')->__('Product cannot be added to quote list'));
                    }

                    if ($checkQty->getHasError()) {
                        $this->_fault('save_error', $checkQty->getMessage());
                    } else {
                        $quoteadvProductId = $checkQty->getData('id');

                        $ownerPrice = Mage::helper('qquoteadv')->_applyPrice($quoteadvProductId, $qty);
                        $originalPrice = Mage::helper('qquoteadv')->_applyPrice($quoteadvProductId, $qty);
                        //#current currency price
                        $currencyCode = $_qquoteadv->getCurrency();
                        $ownerCurPrice = Mage::helper('qquoteadv')->_applyPrice(
                            $quoteadvProductId,
                            $qty,
                            $currencyCode
                        );
                        $originalCurPrice = Mage::helper('qquoteadv')->_applyPrice(
                            $quoteadvProductId,
                            $qty,
                            $currencyCode
                        );

                        $item = array(
                            'quote_id'            => $quoteId,
                            'product_id'          => $productId,
                            'request_qty'         => $qty,
                            'owner_base_price'    => $ownerPrice,
                            'original_price'      => $originalPrice,
                            'owner_cur_price'     => $ownerCurPrice,
                            'original_cur_price'  => $originalCurPrice,
                            'quoteadv_product_id' => $quoteadvProductId
                        );

                        $requestitem = Mage::getModel('qquoteadv/requestitem')->setData($item);
                        $requestitem->save();
                        $_qquoteadv->collectTotals();
                        $_qquoteadv->save();
                    }
                }
            }
        }

        return array(
            'success' => true
        );
    }

    /**
     * Update the quote in $params to the status in $params
     *
     * @param $params
     * @return array
     */
    public function update_quote_status($params)
    {
        // see Ophirah_Qquoteadv_Model_Status for status codes
        $errors = array();
        if (!isset($params['quote_id'])) {
            $this->_fault('data_invalid', 'Quotation ID missing');
        } else {
            $quoteId = $params['quote_id'];
            $_qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
            if (!count($_qquoteadv->getData())) {
                $this->_fault('not_exists', 'Quotation ID (' . $quoteId . ') does not exist');
            }
        }

        if (!isset($params['status'])) {
            $this->_fault('data_invalid', 'Status missing');
        }

        if (!count($errors)) {

            $_qquoteadv->setStatus($params['status']);
            try {
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_qquoteadv));
                $_qquoteadv->save();
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_qquoteadv));
                $response = array(
                    'success' => true
                );
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_api_exception.log', true);
                $this->_fault($e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Function to create a quote using the API
     *
     * @param $data
     * @return int
     * @throws Exception
     */
    public function createQuote($data)
    {
        /** @var $quote \Ophirah_Qquoteadv_Model_Qqadvcustomer*/
        $quote = Mage::getModel('qquoteadv/qqadvcustomer');

        try {
            $quote->setData((array)$data);
            $quote->setIsQuote(true);
            if (!$quote->getStoreId()) {
                throw new Exception('Store id is not set');
            }

            if (!$quote->getCustomerId()) {
                throw new Exception('Customer id is not set');
            }

            $customer = Mage::getModel('customer/customer');
            $customer->load($quote->getCustomerId());
            if (!$customer->getId()) {
                throw new Exception('Customer does not exist');
            }

            $store = Mage::getModel('core/store')->load($quote->getStoreId());
            if (!$store->getId()) {
                throw new Exception('Store does not exist');
            }

            if (!$quote->getCreateAt()) {
                $quote->setCreatedAt(now());
            }

            if (!$quote->getUpdatedAt()) {
                $quote->setUpdatedAt(now());
            }

            if (!$quote->getStatus()) {
                $quote->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN);
            }

            if (!$quote->getIncrementId()) {
                $quote->setIncrementId(Mage::getModel('qquoteadv/entity_increment_numeric')
                    ->getNextId($quote->getStoreId(), true));
            }

            $quote->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('create_quote_fault', $e->getMessage());
        }

        return (int)$quote->getId();
    }

    /**
     * Function to accept a quote using the API
     *
     * @param $quoteId
     * @return bool
     */
    public function acceptQuote($quoteId)
    {
        if (!isset($quoteId)) {
            $this->_fault('data_invalid', 'Quotation ID missing');
        } else {
            $_qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);

            if (!count($_qquoteadv->getData())) {
                $this->_fault('not_exists', sprintf('Quote #%s does not exist', $quoteId));
            }

            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_qquoteadv));
            $_qquoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_CONFIRMED);
            if (!$_qquoteadv->save()) {
                $this->_fault('save_error', sprintf('Could not set quote #%s as accepted', $quoteId));
            }
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_qquoteadv));
        }

        return true;
    }

    /**
     * Reject a quote using the API
     *
     * @param $quoteId
     * @return bool
     */
    public function rejectQuote($quoteId)
    {
        if (!isset($quoteId)) {
            $this->_fault('data_invalid', 'Quotation ID missing');
        } else {
            $_qquoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);

            if (!count($_qquoteadv->getData())) {
                $this->_fault('not_exists', sprintf('Quote #%s does not exist', $quoteId));
            }

            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_qquoteadv));
            $_qquoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_DENIED);
            if (!$_qquoteadv->save()) {
                $this->_fault('save_error', sprintf('Could not set quote #%s as accepted', $quoteId));
            }
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_qquoteadv));
        }
        return true;
    }
}
