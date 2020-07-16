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
 
 
class FME_Photogallery_Block_Photogallery extends Mage_Core_Block_Template
{
    
    protected $_defaultToolbarBlock = 'photogallery/photogallery_toolbar';
        
    public function _prepareLayout()
    {
        if(!Mage::helper('photogallery')->getEnable()) {
         return false;
        }
          parent::_prepareLayout();
 
         $galleryId= $this->getRequest()->getParam('id');
         if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'home', array(
                'label'=>Mage::helper('photogallery')->__('Home'),
                'title'=>Mage::helper('photogallery')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
                )
            );
            $breadcrumbsBlock->addCrumb(
                'photogallery', array(
                'label'=>Mage::helper('photogallery')->__($this->getMainGallery($galleryId)->getGalleryTitle()),
                'title'=>Mage::helper('photogallery')->__($this->getMainGallery($galleryId)->getGalleryTitle())

               
                )
            );
         }
        
          
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle($this->getMainGallery($galleryId)->getMetaTitle());
            $head->setDescription($this->getMainGallery($galleryId)->getMetaDescription());
            $head->setKeywords($this->getMainGallery($galleryId)->getMetaKeywords());
        }
          
        
        // $photogallery = Mage::getModel('photogallery/photogallery')->getCollection()->prepareSummary($galleryId);
        // $this->setToolbar($this->getLayout()->createBlock('photogallery/photogallery_toolbar', 'Toolbar'));		
        // $toolbar = $this->getToolbar();		
        // $this->setToolbar($toolbar);	
        // $toolbar->setCollection($photogallery);
        // $this->setChild('toolbar', $toolbar);

        
       

        return $this;
        
    }

     public function getPhotogallery()     
     { 
        
    if (!$this->hasData('photogallery')) {
            $this->setData('photogallery', Mage::registry('photogallery'));
    }

        return $this->getData('photogallery');
        
     }

    public function getMainGallery($galleryId)
    {
        return Mage::getModel('photogallery/gallery')->load($galleryId);
    }
    
    public function getPhotogalleryImages($photogalleryId)
    {
    $photogalleryImages = Mage::getModel('photogallery/photogalleryblocks')->getCollection()->getImages($photogalleryId);
    return $photogalleryImages;
    }

    public function counterPictures($photogalleryId)
    {
        
    $photogalleryImages = Mage::getModel('photogallery/photogalleryblocks')->getCollection()->getImages($photogalleryId);
    return count($photogalleryImages);
    }
    
    /**************************************************************************
	Retrieve photogallery collection. A protected function in keeping
	with OOP principals

	@Return Mage_Reports_Model_Mysql4_Photogallery_Collection
	*************************************************************************/
    protected function _getPhotogalleryCollection() 
    {
        return $this->getToolbar()->getCollection();
    }

    /**************************************************************************
	Public interface to read toobar object template HTML
	
	@Return String (HTML for Toolbar)
	*************************************************************************/
    public function getToolbarHtml() 
    {
        return $this->getToolbar()->_toHtml();
    }
    
    public function getLoadedPhotogalleryCollection() 
    {
        return $this->_getPhotogalleryCollection();
    }


    public function getAllPhotoGalleryImages()
    {
            $galleryId= $this->getRequest()->getParam('id');
            $store_id=Mage::app()->getStore()->getStoreId();
            $store= array(0 ,$store_id );
            $collection = Mage::getModel('photogallery/img')->getCollection();
            $collection->getSelect()->join(array('pht_item'=> Mage::getConfig()->getTablePrefix().'photogallery'), 'main_table.photogallery_id = pht_item.photogallery_id');
            $collection->getSelect()->join(array('pht_sotre'=> Mage::getConfig()->getTablePrefix().'photogallery_store'), 'main_table.photogallery_id = pht_sotre.photogallery_id');
            $collection->getSelect()->order('main_table.img_order ASC');
            $collection->addFieldToFilter('main_table.disabled', '0');
            $collection->addFieldToFilter('pht_sotre.store_id', array('in' => $store));
            $collection->addFieldToFilter('pht_item.status', 1);
            $collection->addFieldToFilter('pht_item.parent_gallery_id', $galleryId);
            return $collection;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    protected $_pagesCount = null;
    protected $_currentPage = null;
    protected $_itemsOnPage;
    protected $_itemsLimit;
    protected $_pages;
    protected $_displayPages   = 10;

    protected function _construct()
    {
            $this->_currentPage = $this->getRequest()->getParam('page');
            if (!$this->_currentPage) {
            $this->_currentPage=1;
            }

        $itemsPerPage = Mage::helper('photogallery')->getPgImagesperpage();
        if ($itemsPerPage > 0) {
                $this->_itemsOnPage = $itemsPerPage;
        }
    }

    public function getNewsList()
    {
        $collection = $this->getAllPhotoGalleryImages();
        
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
        $_itemsCurrentPage = $this->_itemsLimit - $offset;
        if ($_itemsCurrentPage > $this->_itemsOnPage) {
        $_itemsCurrentPage = $this->_itemsOnPage;
        }

        $collection->getSelect()->limit($_itemsCurrentPage, $offset);
        } else {
        $collection->getSelect()->limit($this->_itemsOnPage, $offset);
        }

        return $collection;

    }

        public function isFirstPage()
        {
            if ($this->_currentPage==1) {
                return true;
            }

                return false;
        }

        public function isLastPage()
        {
            if ($this->_currentPage==$this->_pagesCount) {
                return true;
            }

        return false;
        }

        public function isPageCurrent($page)
        {
            if ($page==$this->_currentPage) {
            return true;
            }

            return false;
        }

        public function getPageUrl($page)
        {
            return $this->getUrl('*', array('page' => $page));
        }

        public function getNextPageUrl()
        {
            $page = $this->_currentPage+1;
            return $this->getPageUrl($page);
        }

        public function getPreviousPageUrl()
        {
                $page = $this->_currentPage-1;
                return $this->getPageUrl($page);
        }

        public function getPages()
        {
                $collection = $this->getAllPhotoGalleryImages();
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

    
}
