<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'caption_animation', " tinyint(1) NOT NULL default 1");
$this->getConnection()->addColumn($this->getTable('flexslider_slide'), 'video_autoplay', " tinyint(1) NOT NULL default 0");

$this->endSetup();
?>