<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

$installer = $this;
$installer->startSetup();
$table = $installer->getTable('exitoffer/campaign');

$installer->run("
ALTER TABLE `{$table}`
	ADD COLUMN `conditions_serialized` MEDIUMTEXT NULL DEFAULT NULL;

ALTER TABLE `{$table}`
	ADD COLUMN `actions_serialized` MEDIUMTEXT NULL DEFAULT NULL;
");

$installer->endSetup();