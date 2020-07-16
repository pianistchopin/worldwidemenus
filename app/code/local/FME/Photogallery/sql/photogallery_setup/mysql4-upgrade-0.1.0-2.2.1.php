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
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(
    "
                
DROP TABLE IF EXISTS {$this->getTable('photogallery/photogallery_block_gimages')};

DROP TABLE IF EXISTS {$this->getTable('photogalleryblocks_images')};
CREATE TABLE {$this->getTable('photogalleryblocks_images')}  (
  `blockimage_id` int(5) NOT NULL AUTO_INCREMENT,
  `blockimage_name` varchar(255) DEFAULT NULL,
  `blockimage_thumb` varchar(255) DEFAULT NULL,
  `blockimage_label` text,
  `blockimage_order` int(5) DEFAULT NULL,
  `disabled` int(5) DEFAULT '0',
  `photogallery_block_id` int(5) DEFAULT NULL,
  PRIMARY KEY (`blockimage_id`),
  KEY `FK_GALLERY_BLOCK_IMAGES` (`photogallery_block_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8

"
);



$installer->endSetup();
