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

$select = $installer->getConnection()->select();
$select
    ->from(array('result' => $installer->getTable('core_config_data')), array('value'))
    ->where('path LIKE "%carriers/qquoteshiprate/active%"');;

$found = 0;

foreach ($installer->getConnection()->fetchAll($select) as $key => $result) {
    $found++;
}

//this is probably not needed anymore
if (!$found) {
    $installer->run("
	INSERT INTO `{$installer->getTable('core_config_data')}` (`config_id`,`path`,`value`) VALUES (NULL,'carriers/qquoteshiprate/active','1');
	INSERT INTO `{$installer->getTable('core_config_data')}` (`config_id`,`path`,`value`) VALUES (NULL,'carriers/qquoteshiprate/name','Quote');
	INSERT INTO `{$installer->getTable('core_config_data')}` (`config_id`,`path`,`value`) VALUES (NULL,'carriers/qquoteshiprate/title','Custom Shipping Price');
	");
}

$installer->endSetup();
