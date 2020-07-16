<?php
/**
 * Magento
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
 * @package   FME_Fmebase
 * @author    Malik Tahir Mehmood <support@fmeextension.com>
 * @copyright 2016 XFME Extensions (https://www.fmeextensions.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link      https://www.fmeextensions.com
 */

/**
 * Class FME_Fmebase_Block_System_Config_Form_Fme_Contact
 *
 * @category FME
 * @package  FME_Fmebase
 * @author   Altaf Ahmed <support@fmeextension.com>
 * @license  http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link     https://www.fmeextensions.com
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(
    "
                
ALTER TABLE `{$this->getTable('photogallery')}`  
ADD COLUMN `parent_gallery_id` INT(11) NOT NULL AFTER `gal_name`;
ALTER TABLE `{$this->getTable('photogallery_images')}`  
ADD COLUMN `alt_text` varchar(255) DEFAULT NULL;
ALTER TABLE `{$this->getTable('photogalleryblocks_images')}`  
ADD COLUMN `alt_text` varchar(255) DEFAULT NULL;
ALTER TABLE `{$this->getTable('photogalleryblocks_images')}`  
ADD COLUMN `img_description` text;

DROP TABLE IF EXISTS `{$this->getTable('gallery')}`;
CREATE TABLE `{$this->getTable('gallery')}` (
          `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `gallery_title` varchar(255) NOT NULL,
		  `main_images` varchar(255) DEFAULT NULL,
		  `gallery_identifier` varchar(255) DEFAULT NULL,
		  `status` int(2) NOT NULL,
		  `store_id` text,
		  `gallery_description` text,
		  `meta_title` varchar(100) DEFAULT NULL,
		  `meta_keywords` text,
		  `meta_description` text,
          PRIMARY KEY (`gallery_id`)                  
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

"
);

/* */


$installer->endSetup();
