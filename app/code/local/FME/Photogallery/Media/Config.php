<?php
/**
 * Gallery extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Gallery
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

/**
 * @category   Gallery
 * @package    Gallery
 * @author     Shahzad Hussain <shehzad.cs@gmail.com>
 */
class FME_Photogallery_Media_Config extends Mage_Catalog_Model_Product_Media_Config
{

    public function getBaseMediaPath()
    {
        return Mage::getBaseDir('media') . DS . 'gallery/galleryimages';
    }

    public function getBaseMediaUrl()
    {
        return Mage::getBaseUrl('media') . 'gallery/galleryimages';
    }

    public function getBaseTmpMediaPath()
    {
        return Mage::getBaseDir('media') . DS . 'gallery/galleryimages';
    }

    public function getBaseTmpMediaUrl()
    {
        return Mage::getBaseUrl('media') . 'gallery/galleryimages';
    }
     
}