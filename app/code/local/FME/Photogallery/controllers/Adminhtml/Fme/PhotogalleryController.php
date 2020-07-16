<?php
/**
 * Photogallery extension
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
 
 

class FME_Photogallery_Adminhtml_Fme_PhotogalleryController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction() 
    {
        $this->loadLayout()
            ->_setActiveMenu('FME_Extensions')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Photogallery Manager'));
        
        return $this;
    }   
 
    public function indexAction() 
    {
        $this->_initAction()
            ->renderLayout();
    }
    
    //start of Gallery Images shahzad
    protected function _initPhotogallery()
    {
        
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('photogallery/photogallery');
        $model_img = Mage::getModel('photogallery/img')->getCollection();
        $model_img->addFieldToFilter('photogallery_id', array('in'=>array($id)));
        // echo "<pre>"; print_r($model_img->getData()); exit();
        if ($id) {
            $model->load($id);
            $model_img->addFieldToFilter('photogallery_id', array('in'=>array($id)));
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This Photogallery is no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }
        
        $data = Mage::getSingleton('adminhtml/session')->getCpaData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

       Mage::register('photogallery', $model);
       Mage::register('photogallery_img', $model_img);
    }
    //end of Gallery Images shahzad
    
    protected function _initPhotogalleryProducts() 
    {
        
        $photogallery = Mage::getModel('photogallery/photogallery');
        $photogalleryId  = (int) $this->getRequest()->getParam('id');
        if ($photogalleryId) {
            $photogallery->load($photogalleryId);
        }

        Mage::register('current_photogallery_products', $photogallery);
        return $photogallery;
        
    }
    
    public function productsAction()
    {
        $this->_initPhotogalleryProducts();
        $this->loadLayout();
        $this->getLayout()->getBlock('photogallery.edit.tab.products')
                           ->setPhotogalleryProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

    public function editAction() 
    {
        $this->_initPhotogallery();
        
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('photogallery/photogallery')->load($id);
        

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('photogallery_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('FME_Extensions');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Photogallery Manager'), Mage::helper('adminhtml')->__('Photogallery Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Photogallery'), Mage::helper('adminhtml')->__('Item Photogallery'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('photogallery/adminhtml_photogallery_edit'))
                ->_addLeft($this->getLayout()->createBlock('photogallery/adminhtml_photogallery_edit_tabs'));
            
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('photogallery')->__('Photogallery does not exist'));
            $this->_redirect('*/*/');
        }
    }
 
    public function newAction() 
    {
        $this->_forward('edit');
    }
 




    public function saveAction() 
    {
                
        if ($data = $this->getRequest()->getPost()) {
            //$dateAr = explode('/',$data['gdate']);
            //$data['gdate']=$dateAr[2].'-'.$dateAr[0].'-'.$dateAr[1];
            
            $model = Mage::getModel('photogallery/photogallery');    
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
                
            $lastid=Mage::getModel('photogallery/photogallery')->getCollection()->getLastId();
            foreach($lastid as $id){
                $lastPid= $id->getPhotogalleryId()+1;
            }
            
            //Set Related Products
             $links = $this->getRequest()->getPost('links');

             if (isset($links['related'])) {
                $productIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['related']);

                 $productString = "";
                 foreach ($productIds as $_product) {
                    $productString .= $_product.",";
                 }

                // $_POST['productIds'] = $productString;
                 $this->getRequest()->setPost('productIds', $productString);
             }
                
            
            if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                $model->setCreatedTime(now())
                ->setUpdateTime(now());
            } else {
                $model->setUpdateTime(now());
            }    
                
            try {
                $model->save();
                //start of Gallery images shahzad
                $datax = $this->getRequest()->getPost('photogallery_images');  
                $datay = Zend_Json::decode($datax);

                $photosInfo = array();
                $photosInfo = Zend_Json::decode($datax);

                $write= Mage::getSingleton('core/resource')->getConnection('core_write');
                $read= Mage::getSingleton('core/resource')->getConnection('core_read');    
                $photogalleryImages=Mage::getSingleton('core/resource')->getTableName('photogallery_images');
                if(!empty($photosInfo)) {

                        foreach($photosInfo as $photoInfo) {
                        //Do update if we have gallery id (menaing photo is already saved)
                            if(isset($photoInfo['photogallery_id'])) {
                                $img = str_replace(".tmp", "", $photoInfo['file']);
                                $imgPath=Mage::helper('photogallery')->splitImageValue($img, "path");
                                $imgName=Mage::helper('photogallery')->splitImageValue($img, "name");
                                $imgThumb = Mage::getBaseUrl('media').'gallery/galleryimages'.$imgPath.'/thumb/'.$imgName;

                                $data = array(
                                    "img_name" => str_replace(".tmp", "", $photoInfo['file']),
                                    "img_thumb" => '<img src="'.$imgThumb.'" border="0" width="100" height="100"  />',
                                    "img_label" => $photoInfo['label'],
                                    "alt_text" => $photoInfo['alt_text'],
                                    "img_description" => $photoInfo['description'],
                                    "photogallery_id" => $photoInfo['photogallery_id'],
                                    "img_order" => $photoInfo['position'],
                                    "disabled" => $photoInfo['disabled'],
                                    
                                );
                                // echo "<pre>"; print_r($data); exit();
                                $where = array("img_id = ".(int)$photoInfo['value_id']);
                                $write->update($photogalleryImages, $data, $where);
        
                                if(isset($photoInfo['removed']) and $photoInfo['removed'] == 1) {
                                    $write->delete($photogalleryImages, 'img_id = '.(int)$photoInfo['value_id']);
                                }
                            } else {
                                $select = $read->select()->from(array('imgtable'=>$photogalleryImages))->where('imgtable.img_name=?', $photoInfo['file']);
                                // now $read is an instance of Zend_Db_Adapter_Abstract
                                $_lookup = $read->fetchAll($select);
                                
                                // $_lookup = $read->fetchAll("SELECT * FROM ".$photogalleryImages." WHERE img_name = ?", $photoInfo['file']);
                                
                                if(empty($_lookup)) {
                                    $img = str_replace(".tmp", "", $photoInfo['file']);
                                    $imgPath=Mage::helper('photogallery')->splitImageValue($img, "path");
                                    $imgName=Mage::helper('photogallery')->splitImageValue($img, "name");
                                    $imgThumb = Mage::getBaseUrl('media').'gallery/galleryimages'.$imgPath.'/thumb/'.$imgName;
                                    
                                    $write->insert(
                                        $photogalleryImages, array(
                                        'img_name' => str_replace(".tmp", "", $photoInfo['file']),
                                        "img_thumb" => '<img src="'.$imgThumb.'" border="0" width="100" height="100"  />',
                                        'img_label' => $photoInfo['label'],
                                        "alt_text" =>     $photoInfo['alt_text'],                
                                        'img_description' => $photoInfo['description'],
                                        'photogallery_id' =>$model->getId(),//(int)$id,
                                        'img_order' => $photoInfo['position'],
                                        "disabled" => $photoInfo['disabled'],
                                        
                                        )
                                    );
                                }
                            }
                        }    
                }

                    //end of Gallery images shahzad
                
                
                
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('photogallery')->__('Photogallery was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }

                $this->_redirect('*/*/');
                
            
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));                                
            
                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('photogallery')->__('Unable to find Photogallery to save'));
        $this->_redirect('*/*/');
    }
 
    public function deleteAction() 
    {
        if($this->getRequest()->getParam('id') > 0) {
            try {
                 $id     = $this->getRequest()->getParam('id');
        
                $model = Mage::getModel('photogallery/photogallery');
                $object  = Mage::getModel('photogallery/photogallery')->load($id);
                $model->setId($this->getRequest()->getParam('id'));
                
                //chmod(Mage::getBaseUrl('media').$object->getValue(),0777); //Will CHMOD a file	
                
                $pathImg = BP . DS . 'media' . DS . $object->getValue();
                if ($pathImg) {        
                unlink($pathImg); 
                }
    
                $model->delete();
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Photogallery was successfully deleted'));
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
       
        $photogalleryIds = $this->getRequest()->getParam('photogallery');
        if(!is_array($photogalleryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Photogallery(s)'));
        } else {
            try {
                $collection = Mage::getModel('photogallery/img')->getCollection();
                    $collection->getSelect()->join(array('pht_item'=> Mage::getConfig()->getTablePrefix().'photogallery'), 'main_table.photogallery_id = pht_item.photogallery_id');
                    $collection->addFieldToFilter('main_table.photogallery_id', array('in'=>$photogalleryIds));
                    //echo "<pre>"; print_r($collection->getData()); exit();
                    foreach ($collection as  $imge) {
                        // print_r($value);

                        $pathImg = BP . DS . 'media' . DS .'gallery'.DS.'galleryimages'.DS. $imge->getimgName();
                        if ($pathImg) {    
                            unlink($pathImg); 
                        }
                    } 
                        
                        
                foreach ($photogalleryIds as $photogalleryId) {
                    $object  = Mage::getModel('photogallery/photogallery')->load($photogalleryId);
                    $object->delete();
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($photogalleryIds)
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
        $photogalleryIds = $this->getRequest()->getParam('photogallery');
        
        Mage::log('status change');
        Mage::log($photogalleryIds);
        
        
        if(!is_array($photogalleryIds)) {
            Mage::getModel('adminhtml/session')->addError($this->__('Please select Photogallery(s)'));
        } else {
            try {
                foreach ($photogalleryIds as $photogalleryId) {
                    $photogallery = Mage::getSingleton('photogallery/photogallery')
                        ->load($photogalleryId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                
                
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($photogalleryIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
    
     
    
      public function toOptionArray($isMultiselect)
      {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('core/language_collection')->loadData()->toOptionArray();
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=>''));
        }

        return $options;
      }
    
    /**
     * Get Photogallery products grid and serializer block
     */
    public function productAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }
    
    
    /**
     * Get related products grid
     */
    public function productsGridAction()
    {
        $this->_initPhotogalleryProducts();
        //Push Existing Values in Array
        $productsarray = array();
        $photogalleryId  = (int) $this->getRequest()->getParam('id');
        foreach (Mage::registry('current_photogallery_products') as $products) {
           $productsarray = $products["product_id"];
        }

     
        $links = $this->getRequest()->getPost('links');

             if (isset($links['related'])) {
                $productIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['related']);
                array_push(Mage::app()->getRequest()->getPost("products_related"), $productIds);
            }
        Mage::registry('current_photogallery_products')->setPhotogalleryProductsRelated($productsarray);
        
        $this->loadLayout();
        $this->getLayout()->getBlock('photogallery.edit.tab.products')
                          ->setPhotogalleryProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

     
    public function gridAction()
    {
         
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('photogallery/adminhtml_photogallery_edit_tab_product')->toHtml()
        );
    
    }

    /**
     * Get specified tab grid
     */
    public function gridOnlyAction()
    {
        //echo 'Function ===> GridOnlyAction';
        $this->_initProduct();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/photogallery_edit_tab_product')
                ->toHtml()
        );
    }
    
   
        
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
        $bg_color = Mage::helper('photogallery')->getBgcolor();
        $bg_color = explode(',', $bg_color);
        
        $imageObj = new Varien_Image($source);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio($keepRation);
        $imageObj->keepFrame($keepFrame);
        $imageObj->keeptransparency(FALSE);
        $imageObj->backgroundColor(array((int) $bg_color[0], (int) $bg_color[1], (int) $bg_color[2]));
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