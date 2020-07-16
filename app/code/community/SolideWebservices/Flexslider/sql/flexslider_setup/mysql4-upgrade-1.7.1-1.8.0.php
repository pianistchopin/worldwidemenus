<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'nav_color', " varchar(32) NOT NULL default '#666666'");
$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'pagination_color', " varchar(32) NOT NULL default '#ffffff'");
$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'loader_color', " varchar(32) NOT NULL default '#eeeeee'");
$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'loader_bgcolor', " varchar(32) NOT NULL default '#222222'");
$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'overlay_textcolor', " varchar(32) NOT NULL default '#ffffff'");
$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'overlay_bgcolor', " varchar(32) NOT NULL default '#222222'");
$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'overlay_hovercolor', " varchar(32) NOT NULL default '#666666'");
$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'caption_textcolor', " varchar(32) NOT NULL default '#ffffff'");
$this->getConnection()->modifyColumn($this->getTable('flexslider_group'), 'caption_bgcolor', " varchar(32) NOT NULL default '#222222'");

$this->getConnection()->dropColumn($this->getTable('flexslider_group'), 'loader_opacity');
$this->getConnection()->dropColumn($this->getTable('flexslider_group'), 'overlay_opacity');
$this->getConnection()->dropColumn($this->getTable('flexslider_group'), 'caption_opacity');

$this->endSetup();
?>