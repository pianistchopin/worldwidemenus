<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'slider_animationloop', " tinyint(1) NOT NULL default 1");
$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'slidetype', " varchar(32) NOT NULL default 'image'");
$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'video_id', " varchar(255) NOT NULL default ''");

$this->run("
	CREATE TABLE IF NOT EXISTS {$this->getTable('flexslider_product')} (
		`group_id` smallint(6) NOT NULL,
		`product_sku` varchar(255) NOT NULL default '',
		PRIMARY KEY (`group_id`,`product_sku`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Flexslider Products';
");

$this->endSetup();
?>