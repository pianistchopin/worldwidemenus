<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'custom_theme', " text NOT NULL default ''");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'slider_easing', " varchar(32) NOT NULL default 'jswing'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'loader_show', " tinyint(1) NOT NULL default 1");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'loader_position', " varchar(32) NOT NULL default 'top'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'loader_color', " varchar(7) NOT NULL default '#eeeeee'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'loader_bgcolor', " varchar(7) NOT NULL default '#222222'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'loader_opacity', " varchar(3) NOT NULL default '0.8'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'caption_textcolor', " varchar(7) NOT NULL default '#ffffff'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'caption_bgcolor', " varchar(7) NOT NULL default '#222222'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'caption_opacity', " varchar(3) NOT NULL default '0.6'");

$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'caption_position', " varchar(32) NOT NULL default 'random'");

$this->endSetup();
?>