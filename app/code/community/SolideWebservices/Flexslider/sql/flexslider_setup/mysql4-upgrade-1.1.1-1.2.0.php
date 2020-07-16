<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'slider_pauseonaction', " tinyint(1) NOT NULL default 1");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'slider_pauseonhover', " tinyint(1) NOT NULL default 0");
$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'hosted_image', " tinyint(1) NOT NULL default 0");
$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'hosted_image_url', " varchar(512) NOT NULL default ''");
$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'hosted_image_thumburl', " varchar(512) NOT NULL default ''");

$this->endSetup();
?>