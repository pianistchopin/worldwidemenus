<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */

/**
 *
 * Adding the default data to the config amgeoipredirect/restriction/excepted_urls
 */

$this->startSetup();
$path = 'amgeoipredirect/restriction/excepted_urls';
$nameTable = $this->getTable('core/config_data');
$defaultUrls = PHP_EOL . Mage::getConfig()->getNode('default/' . $path);
$defaultUrls = addslashes($defaultUrls);
$this->run("UPDATE `{$nameTable}` SET `value` = CONCAT(`value`,'{$defaultUrls}') WHERE `path` = '{$path}'");
$this->endSetup();