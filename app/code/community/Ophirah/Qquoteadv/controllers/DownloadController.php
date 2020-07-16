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
require_once('Mage/Sales/controllers/DownloadController.php');

/**
 * Class Ophirah_Qquoteadv_DownloadController
 */
class Ophirah_Qquoteadv_DownloadController extends Mage_Sales_DownloadController
{
    /**
     * {@inheritDoc}
     */
    public function downloadCustomOptionAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (is_numeric($id)) {
            parent::downloadCustomOptionAction();
            return;
        }
        Mage::dispatchEvent('ophirah_qquoteadv_downloadcustomoption_before', array($id));

        try {
            $downloadInfo = json_decode(base64_decode($id));
            $product = Mage::getModel('qquoteadv/qqadvproduct')->load($downloadInfo->product);
            if (!$product->getId()) {
                $this->_forward('noRoute');
                return;
            }
            $options = unserialize($product->getData('options'));
            if (!isset($options[$downloadInfo->option])) {
                $this->_forward('noRoute');
                return;
            }
            $info = $options[$downloadInfo->option];
            $this->_downloadFileAction($info);
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            $this->_forward('noRoute');
        }

        Mage::dispatchEvent('ophirah_qquoteadv_downloadcustomoption_after', array($id));
        exit(0);
    }
}
