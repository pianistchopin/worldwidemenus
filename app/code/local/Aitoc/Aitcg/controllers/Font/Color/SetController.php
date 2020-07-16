<?php

class Aitoc_Aitcg_Font_Color_SetController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
       $this->loadLayout()
            ->_setActiveMenu('catalog/aitcg/font_color_set')
            ->_addBreadcrumb(Mage::helper('aitcg')->__('Aitoc Custom Product Preview Font Color'), Mage::helper('aitcg')->__('Aitoc Custom Product Preview Font Color'));
        return $this;
    }   

    public function indexAction() 
    {
        $this->_initAction();       
        $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_font_color_set'));
        $this->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()->createBlock('aitcg/adminhtml_font_color_set_grid')->toHtml()
        );
    }
    
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    public function editAction()
    {
        $colorsetId = $this->getRequest()->getParam('id');
        $colorsetModel  = Mage::getModel('aitcg/font_color_set')->load($colorsetId);
 
        if ($colorsetModel->getId()|| $colorsetId == 0) 
        {
 
            Mage::register('aitcg_font_color_set_data', $colorsetModel);
 
            $this->loadLayout();
            //$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_font_color_set_edit'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }
    
    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            try {
                $postData = $this->getRequest()->getPost();
                $colorsetModel = Mage::getModel('aitcg/font_color_set');
                $colorsetModel->load($this->getRequest()->getParam('id'))
                    ->setName($postData['name'])
                    ->setValue($postData['value'])    
                    ->setStatus($postData['status']);
                $colorsetModel->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setAitcgFontColorSetData(false);
 
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAitcgFontColorSetData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
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
        $colorsetIds = $this->getRequest()->getParam('color_set');   
        if(!is_array($colorsetIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } else {
            try 
            {
                foreach ($colorsetIds as $colorsetId) 
                {
                    $this->_deleteItem($colorsetId);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('aitcg')->__(
                    'Total of %d record(s) were deleted.', count($colorsetIds)
                )
            );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    protected function _deleteItem($itemId)
    {
        $colorsetModel = Mage::getModel('aitcg/font_color_set');
        $colorsetModel->load($itemId);
        $colorsetModel->delete();
    }
    
    public function massStatusAction()
    {
        $colorsetIds = $this->getRequest()->getParam('color_set');   
        $status = $this->getRequest()->getParam('status');

        if(!is_numeric($status)) 
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } 
        elseif(!is_array($colorsetIds)) 
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } 
        else 
        {
            try 
            {
                foreach ($colorsetIds as $colorsetId) 
                {
                    $this->_changeStatusItem($colorsetId, $status);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('aitcg')->__(
                    'Total of %d record(s) were changed.', count($colorsetIds)
                )
            );
            } 
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
    
    protected function _changeStatusItem($itemId, $status)
    {
        $colorsetModel = Mage::getModel('aitcg/font_color_set');
        $colorsetModel->load($itemId);
        $colorsetModel->setStatus($status);
        $colorsetModel->save();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aitcg/font_color');
    }

}