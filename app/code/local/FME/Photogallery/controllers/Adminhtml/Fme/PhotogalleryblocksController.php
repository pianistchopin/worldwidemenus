<?php
/**
 * Photogallery Photogallery & Product Videos extension
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
 **/

class FME_Photogallery_Adminhtml_Fme_PhotogalleryblocksController extends Mage_Adminhtml_Controller_Action
{
  
    protected function _initAction() 
    {
        $this->loadLayout()
            ->_setActiveMenu('FME_Extensions')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Photogallery Blocks'), Mage::helper('adminhtml')->__('Manage Photogallery Blocks'));
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        //exit;
        return $this;
    }   
 
    public function indexAction() 
    {
        $this->_initAction();        
        $this->renderLayout();
    }
    
    protected function _initPhotogalleryBlockImages() 
    {
        
        $blockId  = (int) $this->getRequest()->getParam('id');
        
        $model_img = Mage::getModel('photogallery/blockimage')->getCollection();
        $model_img->addFieldToFilter('photogallery_block_id', array('in'=>array($blockId)));
        
        Mage::register('photogallery_blockimage', $model_img);
    }
    
    protected function _initPhotogalleryBlock() 
    {
        
        $blockId  = (int) $this->getRequest()->getParam('id');
        if (!$blockId) {
            return false;
        }

        $block = Mage::getModel('photogallery/photogalleryblocks');
        if ($blockId) {
            $block->load($blockId);
            if (!$block->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('This Photogallery is no longer exists'));
            $this->_redirect('*/*/');
            return;
            }
        }

