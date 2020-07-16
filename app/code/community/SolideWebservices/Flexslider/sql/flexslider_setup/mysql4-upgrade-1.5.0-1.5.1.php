<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'slide_startdate', " datetime NULL");
$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'slide_enddate', " datetime NULL");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'slider_startdate', " datetime NULL");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'slider_enddate', " datetime NULL");

$this->endSetup();
?>