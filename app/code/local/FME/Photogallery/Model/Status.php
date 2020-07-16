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
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */
 
 

class FME_Photogallery_Model_Status extends Varien_Object
{
    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED    = 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('photogallery')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('photogallery')->__('Disabled')
        );
    }
    
    static public function getOptionArrayimage()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('gimage')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('gimage')->__('Disabled')
        );
    }
}
