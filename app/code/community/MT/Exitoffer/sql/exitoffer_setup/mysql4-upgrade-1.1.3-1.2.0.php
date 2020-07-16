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
$popupTable = $installer->getTable('exitoffer/popup');

$installer->run("

ALTER TABLE `{$popupTable}`
	ADD COLUMN `text_5` TEXT NULL AFTER `text_4`,
	ADD COLUMN `text_6` TEXT NULL AFTER `text_5`,
	ADD COLUMN `text_7` TEXT NULL AFTER `text_6`,
	ADD COLUMN `text_8` TEXT NULL AFTER `text_7`;

");

$installer->endSetup();