<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'slider_loggedin', " tinyint(1) NOT NULL default 0");
$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'slide_loggedin', " tinyint(1) NOT NULL default 0");

$this->endSetup();
?>