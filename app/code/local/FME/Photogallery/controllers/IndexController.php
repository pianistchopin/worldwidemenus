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
 
 
class FME_Photogallery_IndexController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();
        if(!Mage::helper('photogallery')->getEnable()) {
         Mage::getSingleton('core/session')->addError(Mage::helper('photogallery')->__('Sorry This Feature is disabled temporarily'));
         $this->norouteAction();
        }
    }

    public function indexAction()
    {
            
        $this->loadLayout();  
        if($id = $this->getRequest()->getParam('id')):
            $loadGallery=Mage::getModel('photogallery/gallery')->load($id);
            $this->getLayout()->getBlock('head')->setTitle($loadGallery->getMetaTitle());
            $this->getLayout()->getBlock('head')->setDescription($loadGallery->getMetaDescription());
            $this->getLayout()->getBlock('head')->setKeywords($loadGallery->getMetaKeywords()); 
        else:
            $this->getLayout()->getBlock('head')->setTitle(Mage::helper('photogallery')->getListPageTitle());
            $this->getLayout()->getBlock('head')->setDescription(Mage::helper('photogallery')->getListMetaDescription());
            $this->getLayout()->getBlock('head')->setKeywords(Mage::helper('photogallery')->getListMetaKeywords()); 
        endif;

        $this->renderLayout();
    }

   
 
    public function ajaxPaginationAction()
    {
        $_pagesCount = null;
        $_itemsOnPage =  Mage::helper('photogallery')->getPgImagesperpage();
        $_itemsLimit =null;
        $_pages=5;
        $imagePath = Mage::getBaseUrl('media')."gallery/galleryimages";
        $_currentPage = $this->getRequest()->getParam('page');
        $_pages = $this->getRequest()->getParam('pages');
        $galleryId=$this->getRequest()->getParam('galleryId');

        $_page = $_currentPage +1;
        $_block_num = $_currentPage - 1;
        $collection = Mage::getModel('photogallery/img')->getCollection();
        $collection->getSelect()->join(array('pht_item'=> Mage::getConfig()->getTablePrefix().'photogallery'), 'main_table.photogallery_id = pht_item.photogallery_id');
        $collection->getSelect()->order('main_table.img_order ASC');
        $collection->addFieldToFilter('disabled');
        $collection->addFieldToFilter('pht_item.parent_gallery_id', $galleryId);

        if ($_itemsLimit!=null && $_itemsLimit<$collection->getSize()) {
        $_pagesCount = ceil($_itemsLimit/$_itemsOnPage);
        } else {
        $_pagesCount = ceil($collection->getSize()/$_itemsOnPage);
        }

        for ($i=1; $i<=$_pagesCount;$i++) {
        $this->_pages[] = $i;
        }

      //  Mage_Catalog_Block_Seo_Sitemap_Tree_Pager::setLastPageNum($_pagesCount);

        $offset = $_itemsOnPage*($_currentPage-1);
        if ($_itemsLimit!=NULL) {
        $_itemsCurrentPage = $_itemsLimit - $offset;
        if ($_itemsCurrentPage > $_itemsOnPage) {
        $_itemsCurrentPage = $_itemsOnPage;
        }

        $collection->getSelect()->limit($_itemsCurrentPage, $offset);
        } else {
        $collection->getSelect()->limit($_itemsOnPage, $offset);
        }
      
        $html = array();
        $html = "<div class='cbp-loadMore-block".$_block_num."'>";
        foreach ($collection as $_gimage){
            $ph_id  = $_gimage['img_id'];
            $targetPath = $imagePath.$_gimage["img_name"];
            $thumbPath = Mage::helper('photogallery')->getThumbsDirPath($targetPath);
            $arrayName = explode('/', $_gimage["img_name"]);
            $gallery_name = $_gimage['gal_name'];
            $thumbnail_path =  $thumbPath . $arrayName[3];
            $image_path = $imagePath . $_gimage["img_name"];
            $description = $_gimage["description"];
            $picture_name = $_gimage["img_label"];
            $picture_descrip = $_gimage["img_description"];
       
            list($width, $height, $type, $attr) = getimagesize($thumbnail_path); 
       
       
            $html .= "<li class= '".$_gimage['photogallery_id']." cbp-item' >";
            $html .=  "<div class='cbp-caption'>";
            $html .= "<div class='cbp-caption-defaultWrap'>";
            $html .= "<img src='$thumbnail_path' alt='' ></div>";
            $html .= "<div class='cbp-caption-activeWrap' style=\"width:".$width."px \">";
            $html .= "<div class='cbp-l-caption-alignCenter'>";
            $html .= "<div class='cbp-l-caption-body'>";
            $html .= "<a href='$image_path' class='cbp-lightbox cbp-l-caption-buttonRight' data-title='$picture_name'>View larger</a>";
            $html .= "</div>";
            $html .= "</div>" ;                       
            $html .= "</div>" ;                  
            $html .= "</div>"  ;           
            $html .= "<div class='cbp-l-grid-projects-title'>$picture_name</div>";
            $html .= "<div class='cbp-l-grid-projects-desc'>$picture_descrip</div>";
            $html .= "</li>";
        }

        $html .= "</div>";
        
        
        $collection->addFieldToFilter('img_id', array('from'>$ph_id));
        if ($_currentPage<$_pages) {
            $html .= "<input type='hidden' value='".Mage::getUrl(
                'photogallery/index/ajaxPagination',
                array('page' => $_page,'pages'=>$_pages,'galleryId'=>$galleryId)
            )."' id='next_pages'/>";
            $html .= "<div class='cbp-loadMore-block".$_currentPage."'>";
            foreach ($collection as $_gimage) {
                $ph_id  = $_gimage['img_id'];
                $targetPath = $imagePath.$_gimage["img_name"];
                $thumbPath = Mage::helper('photogallery')->getThumbsDirPath($targetPath);
                $arrayName = explode('/', $_gimage["img_name"]);
                $gallery_name = $_gimage['gal_name'];
                $thumbnail_path =  $thumbPath . $arrayName[3];
                $image_path = $imagePath . $_gimage["img_name"];
                $description = $_gimage["description"];
                $picture_name = $_gimage["img_label"];
                $picture_descrip = $_gimage["img_description"];
            
                list($width, $height, $type, $attr) = getimagesize($thumbnail_path);
                
                $html .= "<li class= '".$_gimage['photogallery_id']." cbp-item' >";
                $html .=  "<div class='cbp-caption'>";
                $html .= "<div class='cbp-caption-defaultWrap'>";
                $html .= "<img src='$thumbnail_path' alt='' ></div>";
                $html .= "<div class='cbp-caption-activeWrap' style=\"width:".$width."px \">";
                $html .= "<div class='cbp-l-caption-alignCenter'>";
                $html .= "<div class='cbp-l-caption-body'>";
                $html .= "<a href='$image_path' class='cbp-lightbox cbp-l-caption-buttonRight'  data-title='$picture_name'>View larger</a>";
                $html .= "</div>";
                $html .= "</div>" ;                       
                $html .= "</div>" ;                  
                $html .= "</div>"  ;           
                $html .= "<div class='cbp-l-grid-projects-title'>$picture_name</div>";
                $html .= "<div class='cbp-l-grid-projects-desc'>$picture_descrip</div>";
                $html .= "</li>";
            }

        $html .= "</div>";
        }

               $this->getResponse()->setBody($html);

    }

     public function ajaxBlockPaginationAction()
     {
            $_pagesCount = null;
            $_itemsOnPage =  Mage::helper('photogallery')->getPgImagesperpage();
            $_itemsLimit;
            $_pages=5;
        $imagePath = Mage::getBaseUrl('media')."gallery/galleryimages";
        $_currentPage = $this->getRequest()->getParam('page');
        $_pages = $this->getRequest()->getParam('pages');
        $blockId=$this->getRequest()->getParam('blockId');

        $_page = $_currentPage +1;
        $_block_num = $_currentPage - 1;
       $collection = Mage::getModel('photogallery/blockimage')->getCollection()
            ->addFieldToFilter('main_table.photogallery_block_id', $blockId)
            ->addFieldToFilter('main_table.disabled', '0')
            ->setOrder('main_table.blockimage_order', 'ASC');

        if ($_itemsLimit!=null && $_itemsLimit<$collection->getSize()) {
        $_pagesCount = ceil($_itemsLimit/$_itemsOnPage);
        } else {
        $_pagesCount = ceil($collection->getSize()/$_itemsOnPage);
        }

        for ($i=1; $i<=$_pagesCount;$i++) {
        $this->_pages[] = $i;
        }

        Mage_Catalog_Block_Seo_Sitemap_Tree_Pager::setLastPageNum($_pagesCount);

        $offset = $_itemsOnPage*($_currentPage-1);
        if ($_itemsLimit!=NULL) {
        $_itemsCurrentPage = $_itemsLimit - $offset;
        if ($_itemsCurrentPage > $_itemsOnPage) {
        $_itemsCurrentPage = $_itemsOnPage;
        }

        $collection->getSelect()->limit($_itemsCurrentPage, $offset);
        } else {
        $collection->getSelect()->limit($_itemsOnPage, $offset);
        }
      
        $html = array();
         $html = "<div class='cbp-loadMore-block".$_block_num."'>";
         foreach ($collection as $_gimage){
                    $ph_id  = $_gimage['img_id'];
                    $targetPath = $imagePath.$_gimage["blockimage_name"];
                    $thumbPath = Mage::helper('photogallery')->getThumbsDirPath($targetPath);
                    $arrayName = explode('/', $_gimage["blockimage_name"]);
                    $gallery_name = $_gimage['blockimage_label'];
                    $thumbnail_path =  $thumbPath . $arrayName[3];
                    $image_path = $imagePath . $_gimage["blockimage_name"];
                    $description = $_gimage["description"];
                    $picture_name = $_gimage["blockimage_label"];
                    $picture_descrip = $_gimage["img_description"];
       
            list($width, $height, $type, $attr) = getimagesize($thumbnail_path); 
       
       
            $html .= "<li class= '".$_gimage['photogallery_id']." cbp-item' >";
            $html .=  "<div class='cbp-caption'>";
            $html .= "<div class='cbp-caption-defaultWrap'>";
            $html .= "<img src='$thumbnail_path' alt='' ></div>";
            $html .= "<div class='cbp-caption-activeWrap' style=\"width:".$width."px \">";
            $html .= "<div class='cbp-l-caption-alignCenter'>";
            $html .= "<div class='cbp-l-caption-body'>";
            $html .= "<a href='$image_path' class='cbp-lightbox cbp-l-caption-buttonRight' data-title='$picture_name'>View larger</a>";
            $html .= "</div>";
            $html .= "</div>" ;                       
            $html .= "</div>" ;                  
            $html .= "</div>"  ;           
            $html .= "<div class='cbp-l-grid-projects-title'>$picture_name</div>";
            $html .= "<div class='cbp-l-grid-projects-desc'>$picture_descrip</div>";
            $html .= "</li>";
         }

        $html .= "</div>";
        
        
       $collection->addFieldToFilter('img_id', array('from'>$ph_id));
       if($_currentPage<$_pages)
       {
        $html .= "<input type='hidden' value='".Mage::getUrl('photogallery/index/ajaxBlockPagination', array('page' => $_page,'pages'=>$_pages,'blockId'=>$blockId))."' id='next_pages'/>";
        $html .= "<div class='cbp-loadMore-block".$_currentPage."'>";
        foreach ($collection as $_gimage){
                    $ph_id  = $_gimage['img_id'];
                    $targetPath = $imagePath.$_gimage["img_name"];
                    $thumbPath = Mage::helper('photogallery')->getThumbsDirPath($targetPath);
                    $arrayName = explode('/', $_gimage["img_name"]);
                    $gallery_name = $_gimage['gal_name'];
                    $thumbnail_path =  $thumbPath . $arrayName[3];
                    $image_path = $imagePath . $_gimage["img_name"];
                    $description = $_gimage["description"];
                    $picture_name = $_gimage["img_label"];
                    $picture_descrip = $_gimage["img_description"];
            
            list($width, $height, $type, $attr) = getimagesize($thumbnail_path);
            
            $html .= "<li class= '".$_gimage['photogallery_id']." cbp-item' >";
            $html .=  "<div class='cbp-caption'>";
            $html .= "<div class='cbp-caption-defaultWrap'>";
            $html .= "<img src='$thumbnail_path' alt='' ></div>";
            $html .= "<div class='cbp-caption-activeWrap' style=\"width:".$width."px \">";
            $html .= "<div class='cbp-l-caption-alignCenter'>";
            $html .= "<div class='cbp-l-caption-body'>";
            $html .= "<a href='$image_path' class='cbp-lightbox cbp-l-caption-buttonRight' data-title='$picture_name'>View larger</a>";
            $html .= "</div>";
            $html .= "</div>" ;                       
            $html .= "</div>" ;                  
            $html .= "</div>"  ;           
            $html .= "<div class='cbp-l-grid-projects-title'>$picture_name</div>";
            $html .= "<div class='cbp-l-grid-projects-desc'>$picture_descrip</div>";
            $html .= "</li>";
        }

        $html .= "</div>";
       }

         $this->getResponse()->setBody($html);
     }
}
