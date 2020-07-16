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

$newConfigPaths = array();
$newConfigPaths["crmaddon/general/enabled"]             = "qquoteadv_sales_representatives/messaging/enabled";
$newConfigPaths["crmaddon/emails/crmaddon_container"]   = "qquoteadv_sales_representatives/messaging/crmaddon_container";
$newConfigPaths["crmaddon/emails/crmaddon_shortmsg"]    = "qquoteadv_sales_representatives/messaging/crmaddon_shortmsg";

/** @var Ophirah_Crmaddon_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

foreach ($newConfigPaths as $oldPath => $newPath) {
    $installer->run("UPDATE {$installer->getTable('core_config_data')} SET `path` = REPLACE(`path`, '".$oldPath."', '".$newPath."') WHERE `path` = '".$oldPath."'");
}

$installer->endSetup();

