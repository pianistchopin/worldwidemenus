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
$fieldTable = $installer->getTable('exitoffer/field');
$campaignTable = $installer->getTable('exitoffer/campaign');
$campaignStoreTable = $installer->getTable('exitoffer/campaign_store');
$campaignPageTable = $installer->getTable('exitoffer/campaign_page');

$installer->run("DROP TABLE IF EXISTS `{$popupTable}`;");
$installer->run("DROP TABLE IF EXISTS `{$fieldTable}`;");
$installer->run("DROP TABLE IF EXISTS `{$campaignTable}`;");
$installer->run("DROP TABLE IF EXISTS `{$campaignStoreTable}`;");
$installer->run("DROP TABLE IF EXISTS `{$campaignPageTable}`;");

$installer->run("
CREATE TABLE `{$popupTable}` (
	`entity_id` INT(10) NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) NULL DEFAULT NULL,
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`content_type` VARCHAR(50) NULL DEFAULT NULL,
	`static_block_id` INT(10) NULL DEFAULT NULL,
	`theme` VARCHAR(50) NULL DEFAULT NULL,
	`show_in_last` TINYINT(1) NULL DEFAULT NULL,
	`cookie_lifetime` INT(5) NULL DEFAULT NULL,
	`show_coupon_code` TINYINT(1) NULL DEFAULT NULL,
	`text_1` TEXT NULL,
	`text_2` TEXT NULL,
	`text_3` TEXT NULL,
	`text_4` TEXT NULL,
	`color_1` VARCHAR(7) NULL DEFAULT NULL,
	`color_2` VARCHAR(7) NULL DEFAULT NULL,
	`color_3` VARCHAR(7) NULL DEFAULT NULL,
	`coupon_status` TINYINT(1) NULL DEFAULT NULL,
	`coupon_rule_id` INT(10) NULL DEFAULT NULL,
	`coupon_length` INT(10) NULL DEFAULT NULL,
	`coupon_format` VARCHAR(50) NULL DEFAULT NULL,
	`coupon_prefix` VARCHAR(50) NULL DEFAULT NULL,
	`coupon_suffix` VARCHAR(50) NULL DEFAULT NULL,
	`coupon_dash` INT(3) NULL DEFAULT NULL,
	PRIMARY KEY (`entity_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;
");

$installer->run("
CREATE TABLE `{$fieldTable}` (
	`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`popup_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`type` VARCHAR(50) NULL DEFAULT NULL,
	`title` VARCHAR(255) NULL DEFAULT NULL,
	`options` TEXT NULL,
	`is_required` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
	`position` INT(10) NULL DEFAULT NULL,
	PRIMARY KEY (`entity_id`),
	UNIQUE INDEX `popup_id_name` (`popup_id`, `name`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;
");

$installer->run("
CREATE TABLE `{$campaignTable}` (
	`entity_id` INT(10) NOT NULL AUTO_INCREMENT,
	`popup_id` INT(10) NOT NULL DEFAULT '0',
	`status` TINYINT(1) NULL DEFAULT '0',
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`start_date` DATETIME NULL DEFAULT NULL,
	`end_date` DATETIME NULL DEFAULT NULL,
	`layer_close` TINYINT(1) NULL DEFAULT NULL,
	`show_in_last_tab` TINYINT(1) NULL DEFAULT NULL,
	`cookie_lifetime` INT(5) NULL DEFAULT NULL,
	`params` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`entity_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;

");

$installer->run("
CREATE TABLE `{$campaignPageTable}` (
	`campaign_id` INT(10) NOT NULL DEFAULT '0',
	`page_id` VARCHAR(20) NOT NULL DEFAULT '',
	PRIMARY KEY (`campaign_id`, `page_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

");

$installer->run("
CREATE TABLE `{$campaignStoreTable}` (
	`campaign_id` INT(10) NOT NULL DEFAULT '0',
	`store_id` INT(5) NOT NULL DEFAULT '0',
	PRIMARY KEY (`campaign_id`, `store_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
");

$installer->endSetup();