<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('adminnotification/inbox'),
    'is_amasty',
    'tinyint(1) unsigned default 0'
);

$installer->getConnection()->addColumn(
    $installer->getTable('adminnotification/inbox'),
    'expiration_date',
    'datetime default NULL'
);

$installer->endSetup();
