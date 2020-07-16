<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


$this->startSetup();

$tableLocation = $this->getTable('amgeoip/location');
$tableBlock = $this->getTable('amgeoip/block');

$this->run("
    TRUNCATE TABLE `{$tableLocation}`;
    TRUNCATE TABLE `{$tableBlock}`;
");


$fieldsSql = 'SHOW COLUMNS FROM ' . $tableLocation;
$colsLoc = $this->getConnection()->fetchCol($fieldsSql);

if (!in_array('region', $colsLoc)) {
    $this->run("alter table `{$tableLocation}` add column `region` VARCHAR(255) NULL DEFAULT NULL;");
}

Mage::getConfig()->saveConfig('amgeoip/import/block', 0);
Mage::getConfig()->saveConfig('amgeoip/import/location', 0);

if ('true' == (string)Mage::getConfig()->getNode('modules/Mage_AdminNotification/active')) {
    $feedData = array();
    $feedData[] = array(
        'severity'      => 4,
        'date_added'    => gmdate('Y-m-d H:i:s', time()),
        'title'         => 'Amasty`s extension Geo Ip Data has been updated. Please reimport Geo Ip Data.',
        'description'   => 'You can see versions of the installed extensions right in the admin, as well as configure notifications about major updates.',
        'url'           => 'http://amasty.com/news/updates-and-notifications-configuration-9.html'
    );
    Mage::getModel('adminnotification/inbox')->parse($feedData);
}

$this->endSetup();
