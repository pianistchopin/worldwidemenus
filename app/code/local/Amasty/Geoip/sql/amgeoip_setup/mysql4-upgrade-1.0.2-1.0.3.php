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

$fieldsSql = 'SHOW COLUMNS FROM ' . $tableBlock;
$colsBlock = $this->getConnection()->fetchCol($fieldsSql);

if (in_array('region', $colsLoc)) {
    $this->run("alter table `{$tableLocation}` drop column `region`;");
}
if (in_array('postal_code', $colsLoc)) {
    $this->run("alter table `{$tableLocation}` drop column `postal_code`;");
}
if (in_array('latitude', $colsLoc)) {
    $this->run("alter table `{$tableLocation}` drop column `latitude`;");
}
if (in_array('longitude', $colsLoc)) {
    $this->run("alter table `{$tableLocation}` drop column `longitude`;");
}
if (in_array('dma_code', $colsLoc)) {
    $this->run("alter table `{$tableLocation}` drop column `dma_code`;");
}
if (in_array('area_code', $colsLoc)) {
    $this->run("alter table `{$tableLocation}` drop column `area_code`;");
}

if (!in_array('postal_code', $colsBlock)) {
    $this->run("alter table `{$tableBlock}` add column `postal_code` CHAR(5) NULL DEFAULT NULL;");
}
if (!in_array('latitude', $colsBlock)) {
    $this->run("alter table `{$tableBlock}` add column `latitude` FLOAT NULL DEFAULT NULL;");
}
if (!in_array('longitude', $colsBlock)) {
    $this->run("alter table `{$tableBlock}` add column `longitude` FLOAT NULL DEFAULT NULL;");
}

Mage::getConfig()->saveConfig('amgeoip/import/block', 0);
Mage::getConfig()->saveConfig('amgeoip/import/location', 0);

if ('true' == (string)Mage::getConfig()->getNode('modules/Mage_AdminNotification/active')) {
    $feedData = array();
    $feedData[] = array(
        'severity'      => 4,
        'date_added'    => gmdate('Y-m-d H:i:s', time()),
        'title'         => 'Amasty`s extension Geo Ip Data has been installed. Please import Geo Ip Data.',
        'description'   => 'You can see versions of the installed extensions right in the admin, as well as configure notifications about major updates.',
        'url'           => 'http://amasty.com/news/updates-and-notifications-configuration-9.html'
    );
    Mage::getModel('adminnotification/inbox')->parse($feedData);
}

$this->endSetup();
