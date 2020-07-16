<?php
/**
 * Media Photogallery & Product Gimages extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME  
 * @package    Mediaappearance
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 **/ 
class FME_Photogallery_Block_Photogalleryblock extends Mage_Core_Block_Template
{
    const BANNER_TYPE = 'photogallery/photogalleryblock/slider';
    
    protected $ActiveBlock=array();
    protected $_pagesCount = null;
    protected $_currentPage = null;
    protected $_itemsOnPage;
    protected $_itemsLimit;
    protected $_pages;
    protected $_displayPages   = 10;
    // protected $ImageCount=0;
 //    protected function _construct()
    // {
    // 		$this->_currentPage = $this->getRequest()->getParam('page');
    // 		if (!$this->_currentPage) {
    // 		$this->_currentPage=1;
    // 		}

    // 	$itemsPerPage = Mage::helper('photogallery')->getPgImagesperpage();
    // 	if ($itemsPerPage > 0) {
    // 			$this->_itemsOnPage = $itemsPerPage;
    // 	}
    // }
    
    protected function _tohtml()
    {
        if(!Mage::helper('photogallery')->getEnable()) {
         return false;
        }
        
    $bannerType = Mage::getStoreConfig(self::BANNER_TYPE);
    $this->setActiveBlock();
    if($this->ActiveBlock){
        $bannerType = $this->ActiveBlock['slider'];
               
        $this->setTemplate("photogallery/photogalleryblock.phtml");//with thumbnails


        $this->_currentPage = $this->getRequest()->getParam('page');
            if (!$this->_currentPage) {
            $this->_currentPage=1;
            }

        $itemsPerPage = Mage::helper('photogallery')->getPgImagesperpage();
        if ($itemsPerPage > 0) {
                $this->_itemsOnPage = $itemsPerPage;
        }
    }

    return parent::_toHtml();
    }
    
    public function setActiveBlock()
    {
    $blockId = $this->getBlockIdentifier();
    $collection = Mage::getModel('photogallery/photogalleryblocks')->getCollection()
            ->addFieldToFilter('main_table.block_identifier', $blockId)
            ->addFieldToFilter('main_table.block_status', '1')
            ->getData();
    if(count($collection)){
        $this->ActiveBlock=$collection[0];
    }
    
    }
    
     
    public function getActiveBlock()
    {
    return $this->ActiveBlock;
    }
    
    public function getBlockImages($blockId)
    {

    $itemsCurrentPage=Mage::getStoreConfig('photogallery/photogallery/imagesperpage');

    //$collection = Mage::getModel('photogallery/photogalleryblocks')->getCollection()
    //->addFieldToFilter('main_table.block_identifier', $blockId)
    //->getData();
    //return $collection;
    $collection = Mage::getModel('photogallery/blockimage')->getCollection()
    ->addFieldToFilter('main_table.photogallery_block_id', $blockId)
    ->addFieldToFilter('main_table.disabled', '0')
    ->setOrder('main_table.blockimage_order', 'ASC');
    //->getData();

    $this->setImageCount($collection->getSize());
    if ($this->_itemsLimit!=null && $this->_itemsLimit<$collection->getSize()) {
        $this->_pagesCount = ceil($this->_itemsLimit/$this->_itemsOnPage);
    } else {
        $this->_pagesCount = ceil($collection->getSize()/$this->_itemsOnPage);
    }

        for ($i=1; $i<=$this->_pagesCount;$i++) {
        $this->_pages[] = $i;
        }

        $this->setLastPageNum($this->_pagesCount);

        $offset = $this->_itemsOnPage*($this->_currentPage-1);
        if ($this->_itemsLimit!=NULL) {
        $_itemsCurrentPage = $this->_itemsOnPage;
        if ($_itemsCurrentPage > $this->_itemsOnPage) {
        $_itemsCurrentPage = $this->_itemsOnPage;
        }

        $collection->getSelect()->limit($_itemsCurrentPage, $offset);
        } else {
        $collection->getSelect()->limit($this->_itemsOnPage, $offset);
        }

        // echo $this->getLastPageNum(); exit();
    // $collection->getSelect()->limit($itemsCurrentPage);
    //echo $collection->getSelect(); exit();
    return $collection;
    }
    
    public function isPageCurrent($page)
    {
            if ($page==$this->_currentPage) {
            return true;
            }

            return false;
    }
    
    
    public function getBlockRelatedimages($relatedGimageIds)      
    {
        $bimages = Mage::getModel('photogallery/photogalleryblocks')->getCollection()->getBlockImages($relatedGimageIds);
        return $bimages;
    }

    public function getPages()
    {
                $collection = Mage::getModel('photogallery/blockimage')->getCollection()
    ->addFieldToFilter('main_table.photogallery_block_id', $blockId)
    ->addFieldToFilter('main_table.disabled', '0')
    ->setOrder('main_table.blockimage_order', 'ASC');
                $pages = array();
                if ($this->_pagesCount <= $this->_displayPages) {
                $pages = range(1, $this->_pagesCount);
                }
            else {
                    $half = ceil($this->_displayPages / 2);
                    if ($this->_currentPage >= $half && $this->_currentPage <= $this->_pagesCount - $half) {
                    $start  = ($this->_currentPage - $half) + 1;
                    $finish = ($start + $this->_displayPages) - 1;
                    }
                elseif ($this->_currentPage < $half) {
                    $start  = 1;
                    $finish = $this->_displayPages;
                }
                elseif ($this->_currentPage > ($this->_pagesCount - $half)) {
                        $finish = $this->_pagesCount;
                        $start  = $finish - $this->_displayPages + 1;
                }

                $pages = range($start, $finish);
            }
        
                return $pages;
                //return $this->_pages;
    }
    
    
    
    public function getPhotogalleryblockWidth() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/mainwidth");
    }
    
    public function getPhotogalleryblockHeight() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/mainheight");
    }
    
    public function getPhotogalleryblockInterval() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/interval");
    }
    
    public function getPhotogalleryblockDirection() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/direction");
    }
    
    public function getPhotogalleryblockEasing() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/easing");
    }
    
    public function getPhotogalleryblockDuration() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/duration");
    }
    
    public function getPhotogalleryblockAuto() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/autoplay");
    }
    
    public function getContentFlag() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/enablecontent");
    }
    
    public function getTitleFlag() 
    {
        return Mage::getStoreConfig("photogallery/photogalleryblock/enabletitle");
    }
    
}