<?php
/**
 * Photo Photogallery & Products Photogallery extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Photogallery
 * @copyright  Copyright 2010 � free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */
 
 

class FME_Photogallery_Model_Image_Config implements Mage_Media_Model_Image_Config_Interface
{
  /**
     * Retrive base url for media files
     *
     * @return string
     */
    public function getBaseMediaUrl()
    {
      return Mage::getBaseUrl('media') . 'photogallery/files' ;
      
    }

    /**
     * Retrive base path for media files
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
      return BP . DS . 'media' . DS . 'photogallery/files' . DS;
      
    }

    /**
     * Retrive url for media file
     *
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
    
      $aryfile = explode("/", $file);
          
      return Mage::getBaseUrl('media') . 'photogallery' . DS . 'files' . DS . $file;
    }

    /**
     * Retrive file system path for media file
     *
     * @param string $file
     * @return string
     */
    public function getMediaPath($file)
    {
    
      $aryfile = explode("/", $file);
      return BP . DS . 'media' . DS . 'photogallery' . DS . 'files' . DS . $file;
    }
} 
