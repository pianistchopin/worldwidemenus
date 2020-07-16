<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tableBlock = $this->getTable('amgeoip/block');

$this->getConnection()->changeColumn($tableBlock, 'postal_code', 'postal_code', 'CHAR(10) NULL', false);
$this->endSetup();
