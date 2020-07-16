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

// Move config paths
$newConfigPaths = array();
$newConfigPaths["qquoteadv_advanced_settings/frontend/redirect_to_quotation"]           = "qquoteadv_quote_frontend/shoppingcart_quotelist/redirect_to_quotation";
$newConfigPaths["qquoteadv_advanced_settings/quick_quote/quick_quote_mode"]             = "qquoteadv_quote_frontend/catalog/quick_quote_mode";

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

foreach ($newConfigPaths as $oldPath => $newPath) {
    $installer->run("UPDATE {$installer->getTable('core_config_data')} SET `path` = REPLACE(`path`, '".$oldPath."', '".$newPath."') WHERE `path` = '".$oldPath."'");
}

// Add missing fields
if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'vat_id')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `vat_id` VARCHAR(255) NULL;
    ");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('quoteadv_customer'), 'shipping_vat_id')) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `shipping_vat_id` VARCHAR(255) NULL;
    ");
}

$installer->endSetup();
