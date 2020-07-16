<?php
/**
 * Banners extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Banners
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */
 
class FME_Photogallery_Model_Bannertypes
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'slider1',   'label'=>Mage::helper('adminhtml')->__('Banners With Thumbnails Navigation')),
            array('value'=>'slider2',   'label'=>Mage::helper('adminhtml')->__('Banners With Number Navigation')),
            array('value'=>'slider3',   'label'=>Mage::helper('adminhtml')->__('Banners Without Navigation')),
        );
    }
} 
