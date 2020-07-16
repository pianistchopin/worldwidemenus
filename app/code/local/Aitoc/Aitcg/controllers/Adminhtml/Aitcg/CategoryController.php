<?php

class Aitoc_Aitcg_Adminhtml_Aitcg_CategoryController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
       $this->loadLayout()
            ->_setActiveMenu('catalog/aitcg/categories')
            ->_addBreadcrumb(Mage::helper('aitcg')->__('Aitoc CPP Categories'), Mage::helper('aitcg')->__('Aitoc CPP Categories'));
        return $this;
    }   

    public function indexAction() 
    {
        $this->_initAction();       
        $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_category'));
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
               $this->getLayout()->createBlock('importedit/adminhtml_category_grid')->toHtml()
        );
    }
    
    
    public function editAction()
    {
        $categoryId     = $this->getRequest()->getParam('id');
        $categoryModel  = Mage::getModel('aitcg/category')->load($categoryId);
 
        if ($categoryModel->getId() || $categoryId == 0) {
 
            Mage::register('category_data', $categoryModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('catalog/aitcg/categories');
           
            $this->_addBreadcrumb(Mage::helper('aitcg')->__('Aitoc CPP Categories'), Mage::helper('aitcg')->__('Aitoc CPP Categories'));
            $this->_addBreadcrumb(Mage::helper('aitcg')->__('Category'), Mage::helper('aitcg')->__('Category'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_category_edit'));
               
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
                
                $categoryModel = Mage::getModel('aitcg/category');

                $categoryModel->load($this->getRequest()->getParam('id'))
                    ->setName($postData['name'])
                    ->setDescription($postData['description'])
                    ->setStoreLabels($postData['store_labels']);
                    
               $categoryModel->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setCategoryData(false);
 
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCategoryData($this->getRequest()->getPost());
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
                $categoryModel = Mage::getModel('aitcg/category');
                $categoryModel->load($this->getRequest()->getParam('id'));
                $categoryModel->delete();
/*HERE WE SHOULD DELETE ALL IMAGES UNDER DELETED CATEGORY*/                   
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
            $categoryIds = $this->getRequest()->getParam('category');   
            if(!is_array($categoryIds)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
            } else {
                try {
                
                $categoryModel = Mage::getModel('aitcg/category');
                foreach ($categoryIds as $categoryId) {
/*HERE WE SHOULD DELETE ALL IMAGES UNDER MASS DELETED CATEGORIES*/

                    $categoryModel->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('aitcg')->__(
                    'Total of %d record(s) were deleted.', count($categoryIds)
                    )
                );
                
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }

            $this->_redirect('*/*/index');
        }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aitcg/categories');
    }
    
}