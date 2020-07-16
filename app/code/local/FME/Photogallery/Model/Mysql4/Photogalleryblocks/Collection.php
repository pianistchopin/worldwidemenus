<?php
/**
 * Faqs extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php

 * @category   FME
 * @package    Faqs
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Photogallery_Model_Mysql4_Photogalleryblocks_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('photogallery/photogalleryblocks');
    }
    
        public function getAllImages()
        {
            $this->setConnection($this->getResource()->getReadConnection());
            $this->getSelect()
                ->from(array('images_table'=>Mage::getSingleton('core/resource')->getTableName('photogallery_images')), '*')
                ->order('img_order', 'asc');
            return $this;
        }
    
    public function addAttributeToFilter($attribute, $condition=null, $joinType='inner')
    {
        switch($attribute) {
            case 'status':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                return $this;
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
        }

        return $this;
    }
    
    public function addBlockIdFilter($id = 0)
    {
        $this->getSelect()
            ->where('related.photogallery_block_id=?', (int)$id);

        return $this;
    }
    
    public function addBlockIdentiferFilter($identifier = '')
    {
        $this->getSelect()
            ->where('main_table.block_identifier=?', $identifier);

        return $this;
    }
    
    /**
     * Retrieve Related Images ids
     *
     * @return array
     */
    public function getRelatedImagesIds($Id)
    {
    $this->setConnection($this->getResource()->getReadConnection());
    $this->getSelect()
        ->from(array('g' => Mage::getSingleton('core/resource')->getTableName('photogallery_blocks')), 'g.related_photogallery')
        ->where('g.photogallery_block_id', $Id);
    return $this;
    }
    
    /**
     * Retrieve Product Galleries
     *
     * @return array
     */
    public function getBlockImages($relatedGimageIds)
    {
    $this->setConnection($this->getResource()->getReadConnection());
    $this->getSelect()
        ->from(array('g' => Mage::getSingleton('core/resource')->getTableName('photogallery_images')), '*')
        ->where('g.img_id IN (?)', $relatedGimageIds)
        ->order('g.img_order', 'ASC');
    return $this;
    }
    
    //get product images
    public function getPimages($photogalleryIds)
    {
    
    $galleries = explode(",", $photogalleryIds);
    $this->setConnection($this->getResource()->getReadConnection());
    $this->getSelect()
        ->from(array('g' => Mage::getSingleton('core/resource')->getTableName('photogallery/img')), 'g.*')
        ->where('g.photogallery_id IN (?)', $galleries)
        ->where('g.disabled= (?)', '0')
        ->order('g.img_order', 'ASC');
    return $this;
    }
    
    /*start Gallery Images shahzad*/
    /**
     * Retrieve Gallery Images
     *
     * @return array
     */
    public function getImages($galleryId)
    {
    $this->setConnection($this->getResource()->getReadConnection());
    $this->getSelect()
        ->from(array('main_table'=>Mage::getSingleton('core/resource')->getTableName('photogallery_images')), '*')
        ->where('photogallery_id = ?', $galleryId)
        ->order('img_order', 'ASC');
    return $this;
    }
    /*end Gallery Images shahzad*/
    
    
}
