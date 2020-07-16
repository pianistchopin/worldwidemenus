<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'type', " varchar(32) NOT NULL default 'basic'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'thumbnail_size', " smallint(5) NOT NULL default '200'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'theme', " varchar(32) NOT NULL default 'default'");
$this->getConnection()->changeColumn($this->getTable('flexslider_group'), 'slider_directionnav', 'nav_show', " varchar(32) NOT NULL default ''");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'nav_style', " varchar(32) NOT NULL default ''");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'nav_position', " varchar(32) NOT NULL default ''");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'nav_color', " varchar(7) NOT NULL default '#666666'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'pagination_show', " varchar(32) NOT NULL default ''");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'pagination_style', " varchar(32) NOT NULL default ''");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'pagination_position', " varchar(32) NOT NULL default ''");

$this->endSetup();
?>