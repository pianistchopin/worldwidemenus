<?php
class AdjustWare_Icon_Adminhtml_AdjiconController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $this->loadLayout();
        $this->_setActiveMenu('catalog/attributes/adjicon');
        $this->_addBreadcrumb($this->__('Attribute Icons'), $this->__('Attribute Icons')); 
        $this->_addContent($this->getLayout()->createBlock('adjicon/adminhtml_icon')); 	    
        $this->renderLayout();
    }

    public function newAction() {
        $this->editAction();
    }

    public function editAction() {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('adjicon/attribute')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adjicon')->__('Attribute does not exist'));
            $this->_redirect('*/*/');
            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        //load attribute title
        if ($model->getId()){
            $attrModel = Mage::getModel('eav/entity_attribute')->load($model->getAttributeId());
            $model->setFrontendLabel($attrModel->getFrontendLabel());
        }

        Mage::register('adjicon_attribute', $model);

        $this->loadLayout();
        $this->_setActiveMenu('catalog/attributes/adjicon');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('adjicon/adminhtml_icon_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('adjicon/attribute');
        $data   = $this->getRequest()->getPost();

        if ($data) {
            $model->setData($data)->setId($id);

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $msg = Mage::helper('adjicon')->__('Visualized attribute has been successfully saved');

                Mage::getSingleton('adminhtml/session')->addSuccess($msg);

                if ($this->getRequest()->getParam('continue'))
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                else
                    $this->_redirect('*/*/');

                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adjicon')->__('Unable to find an item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('adjicon/attribute')->load($id);
        if (!$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adjicon')->__('Unable to find an item to delete'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $path = Mage::getBaseDir('media') . DS . 'icons' . DS;
            foreach ($model->getOptions() as $info){
                $icon = Mage::getModel('adjicon/icon');
                $icon->load($info['icon_id']);
                $oldFile = $icon->getFilename();
                if ($oldFile)
                    unlink($path . $oldFile);
                $icon->delete();
            }
            $model->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adjicon')->__('All attribute icons have been deleted'));
            $this->_redirect('*/*/');

        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        }
    }

    public function uploadIconAction()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $optionId = $this->getRequest()->getParam('option_id');

        $option = Mage::getModel('adjicon/attribute')->getOptionById($optionId, $attributeId);
        try {
            $icon = Mage::getModel('adjicon/icon');
            $icon->load($option['icon_id'])->upload($attributeId, $option);
            $filename = $icon->getFilename();
            $response = array();
            $response['src_view'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'icons/v_'.$filename;
            $response['src_list'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'icons/pl_'.$filename;
            $response['src_layered'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'icons/l_'.$filename;
            $response['src_option'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'icons/o_'.$filename;
            $response['file_id'] = $optionId;
        }
        catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function deleteIconAction()
    {
        $id = $this->getRequest()->getParam('icon_id');
        $model = Mage::getModel('adjicon/icon')->load($id);
        if (!$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adjicon')->__('Unable to find an item to delete'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $path = Mage::getBaseDir('media') . DS . 'icons' . DS;
            $oldFile = $model->getFilename();
            if ($oldFile)
                unlink($path . $oldFile);
            $model->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adjicon')->__('Attribute icon has been deleted'));
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
    }

    public function formloadAction()
    {
        $id = $this->getRequest()->getParam('attribute');
        $model  = Mage::getModel('adjicon/attribute');
        $model->setAttributeId($id)->setPos('0')
            ->setIconColor('#FFFFFF')
            ->setIconTextColor('#000000')
            ->setShowImages('1')
            ->setShowQty('1')
            ->setShowImagesConfigurable('1');

        Mage::register('adjicon_attribute', $model);

        $response = array();
        $response['form'] = $this->getLayout()->getBlockSingleton('adjicon/adminhtml_icon_edit_form')->toHtml();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function uploadImageFileAction()
    {
        $optionId = $this->getRequest()->getParam('id');
        $optionLabel = $this->getRequest()->getParam('label');
        $productId = $this->getRequest()->getParam('product_id');

        try {
            $image = Mage::getModel('adjicon/image');
            $image->upload($optionId, $optionLabel, $productId);
            $filename = $image->getFile();
            $response = array();
            $response['url'] = Mage::helper('adjicon/image')->getAdjConfigurableBaseUrl($filename);
            $response['file'] = $filename;
            $response['image_id'] = $image->getImageId();
        }
        catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function deleteImageFileAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('adjicon/image')->load($id);
        $response = array();

        if (!$model->getId()) {
            $response['error'] = 'Unable to find an item to delete';
        }
        else {
            try {
                $this->_deleteImageFiles($model);

                $response['image_id'] = $model->getId();
                $response['file'] = $model->getFile();
                $model->delete();
            }
            catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    protected function _deleteImageFiles($adjImageModel)
    {
        $vyaCPPModel = Mage::getModel('adjicon/cpp');
        $vyaCPPModel->load($adjImageModel->getImageId(), 'vya_image_id');
        if($vyaCPPModel->getId()){
            return;// do not delete image files - CPP extension compatibility
        }

        $path = Mage::getBaseDir('media') . DS . 'adjconfigurable' . DS;
        $oldFile = $adjImageModel->getFile();
        if ($oldFile)
        {
            if(file_exists($path . $oldFile))
            {
                unlink($path . $oldFile);
            }
            $this->_deleteThumbs($path, $oldFile);
        }
    }

    /**
     * @param string $path
     * @param string $oldFile
     */
    protected function _deleteThumbs($path, $oldFile)
    {
        $helper = Mage::helper('adjicon');
        $thumbSizes = $helper->getThumbsSizes();
        foreach ($thumbSizes as $thumbSize){
            $fullPath = $path . $helper->getThumbnailFileName($thumbSize, $oldFile);
            if(file_exists($fullPath))
            {
                unlink($fullPath);
            }
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/attributes/adjicon');
    }
}
