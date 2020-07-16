<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup  */
$installer->startSetup();

$installer->run("
 DROP TABLE IF EXISTS `{$this->getTable('aitcg/mask_created')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('aitcg/mask_created')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mask_id` int(11) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$installer->run("
 DROP TABLE IF EXISTS `{$this->getTable('aitcg/mask')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('aitcg/mask')}` (
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`category_id` INT NOT NULL ,
`resize` INT( 1 ) NOT NULL  DEFAULT '0',
`name` VARCHAR( 255 ) NOT NULL ,
`filename` VARCHAR( 255 ) NOT NULL ,
INDEX ( `id` ) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$installer->run("
 DROP TABLE IF EXISTS `{$this->getTable('aitcg/mask_category')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('aitcg/mask_category')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");


$installer->run("
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `use_masks` TINYINT( 1 );
");

$installer->run("
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `masks_cat_id` varchar(255);
");

$installer->run("
    ALTER TABLE {$this->getTable('catalog/product_option_aitimage')} ADD `mask_location` int(1) NOT NULL DEFAULT '0';
");
$installer->run("
    ALTER TABLE `{$this->getTable('aitcg/mask')}` ADD FOREIGN KEY ( `category_id` ) REFERENCES `{$this->getTable('aitcg/mask_category')}` (
        `id`
    ) ON DELETE CASCADE ON UPDATE CASCADE ;
");
$installer->run("
    ALTER TABLE `{$this->getTable('aitcg/mask_created')}` ADD FOREIGN KEY ( `mask_id` ) REFERENCES `{$this->getTable('aitcg/mask')}` (
    `id`
    ) ON DELETE CASCADE ON UPDATE CASCADE ;
");
    
$installer->run("
    INSERT INTO `{$this->getTable('aitcg/mask_category')}` (`id`, `name`) VALUES
    (1, 'Default custom shapes');
");
    
$installer->run("
    
    INSERT INTO `{$this->getTable('aitcg/mask')}` (`id`, `category_id`, `resize`, `name`, `filename`) VALUES
    (1, 1, 1, 'mask1', 'm01.PNG'),
    (2, 1, 1, 'mask2', 'm2.png'),
    (3, 1, 1, 'mask3', 'm3.png'),
    (4, 1, 1, 'mask4', 'm4.png'),
    (5, 1, 1, 'mask5', 'm5.png'),
    (6, 1, 1, 'mask6', 'm06.png'),
    (7, 1, 1, 'mask7', 'm07.PNG'),
    (8, 1, 1, 'mask8', 'm08.PNG'),
    (9, 1, 1, 'mask9', 'm09.PNG'),
    (10, 1, 1, 'mask10', 'm10.PNG'),
    (11, 1, 1, 'mask11', 'm11.PNG'),
    (12, 1, 1, 'mask12', 'm12.PNG'),
    (13, 1, 1, 'mask13', 'm13.PNG'),
    (14, 1, 1, 'mask14', 'm14.PNG'),
    (15, 1, 1, 'mask15', 'm15.PNG'),
    (16, 1, 1, 'mask16', 'm16.PNG'),
    (17, 1, 1, 'mask17', 'm17.PNG'),
    (18, 1, 1, 'mask18', 'm18.PNG'),
    (19, 1, 1, 'mask19', 'm19.PNG'),
    (20, 1, 1, 'mask20', 'm20.PNG'),
    (21, 1, 1, 'mask21', 'm21.PNG');
");
$installer->endSetup(); 