<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

$this->startSetup();

$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'carousel_minitems', " smallint(2) NOT NULL default 3");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'carousel_maxitems', " smallint(2) NOT NULL default 5");
$this->getConnection()->addColumn($this->getTable('flexslider_group'), 'carousel_move', " smallint(2) NOT NULL default 0");

$this->endSetup();
?>
