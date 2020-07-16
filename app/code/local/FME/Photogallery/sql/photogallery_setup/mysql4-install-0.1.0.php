<?php
/**
 * Photo Photogallery & Products Photogallery extension
 *
 * NOTICE OF LICENSE
 *
 * PHP version 5
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category  FME
 * @package   Photo Photogallery & Products Photogallery extension
 * @author    Altaf Ahmed <support@fmeextension.com>
 * @copyright 2016 XFME Extensions (https://www.fmeextensions.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link      https://www.fmeextensions.com
 */

/**
 * Class FME_Fmebase_Block_System_Config_Form_Fme_Contact
 *
 * @category FME
 * @package  Photo Photogallery & Products Photogallery extension
 * @author   Kamran Rafiq Malik <support@fmeextension.com>
 * @license  http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link     https://www.fmeextensions.com
 */
 

$installer = $this;

$installer->startSetup();

$installer->run(
    "

DROP TABLE IF EXISTS {$this->getTable('photogallery')};
CREATE TABLE {$this->getTable('photogallery')} (
   `photogallery_id` int(11) unsigned NOT NULL auto_increment,  
   `gal_name` varchar(255) NOT NULL default '',
   `gorder` int(11) default NULL,                          
   `description` text NOT NULL,
   `show_in` tinyint(1) NOT NULL default '3',
   `gdate` date default NULL,                               
   `status` tinyint(1) NOT NULL default '0',               
   `value` varchar(255) default NULL,                      
   `grid_thumb` text,                                      
   `meta_keywords` text,                                   
   `meta_description` text,                                
   `created_time` datetime default NULL,                   
   `update_time` datetime default NULL,                    
   PRIMARY KEY  USING BTREE (`photogallery_id`)                 
 ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('photogallery_store')};
CREATE TABLE {$this->getTable('photogallery_store')} (
 `photogallery_id` int(11) unsigned NOT NULL,                      
 `store_id` smallint(5) unsigned NOT NULL,                    
 PRIMARY KEY  (`photogallery_id`,`store_id`),                      
 KEY `FK_GALLERY_STORE_STORE` (`store_id`)                    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   
DROP TABLE IF EXISTS {$this->getTable('photogallery_products')};
CREATE TABLE {$this->getTable('photogallery_products')} (                                
	`photogallery_id` int(11) NOT NULL,                                 
	`product_id` smallint(5) unsigned NOT NULL,                                                      
	PRIMARY KEY  (`photogallery_id`,`product_id`),                      
	KEY `FK_GALLERY_PRODUCTS` (`product_id`)                       
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Photogallery Products';


DROP TABLE IF EXISTS {$this->getTable('photogallery_blocks')};
CREATE TABLE {$this->getTable('photogallery_blocks')} (
   `photogallery_block_id` int(11) NOT NULL auto_increment,   
   `block_title` varchar(255) default '',                     
   `block_content` text,                                      
   `block_identifier` varchar(255) default NULL,              
   `block_status` smallint(6) default '0',
   `slider` varchar(144) default NULL,
   `direction` varchar(144) default NULL,
   `easing` varbinary(144) default NULL,
   `duration` int(11) default '0',              
   `interval` int(11) default '0',              
   `mainwidth` int(11) default '0',              
   `mainheight` int(11) default '0',               
   `autoplay` smallint(6) default '0',              
   `enablecontent` smallint(6) default '1',          
   `enabletitle` smallint(6) default '1',           
   PRIMARY KEY  (`photogallery_block_id`)                     
 ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('photogallery_block_gimages')};
CREATE TABLE {$this->getTable('photogallery_block_gimages')} (                       
  `photogallery_block_id` int(11) NOT NULL,                         
  `img_id` int(11) NOT NULL,                     
  PRIMARY KEY  (`photogallery_block_id`,`img_id`),      
  KEY `FK_CATEGORYBANNERS_CATEGORIES` (`photogallery_block_id`)     
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('photogallery_images')};
CREATE TABLE {$this->getTable('photogallery_images')} (                       
 `img_id` int(5) NOT NULL AUTO_INCREMENT,
  `img_name` varchar(255) DEFAULT NULL,
  `img_thumb` varchar(255) DEFAULT NULL,
  `img_label` text,
  `photogallery_id` int(5) DEFAULT NULL,
  `img_order` int(5) DEFAULT NULL,
  `disabled` int(5) DEFAULT '0',
  `img_description` varchar(255) NOT NULL DEFAULT '',                     
PRIMARY KEY  (`img_id`)                               
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;  


 "
);

//photogallery settings
$installer->setConfigData('photogallery/photogallery/enabled', '1');
$installer->setConfigData('photogallery/photogallery/imagesperpage', '12');
$installer->setConfigData('photogallery/photogallery/photogallerywidth', '900');
$installer->setConfigData('photogallery/photogallery/thumbwidth', '300');
$installer->setConfigData('photogallery/photogallery/thumbheight', '300');
$installer->setConfigData('photogallery/photogallery/keepaspectratio', '1');
$installer->setConfigData('photogallery/photogallery/keepframe', '1');
$installer->setConfigData('photogallery/photogallery/thumbbackgroundColor', '255,255,255');


//General Settings
$installer->setConfigData('photogallery/list/page_title', 'Photo Gallery');
$installer->setConfigData('photogallery/list/identifier', 'photogallery');
$installer->setConfigData('photogallery/list/seourl_suffix', '.html');
$installer->setConfigData('photogallery/list/identifier', 'photogallery');
$installer->setConfigData('photogallery/list/meta_keywords', 'Photo Gallery');
$installer->setConfigData('photogallery/list/meta_description', 'Photo Gallery');
$installer->setConfigData('photogallery/photogallery/pagertext', 'Load More');


$installer->endSetup(); 
