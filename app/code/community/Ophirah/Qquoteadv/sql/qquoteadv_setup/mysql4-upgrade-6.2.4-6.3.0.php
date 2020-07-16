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

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

Mage::getModel('adminnotification/inbox')->parse(array(
    array(
        'severity'      => Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
        'date_added'    => date('Y-m-d H:i:s'),
        'title'         => 'Cart2Quote is also available for Magento 2!',
        'description'   => 'Cart2Quote is also available for Magento 2. Need help with your Magento 2 project? Check our partner page for local development agencies: https://www.cart2quote.com/cart2quote-partners',
        'url'           => 'https://www.cart2quote.com/magento-b2b-extensions',
        'internal'      => true
    )
));

$installer->endSetup();
