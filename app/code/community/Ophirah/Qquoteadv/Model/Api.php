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
 * Class Ophirah_Qquoteadv_Model_Api
 */
class Ophirah_Qquoteadv_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    CONST XML_PATH_QQUOTEADV_REQUEST_PROPOSAL_EMAIL_TEMPLATE = 'qquoteadv_quote_emails/templates/proposal';

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

        if (count($filters)) {
            foreach ($filters as $filter => $value) {
                $_collection->addFieldToFilter("$filter", $value);
            }
        }

        return $_collection->toArray();
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
        $exceptions = array();
        if (isset($params['quote_id']) && isset($params['value'])) {
            $quote_id = $params['quote_id'];
            $value = $params['value'];

            /** @var \Ophirah_Qquoteadv_Model_Qqadvcustomer $_quoteCollection */
            $_quoteCollection = Mage::getModel('qquoteadv/qqadvcustomer')->load($quote_id);
            $_quoteCollection->setImported($value);
            try {
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteCollection));
                $_quoteCollection->save();
                Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteCollection));
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                $data = array('request_data' => $params, 'exception' => $e->getMessage());
                $exceptions[] = $data;
            }

            if (count($exceptions) > 0) {
                return array(
                    'error'  => var_export($exceptions, 1),
                    'method' => __METHOD__
                );
            }

            return true; //'setimported: update is ok';
        }

        return array(
            'error'  => 'request data format is not correct',
            'method' => __METHOD__
        );
    }

    /**
     * Retrieve an quotation's information
     *
     * @param int $quote_id
     * @return array
     */
    public function info($quote_id)
    {
        /** @var \Ophirah_Qquoteadv_Model_Mysql4_Qqadvcustomer_Collection $_quoteCollection */
        $_quoteCollection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $quote_id);

        if ($_quoteCollection->getSize() > 0) {
            $response = $_quoteCollection->toArray();

            foreach ($response['items'] as $index => $row) {
                $key = $row['id'];
                $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                    ->addFieldToFilter('quote_id', $quote_id)
                    ->addFieldToFilter('quoteadv_product_id', $key);

                if ($_collection->getSize() > 0) {
                    $requested = $_collection->toArray();

                    $row['primary_key'] = $row['id'];
                    $storeId = $row['store_id'];
                    $productId = $row['product_id'];
                    try {
                        $sku = Mage::getModel('catalog/product')
                            ->setStoreId($storeId)
                            ->load($productId)
                            ->getSku();
                    } catch (Exception $e) {
                        Mage::log(
                            'Exception: ' . $e->getMessage(),
                            null,
                            'c2q_exception.log',
                            true
                        );
                    }

                    $row['sku'] = $sku ? $sku : "not exists";

                    //#add attributes/options
                    foreach ($requested['items'] as $key => $request) {
                        //$request['foreign_key'] = $request['quoteadv_product_id'];
                        //unset($request['quoteadv_product_id']);

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
            return array(
                'error'  => 'Quote is not found',
                'method' => __METHOD__
            );
        }
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
     *  'quote_id'              => int,
     *  'product_id'            => int,
     *  'request_qty'           => int,
     *  'owner_base_price'      => float,
     *  'original_price'        => float,
     *  'quoteadv_product_id'   => int
     * );
     * @return array
     */
    public function add_qtybyitem($params)
    {
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
                ->addFieldToFilter('quote_id', $quote_id)
                ->addFieldToFilter('quoteadv_product_id', $key);

            if ($_collection->getSize() > 0) {
                $_collection->clear();
                $_collection->addFieldToFilter('request_qty', $request_qty);
                $data = $_collection->getData();
                if (count($data) > 0) {
                    $message = 'Duplicate qty value entered:';
                    $message .= "\nrequest data:" . var_export($params, 1);

                    Mage::log('Message: ' . $message, null, 'c2q.log', true);
                    return array(
                        'error' => $message,
                        'method' => __METHOD__
                    );
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
                    $message = 'Can not add item to quote request. ';
                    Mage::log('Message: ' . $message, null, 'c2q.log', true);
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);

                    return array(
                        'error' => $message,
                        'method' => __METHOD__
                    );
                }

                return array(
                    'result' => 'success',
                    'method' => __METHOD__
                );
            } else {
                return array(
                    'error' => 'Quote data is not ok',
                    'method' => __METHOD__
                );
            }
        }

        return array(
            'error' => 'Quote is not found',
            'method' => __METHOD__
        );
    }

    /**
     * Send email proposal to client
     *
     * @param int $quoteId
     * @return array
     */
    public function send_proposal($quoteId)
    {
        $exceptions = array();

        if ($quoteId) {
            $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);

            //#send Proposal email
            $customerId = $_quoteadv->getCustomerId();
            if ($customerId) {
                $_collection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                    ->addFieldToFilter('quote_id', $quoteId);

                if ($_collection->getSize() > 0) {
                    $res = $this->_sendProposalEmail($quoteId);
                    if (empty($res)) {
                        $message = sprintf("Qquote proposal email was't sent to the client for quote #%s", $_quoteadv->getId());
                        $exceptions[] = $message;
                    } else {
                        $_quoteadv->setStatus(Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL);

                        try {
                            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
                            $_quoteadv->save();
                            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
                        } catch (Exception $e) {
                            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                            $exceptions[] = $e->getMessage();
                        }
                    }
                } else {
                    $exceptions[] = 'Quote data is ok for proposal sent';
                }
            } else {
                $exceptions[] = 'Quote customer is not found';
            }
        } else {
            $exceptions[] = 'Quote is not found';
        }

        if (count($exceptions) > 0) {
            return array(
                'error' => var_export($exceptions, 1),
                'method' => __METHOD__
            );
        }

        return array(
            'result' => 'success',
            'method' => __METHOD__
        );
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
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
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
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
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
        $exceptions = array();

        if (isset($params['quote_id'])
            && isset($params['shipping_type'])
            && isset($params['shipping_price'])
        ) {
            $quoteId = (int)$params['quote_id'];
            $type = (string)$params['shipping_type'];
            $price = (float)$params['shipping_price'];

            if (empty($type)) {
                $price = -1;
            } elseif (($type == "I" or $type == "O") && $price > 0) {
                //do nothing //C2Q sipping method
            } else {
                $exceptions[] = 'Request data is not ok';
            }

            if (!count($exceptions)) {
                if ($quoteId) {
                    $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);
                    $_quoteadv->setShippingType($type);
                    $_quoteadv->setShippingPrice($price);

                    try {
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
                        Mage::log(
                            'Exception: ' . $e->getMessage(),
                            null,
                            'c2q_exception.log',
                            true
                        );
                        $message = $e->getMessage();
                        $exceptions[] = $message;
                    }
                } else {
                    $exceptions[] = 'Quote is not found';
                }
            }
        } else {
            $exceptions[] = 'Request data is not ok';
        }

        if (count($exceptions) > 0) {
            return array(
                'error' => var_export($exceptions, 1),
                'method' => __METHOD__
            );
        }

        return array(
            'result' => 'success',
            'method' => __METHOD__
        );
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
        $exceptions = array();
        $limit = 400;

        if (isset($params['quote_id']) && isset($params['comment'])) {
            $quoteId = (int)$params['quote_id'];
            $comment = trim($params['comment']);
            if (strlen($comment) > $limit) {
                $exceptions[] = sprintf("Quote comment length has limit %s characters", $limit);
            }

            if (!count($exceptions)) {
                if ($quoteId) {
                    $_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);
                    $_quoteadv->setClientRequest($comment);

                    try {
                        Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
                        $_quoteadv->save();
                        Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
                    } catch (Exception $e) {
                        Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                        $message = $e->getMessage();
                        $exceptions[] = $message;
                    }
                } else {
                    $exceptions[] = 'Quote is not found';
                }
            }
        } else {
            $exceptions[] = 'Request data is not ok';
        }

        if (count($exceptions) > 0) {
            return array(
                'error' => var_export($exceptions, 1),
                'method' => __METHOD__
            );
        }

        return array(
            'result' => 'success',
            'method' => __METHOD__
        );
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
        $exceptions = array();
        $limit = 400;

        if (isset($params['quoteadv_product_id']) && isset($params['comment'])) {
            $id = (int)$params['quoteadv_product_id'];
            $comment = trim($params['comment']);
            if (strlen($comment) > $limit) {
                $exceptions[] = sprintf("Quote comment length has limit %s characters", $limit);
            }

            if (count($exceptions) > 0) {
                //#errors
            } else {
                $_quoteadv = Mage::getModel('qquoteadv/qqadvproduct')->load($id);
                $_quoteadv->setClientRequest($comment);

                try {
                    Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $_quoteadv));
                    $_quoteadv->save();
                    Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $_quoteadv));
                } catch (Exception $e) {
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                    $message = $e->getMessage();
                    $exceptions[] = $message;
                }
            }
        } else {
            $exceptions[] = 'Request data is not ok';
        }

        if (count($exceptions) > 0) {
            return array(
                'error' => var_export($exceptions, 1),
                'method' => __METHOD__
            );
        }

        return array(
            'result' => 'success',
            'method' => __METHOD__
        );
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
        $exceptions = array();

        if (isset($params['request_id']) && isset($params['quote_id'])) {
            $requestId = (int)$params['request_id'];
            $quoteId = (int)$params['quote_id'];

            /** @var \Ophirah_Qquoteadv_Model_Requestitem $_quoteadv */
            $_quoteadv = Mage::getModel('qquoteadv/requestitem')->load($requestId);
            $itemData = $_quoteadv->getData();
            if (count($itemData) > 0) {
                if ($itemData['quote_id'] == $quoteId) {
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
                        } catch (Exception $e) {
                            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                            $message = $e->getMessage();
                            $exceptions[] = $message;
                        }
                    } else {
                        $exceptions[] = 'You cannot delete this last item Qty';
                    }
                } else {
                    $exceptions[] = 'Request data is not ok';
                }
            } else {
                $exceptions[] = 'Quote item qty is not found';
            }
        } else {
            $exceptions[] = 'Request data is not ok';
        }

        if (count($exceptions) > 0) {
            return array(
                'error' => var_export($exceptions, 1),
                'method' => __METHOD__
            );
        }
        return array(
            'result' => 'success',
            'method' => __METHOD__
        );
    }

    /**
     * Delete requested item
     *
     * @param array $params
     * int $params['primary_key']
     * int $params['quote_id']
     * @return array
     */
    public function delete_requested_item($params)
    {
        $exceptions = array();

        if (isset($params['primary_key']) && isset($params['quote_id'])) {
            $id = (int)$params['primary_key'];
            $quoteId = (int)$params['quote_id'];
            $_quoteadv = Mage::getModel('qquoteadv/qqadvproduct')->load($id);

            $itemData = $_quoteadv->getData();
            if (count($itemData) > 0) {
                if ($itemData['quote_id'] == $quoteId) {
                    $_itemsCollection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
                        ->addFieldToFilter('quote_id', $quoteId);

                    if ($_itemsCollection->getSize() > 1) {
                        try {
                            $_quoteadv->delete();

                            /** @var \Ophirah_Qquoteadv_Model_Qqadvcustomer $quote */
                            $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
                            $quote->collectTotals();
                            $quote->save();
                        } catch (Exception $e) {
                            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                            $message = $e->getMessage();
                            $exceptions[] = $message;
                        }
                    } else {
                        $exceptions[] = 'You cannot delete this last item';
                    }
                } else {
                    $exceptions[] = 'Request data is not ok';
                }
            } else {
                $exceptions[] = 'Quote item is not found';
            }
        } else {
            $exceptions[] = 'Request data is not ok';
        }

        if (count($exceptions) > 0) {
            return array(
                'error' => var_export($exceptions, 1),
                'method' => __METHOD__
            );
        }

        return array(
            'result' => 'success',
            'method' => __METHOD__
        );
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
        if (isset($params['request_id'])
            && isset($params['quote_id'])
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
                ->addFieldToFilter('quote_id', $quote_id)
                ->addFieldToFilter('quoteadv_product_id', $key);

            if ($_collection->getSize() > 0) {
                $_collection->clear();
                $_collection->addFieldToFilter('request_qty', $request_qty);
                $data = $_collection->getData();
                if (count($data) > 0) {
                    $message = 'Duplicate qty value entered:';
                    $message .= "\nrequest data:" . var_export($params, 1);

                    Mage::log('Message: ' . $message, null, 'c2q.log', true);
                    return array(
                        'error' => $message,
                        'method' => __METHOD__
                    );
                }

                try {
                    /** @var \Ophirah_Qquoteadv_Model_Requestitem $requestItem */
                    $requestItem = Mage::getModel('qquoteadv/requestitem')->load($params['request_id']);
                    unset($params['request_id']);

                    //change selected product data in quoteadv_product
                    $productItem = Mage::getModel('qquoteadv/qqadvproduct')->load($requestItem->getQuoteadvProductId());
                    try {
                        if (array_key_exists('request_qty', $params)) {
                            $orgQty = $productItem->getQty();
                            $requestQty = $requestItem->getRequestQty();
                            if ($orgQty == $requestQty) {
                                $productItem->setQty($params['request_qty']);
                                $productItem->save();
                            }
                        }
                    } catch (Exception $e) {
                        Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                    }

                    //change item data in quoteadv_request_item
                    $requestItem->setData($params);
                    $requestItem->save();
                } catch (Exception $e) {
                    $message = 'Can not update item\'s qty. ';
                    Mage::log('Message: ' . $message, null, 'c2q.log', true);
                    Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);

                    return array(
                        'error' => $message,
                        'method' => __METHOD__
                    );
                }

                return array(
                    'result' => 'success',
                    'method' => __METHOD__
                );
            } else {
                return array(
                    'error' => 'Quote data is not ok',
                    'method' => __METHOD__
                );
            }
        }

        return array(
            'error' => 'Quote is not found',
            'method' => __METHOD__
        );
    }
}
