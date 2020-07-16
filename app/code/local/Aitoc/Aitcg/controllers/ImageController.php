<?php

class Aitoc_Aitcg_ImageController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
       $this->loadLayout()
            ->_setActiveMenu('catalog/aitcg/categories')
            ->_addBreadcrumb(Mage::helper('aitcg')->__('Aitoc Custom Product Preview Images'), Mage::helper('aitcg')->__('Aitoc Custom Product Preview Images'));
        return $this;
    }   

    public function indexAction() 
    {
        $this->_initAction();       
        $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_image'));
        $this->renderLayout();
    }

    /**
     * Product grid for AJAX request.
     * Sort and filter result for example.
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()->createBlock('importedit/adminhtml_image_grid')->toHtml()
        );
    }
    
    
    public function editAction()
    {
        $imageId     = $this->getRequest()->getParam('imgid');
        $imageModel  = Mage::getModel('aitcg/category_image')->load($imageId);
 
        if ($imageModel->getId() || $imageId == 0) {
 
            Mage::register('image_data', $imageModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('catalog/aitcg/categories');
           
            $this->_addBreadcrumb(Mage::helper('aitcg')->__('Aitoc Custom Product Preview Images'), Mage::helper('aitcg')->__('Aitoc Custom Product Preview Images'));
            $this->_addBreadcrumb(Mage::helper('aitcg')->__('Aitoc Custom Product Preview Images'), Mage::helper('aitcg')->__('Aitoc Custom Product Preview Images'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_image_edit'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Item does not exist'));
            $this->_redirect('*/*/',array('id' => $this->getRequest()->getParam('id')));
        }
    }
   
    public function newAction()
    {
        $this->_forward('edit');
    }    
    
    
    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            try {
                $postData = $this->getRequest()->getPost();
                $imageModel = Mage::getModel('aitcg/category_image');

                if(isset($_FILES['filename']['name']) and (file_exists($_FILES['filename']['tmp_name']))) 
                {
                    $path = $imageModel->getImagesPath();
                    $postData['filename'] = Aitoc_Aitcg_Model_Image::uploadFile($path, 'filename', array('jpg','jpeg','png'));
                }
                
                $imageModel->load($this->getRequest()->getParam('imgid'))
                    ->setName($postData['name'])
                    ->setCategoryId($this->getRequest()->getParam('id'));
                    
                
                if(isset($postData['filename'])) {
                    $imageModel->setFilenameWithUnlink($postData['filename']);
                }
                    
                $imageModel->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setImageData(false);
 
                $this->_redirect('*/*/',array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setImageData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('imgid' => $this->getRequest()->getParam('imgid'),'id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/',array('id' => $this->getRequest()->getParam('id')));
    }
   
    public function deleteAction()
    {
        if( $this->getRequest()->getParam('imgid') > 0 ) {
            try {
                $imageModel = Mage::getModel('aitcg/category_image');
                $imageModel->load($this->getRequest()->getParam('imgid'));
                
                $imageModel->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/',array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('imgid')));
            }
        }
        $this->_redirect('*/*/',array('id' => $this->getRequest()->getParam('id')));
    }    
    
        public function massDeleteAction()
        {
            $imageIds = $this->getRequest()->getParam('image');   
            if(!is_array($imageIds)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
            } else {
                try {
                $imageModel = Mage::getModel('aitcg/category_image');
                foreach ($imageIds as $imageId) {
                    $imageModel->load($imageId);
                    $imageModel->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('aitcg')->__(
                    'Total of %d record(s) were deleted.', count($imageIds)
                    )
                );
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }

            $this->_redirect('*/*/index',array('id' => $this->getRequest()->getParam('id')));
        }    
        
        public function massStatusAction()
        {
            $imageIds = $this->getRequest()->getParam('image');   
            $status = $this->getRequest()->getParam('status');

             if(!is_numeric($status)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
            } elseif(!is_array($imageIds)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
            } else {
                try {
                $imageModel = Mage::getModel('aitcg/category_image');
                foreach ($imageIds as $imageId) {
                    $imageModel->load($imageId);
                    $imageModel->setStatus($status);
                    $imageModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('aitcg')->__(
                    'Total of %d record(s) were changed.', count($imageIds)
                    )
                );
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }

            $this->_redirect('*/*/index',array('id' => $this->getRequest()->getParam('id')));
        }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aitcg/categories');
    }
    
}