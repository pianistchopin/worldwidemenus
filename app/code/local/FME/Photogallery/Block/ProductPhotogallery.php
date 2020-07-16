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

class FME_Photogallery_Block_ProductPhotogallery extends Mage_Catalog_Block_Product_Abstract
{

    const DISPLAY_CONTROLS = 'photogallery/photogallery/enabled';

    protected function _tohtml()
    {
        if(!Mage::helper('photogallery')->getEnable()) {
         return false;
        }
     
         if ($this->getFromXml()=='yes'&&!Mage::getStoreConfig(self::DISPLAY_CONTROLS)){
             return parent::_toHtml();
         }
           

        $this->setLinksforProduct();
        $this->setTemplate("photogallery/photogallery2product.phtml");
        return parent::_toHtml();
    }
    
    public function getProductGalleries($productId)
    {
    $pgalleries = Mage::getModel('photogallery/photogallery')->getCollection()->getPgalleries($productId);
    return $pgalleries->getData();
    }
    
    public function getProductGimages($photogalleryIds)
    {
    $photogalleryImages = Mage::getModel('photogallery/photogalleryblocks')->getCollection()->getPimages($photogalleryIds);
    return $photogalleryImages->getData();
    }
    
}
