<?php
/**
 * Media Photogallery & Product Videos extension
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
 **/

class FME_Photogallery_Model_Photogalleryblocks extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('photogallery/photogalleryblocks');
    }
    
    /**
     * Retrieve related articles
     *
     * @return array
     */
    public function getRelatedPhotogallery($blockId)
    {
        $photogallerygimageblockTable = Mage::getSingleton('core/resource')->getTableName('photogallery_block_gimages');
        $photogalleryTable = Mage::getSingleton('core/resource')->getTableName('photogallery_images');
        $collection = Mage::getModel('photogallery/photogalleryblocks')->getCollection()
                ->addBlockIdFilter($blockId);
        
        $collection->getSelect()
            ->joinLeft(array('related' => $photogallerygimageblockTable), 'main_table.photogallery_block_id = related.photogallery_block_id')
            ->joinLeft(array('photogallery' => $photogalleryTable), 'related.img_id = photogallery.img_id')
        ->order('photogallery.img_id');
        
        return $collection->getData();

    }
    
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }
}
