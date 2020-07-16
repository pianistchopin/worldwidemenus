<?php

class Aitoc_Aitcg_FontController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
       $this->loadLayout()
            ->_setActiveMenu('catalog/aitcg/fonts')
            ->_addBreadcrumb(Mage::helper('aitcg')->__('Aitoc Custom Product Preview Fonts'), Mage::helper('aitcg')->__('Aitoc Custom Product Preview Fonts'));
        return $this;
    }   

    public function indexAction() 
    {
        $this->_initAction();       
        $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_font'));
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
               $this->getLayout()->createBlock('importedit/adminhtml_font_grid')->toHtml()
        );
    }
    
    
    public function editAction()
    {
        $fontId     = $this->getRequest()->getParam('id');
        $fontModel  = Mage::getModel('aitcg/font')->load($fontId);
 
        if ($fontModel->getId() || $fontId == 0) {
 
            Mage::register('font_data', $fontModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('catalog/aitcg/fonts');
           
            $this->_addBreadcrumb(Mage::helper('aitcg')->__('Item Manager'), Mage::helper('aitcg')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('aitcg')->__('Item News'), Mage::helper('aitcg')->__('Item News'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_font_edit'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Item does not exist'));
            $this->_redirect('*/*/');
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
                $fontModel = Mage::getModel('aitcg/font');

                if(isset($_FILES['filename']['name']) and (file_exists($_FILES['filename']['tmp_name']))) 
                {
                    $path = $fontModel->getFontsPath();
                    $postData['filename'] = Aitoc_Aitcg_Model_Image::uploadFile($path, 'filename', array('ttf','tte'));
                }
                
                $fontModel->load($this->getRequest()->getParam('id'))
                    ->setName($postData['name'])
                    ->setStatus($postData['status']);
                    
                
                if(isset($postData['filename'])) {
                    $fontModel->setFilename($postData['filename']);
                }
                    
               $fontModel->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFontData(false);
 
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFontData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
   
    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $this->_deleteItem($this->getRequest()->getParam('id'));
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
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
        $fontIds = $this->getRequest()->getParam('font');   
        if(!is_array($fontIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } else {
            try {
            $fontModel = Mage::getModel('aitcg/font');
            foreach ($fontIds as $fontId) {
                $this->_deleteItem($fontId);
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('aitcg')->__(
                'Total of %d record(s) were deleted.', count($fontIds)
                )
            );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }    

    protected function _deleteItem ($fontId)
    {
            $fontModel = Mage::getModel('aitcg/font');
            $fontModel->load($fontId);
                    if($fontModel->getFilename()) {
                            $fullPath = $fontModel->getFontsPath() . $fontModel->getFilename();
                            unlink($fullPath);                    
                    }

            $fontModel->delete();

    }
    public function massStatusAction()
    {
        $fontIds = $this->getRequest()->getParam('font');   
        $status = $this->getRequest()->getParam('status');

            if(!is_numeric($status)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } elseif(!is_array($fontIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } else {
            try {
            foreach ($fontIds as $fontId) 
            {
                    $this->_changeStatusItem($fontId, $status);
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('aitcg')->__(
                'Total of %d record(s) were changed.', count($fontIds)
                )
            );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }    
    
    protected function _changeStatusItem($itemId, $status)
    {

        $fontModel = Mage::getModel('aitcg/font');
                    $fontModel->load($itemId);
                    $fontModel->setStatus($status);
                    $fontModel->save();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aitcg/fonts');
    }
}