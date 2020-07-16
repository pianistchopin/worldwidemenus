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

$installer->endSetup();

if (!Mage::helper('ambase')->isModuleActive('Mage_AdminNotification')) {
    return;
}

Amasty_Base_Helper_Module::baseModuleInstalled();

$feedData = array();
$feedData[] = array(
    'severity'    => 4,
    'date_added'  => gmdate('Y-m-d H:i:s', time()),
    'title'       => 'Amasty`s extension has been installed. Remember to flush all cache, recompile, log-out and log back in.',
    'description' => 'You can see versions of the installed extensions right in the admin, as well as configure notifications about major updates.',
    'url'         => 'https://amasty.com/knowledge-base/how-to-disable-base-module-notifications-in-magento-1.html',
    'is_amasty' => 1
);

Mage::getModel('adminnotification/inbox')->parse($feedData);
