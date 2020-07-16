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
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */
 
 

class FME_Photogallery_Adminhtml_Fme_GalleryController extends Mage_Adminhtml_Controller_Action
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
        $this->loadLayout()
            ->renderLayout();
    }
    
    //start of Gallery Images shahzad
    protected function _initGallery()
    {
        
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('photogallery/gallery');
       
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This Gallery is no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }
        
        $data = Mage::getSingleton('adminhtml/session')->getCpaData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

       Mage::register('gallery', $model);
     
    }
    

    public function editAction() 
    {
        $this->_initGallery();
        
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('photogallery/gallery')->load($id);
        

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('gallery_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('FME_Extensions');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Photogallery Manager'), Mage::helper('adminhtml')->__('Gallery Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Photogallery'), Mage::helper('adminhtml')->__('Item Gallery'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('photogallery/adminhtml_gallery_edit'))
                ->_addLeft($this->getLayout()->createBlock('photogallery/adminhtml_gallery_edit_tabs'));
            
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
// ñîõðàíåíèå 
                $main_images= array('delete' => 0);
        if ($data = $this->getRequest()->getPost()) {
              //echo "<pre>"; print_r($data); exit();
            //$dateAr = explode('/',$data['gdate']);
            //$data['gdate']=$dateAr[2].'-'.$dateAr[0].'-'.$dateAr[1];
            if(isset($data['stores'])) {
                if(in_array('0', $data['stores'])){
                    $data['store_id'] = '0';
                }
                else{
                    $data['store_id'] = implode(",", $data['stores']);
                }

               unset($data['stores']);
            }

            $delete=false;
            
             if ($main_images=$this->getRequest()->getPost('main_images')) 
             { 
                 if(isset($main_images['delete']) && $main_images['delete']):
                 $delete=true;
                 endif;
             }

             if(isset($_FILES['main_images']['name'])) {
              try {
                  $uploader = new Varien_File_Uploader('main_images');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('media') . DS ;
                if (!file_exists($path . '/gallery')) {
                    mkdir($path . "/gallery/albums", 0777, true);
                }

                $path = $path ."gallery/albums";
                $uploader->save($path, $_FILES['main_images']['name']);
                $data['main_images'] = "gallery/albums".DS.$uploader->getUploadedFileName();
              }catch(Exception $e) {
               unset($data['main_images']);
              }
             }  if ($delete) { 
                     $data['main_images'] = '';
             }

             
            $model = Mage::getModel('photogallery/gallery');    
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
                
            
                
            
            if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                $model->setCreatedTime(now())
                ->setUpdateTime(now());
            } else {
                $model->setUpdateTime(now());
            }    
                
            try {
                $model->save();
                //start of Gallery images shahzad
                
                
                
                
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('photogallery')->__('Gallery was successfully saved'));
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

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('photogallery')->__('Unable to find Gallery to save'));
        $this->_redirect('*/*/');
    }
 
    public function deleteAction() 
    {
        if($this->getRequest()->getParam('id') > 0) {
            try {
                 $id     = $this->getRequest()->getParam('id');
        
                $model = Mage::getModel('photogallery/gallery');
                $object  = Mage::getModel('photogallery/gallery')->load($id);
                $model->setId($this->getRequest()->getParam('id'));
                
                //chmod(Mage::getBaseUrl('media').$object->getValue(),0777); //Will CHMOD a file	
                
                $pathImg = BP . DS . 'media' . DS . $object->getValue();
                if ($object->getValue()) {        
                    unlink($pathImg); 
                }
    
                $model->delete();
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Gallery was successfully deleted'));
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
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Gallery(s)'));
        } else {
            try {
                $read= Mage::getSingleton('core/resource')->getConnection('core_read');            
                $tableName = Mage::getSingleton('core/resource')->getTableName('photogallery_images');
                $write= Mage::getSingleton('core/resource')->getConnection('core_write');
                
                foreach ($photogalleryIds as $photogalleryId) {
                    $gallery = Mage::getModel('photogallery/gallery');
                    $gallery->setId($photogalleryId)->delete();
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
                    $photogallery = Mage::getSingleton('photogallery/gallery')
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
        foreach (Mage::registry('current_photogallery_products')->getPhotogalleryRelatedProducts($photogalleryId) as $products) {
           $productsarray = $products["product_id"];
        }

        array_push($this->getRequest()->getPost("products_related"), $productsarray);
        Mage::registry('current_photogallery_products')->setPhotogalleryProductsRelated($productsarray);
        
        $this->loadLayout();
        $this->getLayout()->getBlock('photogallery.edit.tab.products')
                          ->setPhotogalleryProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

     
    public function gridAction()
    {
         
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('photogallery/adminhtml_gallery_edit_tab_product')->toHtml()
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
            $this->getLayout()->createBlock('adminhtml/gallery_edit_tab_product')
                ->toHtml()
        );
    }
    
     

    
    protected function _isAllowed()
    {
        return true;
    }

}