        Mage::register('current_block', $block);
        return $block;
    }
    
    /**
     * Create serializer block for a grid
     *
     * @param string $inputName
     * @param Mage_Adminhtml_Block_Widget_Grid $gridBlock
     * @param array $productsArray
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Ajax_Serializer
     */
    protected function _createSerializerBlock($inputName, Mage_Adminhtml_Block_Widget_Grid $gridBlock, $productsArray)
    {
        return $this->getLayout()->createBlock('adminhtml/photogalleryblocks_edit_tab_ajax_serializer')
        ->setGridBlock($gridBlock)
        ->setProducts($productsArray)
        ->setInputElementName($inputName);
    }
 
    
    public function editAction() 
    {
        
        $this->_initPhotogalleryBlockImages();
        $this->loadLayout();
        $this->_setActiveMenu('FME_Extensions');
        $this->_addBreadcrumb(Mage::helper('photogallery')->__('Manage Photogallery Blocks'), Mage::helper('photogallery')->__('Manage Photogallery Blocks'));
        
        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            $model  = Mage::getModel('photogallery/photogalleryblocks')->load($id);
            Mage::register('photogallery_block_data', $model);
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('photogallery/adminhtml_photogalleryblocks_edit'))
             ->_addLeft($this->getLayout()->createBlock('photogallery/adminhtml_photogalleryblocks_edit_tabs'));
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        $this->renderLayout();
    }
 
    public function newAction() 
    {
        $this->_forward('edit');
    }
 
    public function saveAction() 
    {
        try {
        $id=$this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPost();
        
                
        
        if($data['block_identifier'] == "") {
            $data['block_identifier'] = Mage::helper('photogallery')->nameToUrlKey($data['block_title']);
        } else {
            $data['block_identifier'] = Mage::helper('photogallery')->nameToUrlKey($data['block_identifier']);
        }
            
        
        //Save Related Photogallery
        if ($this->getRequest()->getPost('links')) {
            $links = $this->getRequest()->getPost('links');
            $photogalleryIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['relatedphotogallery']);
            
            
            
            //Explode Array into Comma Seprated
            $relatedphotogallery = implode(",", $photogalleryIds);
            
            
            $data['related_photogallery'] = $relatedphotogallery; 
        }
    
        
            $model = Mage::getModel('photogallery/photogalleryblocks');        
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                $model->setCreatedTime(now())
                    ->setUpdateTime(now());
            } else {
                $model->setUpdateTime(now());
            }    
            
            $model->save();
        
            //start of Gallery images 
            $datax = $this->getRequest()->getPost('photogallery_images');        
            $datay = Zend_Json::decode($datax);
            $_photos_info = array();
            $_photos_info = Zend_Json::decode($datax);
            
            $write= Mage::getSingleton('core/resource')->getConnection('core_write');
            $read= Mage::getSingleton('core/resource')->getConnection('core_read');

            $blockImagesTable=Mage::getSingleton('core/resource')->getTableName('photogallery/blockimage');
            
            
            if(!empty($_photos_info)) {
                foreach($_photos_info as $_photo_info) {
                    //Do update if we have gallery id (menaing photo is already saved)
                    
                    
                    if(isset($_photo_info['photogallery_block_id'])) {
                        $img = str_replace(".tmp", "", $_photo_info['file']);
                        $imgPath=Mage::helper('photogallery')->splitImageValue($img, "path");
                        $imgName=Mage::helper('photogallery')->splitImageValue($img, "name");
                        $imgThumb = Mage::getBaseUrl('media').'gallery/galleryimages'.$imgPath.'/thumb/'.$imgName;

                        $data = array(
                            "blockimage_name" => str_replace(".tmp", "", $_photo_info['file']),
                            "blockimage_thumb" => '<img src="'.$imgThumb.'" border="0" width="100" height="100"  />',
                            "blockimage_label" => $_photo_info['label'],
                            "alt_text" => $_photo_info['alt_text'],
                            "img_description" => $_photo_info['description'],
                            "photogallery_block_id" => $_photo_info['photogallery_block_id'],
                            "blockimage_order" => $_photo_info['position'],
                            "disabled" => $_photo_info['disabled'],
                        );
                        // echo "<pre>"; print_r($data); exit;
            
                        $where = array("blockimage_id = ".(int)$_photo_info['value_id']);
                        $write->update($blockImagesTable, $data, $where);
                        
                        if(isset($_photo_info['removed']) and $_photo_info['removed'] == 1) {
                            $write->delete($blockImagesTable, 'blockimage_id = '.(int)$_photo_info['value_id']);
                            $pos=strripos($_SERVER['SCRIPT_FILENAME'], "/");
                            $DirPath=substr($_SERVER['SCRIPT_FILENAME'], 0, $pos) . '/media/gallery/galleryimages';
                            $thmbpath=$DirPath . $imgPath . '/thumb/' . $imgName;
                            $imgpaths=$DirPath . $imgName;
                            unlink($thmbpath);
                            unlink($imgpaths);
                        }
                    } else {
                        $select= $read->select()->from(
                            array('imgtbl'=>$blockImagesTable)
                        )->where('imgtbl.blockimage_name=?', $_photo_info['file']);
                        $_lookup = $read->fetchAll($select);
                        //$read->fetchAll("SELECT * FROM ".$blockImagesTable." WHERE blockimage_name = ?", $_photo_info['file']);
                        
                        if(empty($_lookup)) {
                            $img = str_replace(".tmp", "", $_photo_info['file']);
                            $imgPath=Mage::helper('photogallery')->splitImageValue($img, "path");
                            $imgName=Mage::helper('photogallery')->splitImageValue($img, "name");
                            $imgThumb = Mage::getBaseUrl('media').'gallery/galleryimages'.$imgPath.'/thumb/'.$imgName;
                                
                            if((isset($_photo_info['removed']) and $_photo_info['removed'] == 0)) {
                                $write->insert(
                                    $blockImagesTable, array(
                                    'blockimage_name' => $img,
                                    "blockimage_thumb" => '<img src="'.$imgThumb.'" border="0" width="100" height="100"  />',
                                    'blockimage_label' => $_photo_info['label'],
                                    "alt_text" => $_photo_info['alt_text'],
                                    "img_description" => $_photo_info['description'],
                                    'photogallery_block_id' =>$model->getId(),//(int)$id,
                                    'blockimage_order' => $_photo_info['position'],
                                    "disabled" => $_photo_info['disabled'],
                                    
                                    )
                                );
                            }
                            
                            if(isset($_photo_info['removed']) and $_photo_info['removed'] == 1) {
                                $pos=strripos($_SERVER['SCRIPT_FILENAME'], "/");
                                $DirPath=substr($_SERVER['SCRIPT_FILENAME'], 0, $pos) . '/media/gallery/galleryimages';
                                $thmbpath=$DirPath . $imgPath . '/thumb/' . $imgName;
                                $imgpaths=$DirPath . $img;
                                unlink($thmbpath);
                                unlink($imgpaths);
                            }
                        }
                    }
                }    
            }

            //end of Gallery images 
            
            
            
            
            
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('photogallery')->__('Photogallery Block was successfully saved'));
            Mage::getSingleton('adminhtml/session')->setFormData(false);
            
            if ($this->getRequest()->getParam('back')) {
            $this->_redirect('*/*/edit', array('id' => $model->getId()));
            return;
            }

            $this->_redirect('*/*/');
            return;
        } catch (Exception $e) {
            //echo $e->getMessage(); exit;
            
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/', array('id' => $id));
        }
    }
    
    /**
     * Get related products grid and serializer block
     */
    public function relatedphotogalleryAction()
    {
    $this->_initPhotogalleryBlock();
        $this->loadLayout();
        $this->getLayout()->getBlock('photogalleryblocks.edit.tab.relatedphotogallery')
        ->setPhotogalleryRelated($this->getRequest()->getPost('related_photogallery', null));
        $this->renderLayout();
    }

    
    public function deleteAction() 
    {
        if($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('photogallery/photogalleryblocks');
                 
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Photogallery Block was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }

        $this->_redirect('*/*/');
    }

    public function massDeleteAction() 
    {
        $photogalleryblockid = $this->getRequest()->getParam('photogallery');
        if(!is_array($photogalleryblockid)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Photogallery Block(s)'));
        } else {
            try {
                $collection = Mage::getModel('photogallery/blockimage')->getCollection()
                ->addFieldToFilter('main_table.photogallery_block_id', array('in'=>$photogalleryblockid));
                foreach ($collection as  $imge) {
                        $pathImg = BP . DS . 'media' . DS .'gallery'.DS.'galleryimages'.DS. $imge->getBlockimageName();
                        if ($pathImg) {    
                            unlink($pathImg); 
                        }
                } 
                        
                foreach ($photogalleryblockid as $blockid) {
                    $photogallery = Mage::getModel('photogallery/photogalleryblocks')->load($blockid);
                    $photogallery->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($photogalleryblockid)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
    
    public function massStatusAction()
    {
        $photogalleryblockid = $this->getRequest()->getParam('photogallery');
        if(!is_array($photogalleryblockid)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Photogallery Block(s)'));
        } else {
            try {
                foreach ($photogalleryblockid as $blockid) {
                    $photogallery = Mage::getSingleton('photogallery/photogalleryblocks')
                        ->load($blockid)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($photogalleryblockid))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
    
    
    public function exportCsvAction()
    {
        $fileName   = 'photogalleryblocks.csv';
        $content    = $this->getLayout()->createBlock('photogallery/adminhtml_photogalleryblocks_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'photogalleryblocks.xml';
        $content    = $this->getLayout()->createBlock('photogallery/adminhtml_photogalleryblocks_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        return true;
    }
    
    public function productAction() 
    {
        
        $this->_initProduct();
        $this->loadLayout();
        $data=$this->getRequest()->getPost();
        $this->renderLayout();
    }
    
    public function productGridAction() 
    {
        
            //echo 'Function ===> productgridaction';
            $this->_initProduct();
            $this->loadLayout();
            $data=$this->getRequest()->getPost();
            $this->renderLayout();
    }
    
    public function gridAction() 
    {
     
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('photogallery/adminhtml_photogalleryblocks_edit_tab_product')->toHtml()
        );
    
    }
    
    public function gridOnlyAction()
    {
        //echo 'Function ===> GridOnlyAction';
        $this->_initProduct();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/photogalleryblocks_edit_tab_product')
                ->toHtml()
        );
    }
    
    
     //start Gallery Images Malik Tahir
   
        
    public function uploadAction()
    {
        $result = array();
        try {
            $uploader = new Varien_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                Mage::getSingleton('catalog/product_media_config')->getBaseTmpMediaPath()
            );

            $result['url'] = Mage::getSingleton('catalog/product_media_config')->getTmpMediaUrl($result['file']);
            $fileName =  $result['file'];
            $result['file'] = $result['file'] . '.tmp';
            
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
    
     public function imageAction()
     {

        $result = array();
        try {
            $uploader = new FME_Photogallery_Media_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                Mage::getSingleton('photogallery2/config')->getBaseTmpMediaPath()
            );

            $result['url'] = Mage::getSingleton('photogallery2/config')->getTmpMediaUrl($result['file']);
            $fileName =  $result['file'];
            $result['file'] = $result['file'] . '.tmp';
            
            // create thumbnail
            $targetPath = Mage::getSingleton('photogallery2/config')->getBaseTmpMediaPath();
            
            if(Mage::helper('photogallery')->getAspectratioflag() == 1) {
                $keepRatio = true;
            } else {
                $keepRatio = false;
            }
            
            if(Mage::helper('photogallery')->getKeepframe() == 1) {
                $keepFrame = true;
            } else {
                $keepFrame = false;
            }
            
            $this->resizeFile($targetPath . $uploader->getUploadedFileName(), $keepRatio, $keepFrame, $fileName);
            
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
     }

    
    public function resizeFile($source, $keepRation = true, $keepFrame = true, $fileName)
    {
        if (!is_file($source) || !is_readable($source)) {
            return false;
        }

        $targetDir = $this->getThumbsPath($source);
        $io = new Varien_Io_File();
        if (!$io->isWriteable($targetDir)) {
            $io->mkdir($targetDir);
        }

        if (!$io->isWriteable($targetDir)) {
            return false;
        }
        
        $width = Mage::helper('photogallery')->getThumbWidth();  //$this->getConfigData('resize_width');
        $height = Mage::helper('photogallery')->getThumbHeight(); //$this->getConfigData('resize_height');
        
        $imageObj = new Varien_Image($source);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio($keepRation);
        $imageObj->keepFrame($keepFrame);
        $imageObj->keeptransparency(FALSE);
        $imageObj->backgroundColor(array(Mage::helper('photogallery')->getBgcolor()));
        $imageObj->resize($width, $height);
                
        $dest = $targetDir . DS . pathinfo($source, PATHINFO_BASENAME);
        $imageObj->save($dest);
        if (is_file($dest)) {
            return $dest;
        }

        return false;
    }
    
    /**
     * Return thumbnails directory path for file/current directory
     *
     * @param string $filePath Path to the file
     * @return string
     */
    public function getThumbsPath($filePath = false)
    {
       $mediaRootDir = Mage::getSingleton('photogallery2/config')->getBaseTmpMediaPath();
       $thumbnailDir = Mage::getSingleton('photogallery2/config')->getBaseTmpMediaPath();
        if ($filePath && strpos($filePath, $mediaRootDir) === 0) {
            $thumbnailDir .= dirname(substr($filePath, strlen($mediaRootDir)));
        }

        $thumbnailDir .= DS . "thumb";
        return $thumbnailDir;
    }

    protected function _isAllowed()
    {
        return true;
    }

}
