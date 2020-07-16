<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'overlay_position', " varchar(32) NOT NULL default 'right'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'overlay_textcolor', " varchar(7) NOT NULL default '#ffffff'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'overlay_bgcolor', " varchar(7) NOT NULL default '#222222'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'overlay_hovercolor', " varchar(7) NOT NULL default '#666666'");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'overlay_opacity', " varchar(3) NOT NULL default '0.8'");

$this->endSetup();
?>