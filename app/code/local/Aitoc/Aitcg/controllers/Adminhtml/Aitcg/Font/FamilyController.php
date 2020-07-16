<?php

class Aitoc_Aitcg_Adminhtml_Aitcg_Font_FamilyController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/aitcg/font_families')
            ->_addBreadcrumb(
                Mage::helper('aitcg')->__('Aitoc Custom Product Preview Font Families'), Mage::helper('aitcg')
                ->__('Aitoc Custom Product Preview Font Families')
            );

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_font_family'));
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
            $this->getLayout()->createBlock('importedit/adminhtml_font_family_grid')->toHtml()
        );
    }


    public function editAction()
    {
        $fontId    = $this->getRequest()->getParam('id');
        $fontModel = Mage::getModel('aitcg/font_family')->load($fontId);

        if ($fontModel->getId() || $fontId == 0) {

            Mage::register('font_family_data', $fontModel);

            $this->loadLayout();
            $this->_setActiveMenu('catalog/aitcg/font_families');

            $this->_addBreadcrumb(Mage::helper('aitcg')->__('Item Manager'), Mage::helper('aitcg')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('aitcg')->__('Item News'), Mage::helper('aitcg')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('aitcg/adminhtml_font_family_edit'));

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
        if ($this->getRequest()->getPost()) {
            try {
                $postData        = $this->getRequest()->getPost();
                $fontFamilyModel = Mage::getModel('aitcg/font_family');
                $filesNamesArray = array();

                if (isset($_FILES['filename']['name']) && (file_exists($_FILES['filename']['tmp_name']))) {
                    $path            = $fontFamilyModel->getFontsPath();
                    $filesNamesArray = Aitoc_Aitcg_Model_Font::uploadZip($path, 'filename', array('zip'));
                }

                $fontFamilyModel->load($this->getRequest()->getParam('id'));

                if (isset($postData['title'])) {
                    $fontFamilyModel->setTitle($postData['title']);
                }

                $fontFamilyModel->save();

                if ($filesNamesArray) {
                    foreach ($filesNamesArray as $font) {
                        $fontModel = Mage::getModel('aitcg/font');

                        $name = explode(".", $font);
                        $name = $name[0];

                        $fontModel->setName($name);
                        $fontModel->setStatus(1);
                        $fontModel->setFilename($font);
                        $fontModel->setFontFamilyId($fontFamilyModel->getId());
                        $fontModel->save();
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')
                        ->__('Item was successfully saved')
                );
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
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $fontCollection = Mage::getModel('aitcg/font')->getCollection();
                $error          = false;

                foreach ($fontCollection as $font) {
                    if ($font->getFontFamilyID() == $this->getRequest()->getParam('id')) {
                        $error = true;
                        break;
                    }
                }

                if ($error) {
                    Mage::getSingleton('adminhtml/session')
                        ->addError('You cannot remove the font family since there is at least one font dependent on it.');
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                } else {
                    $this->_deleteItem($this->getRequest()->getParam('id'));

                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')
                            ->__('Item was successfully deleted')
                    );
                    $this->_redirect('*/*/');
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $fontFamiliesIds = $this->getRequest()->getParam('font');
        if (!is_array($fontFamiliesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } else {
            try {
                $fontModel = Mage::getModel('aitcg/font_family');
                $countOfErrors = 0;

                foreach ($fontFamiliesIds as $fontFamilyId) {
                    $fontCollection = Mage::getModel('aitcg/font')->getCollection();
                    $error          = false;

                    foreach ($fontCollection as $font) {
                        if ($font->getFontFamilyID() == $fontFamilyId) {
                            Mage::getSingleton('adminhtml/session')
                                ->addError('ID:' . $fontFamilyId . '. You cannot remove the font family since there is at least one font dependent on it.');
                            $error = true;
                            $countOfErrors++;
                            break;
                        }
                    }

                    if (!$error) {
                        $this->_deleteItem($fontFamilyId);
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('aitcg')->__(
                        'Total of %d record(s) were deleted.', (count($fontFamiliesIds) - $countOfErrors)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _deleteItem($fontId)
    {
        $fontModel = Mage::getModel('aitcg/font_family');
        $fontModel->load($fontId);
        if ($fontModel->getFilename()) {
            $fullPath = $fontModel->getFontsPath() . $fontModel->getFilename();
            unlink($fullPath);
        }

        $fontModel->delete();

    }

    public function massStatusAction()
    {
        $fontIds = $this->getRequest()->getParam('font');
        $status  = $this->getRequest()->getParam('status');

        if (!is_numeric($status)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } elseif (!is_array($fontIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcg')->__('Please select item(s).'));
        } else {
            try {
                foreach ($fontIds as $fontId) {
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

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aitcg/font_families');
    }
}