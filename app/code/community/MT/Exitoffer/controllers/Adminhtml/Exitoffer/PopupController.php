<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

class MT_Exitoffer_Adminhtml_Exitoffer_PopupController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_title($this->__('Exit Intent Popup'))->_title($this->__('Popups'));
        $this->loadLayout();
        $this->_setActiveMenu('exitoffer/exitoffer_popup');

        $this->_addContent($this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_list'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id', null);

        $model = Mage::getModel('exitoffer/popup');
        if ($id) {
            $model->load((int) $id);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('exitoffer')->__('popup does not exist')
                );
                $this->_redirect('*/*/');
            }
        }

        Mage::register('exitoffer_popup_data', $model);

        $this->_title($this->__('Exit Intent Popup'))->_title($this->__('Edit'));

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit'))
            ->_addLeft($this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('exitoffer/popup');
            $id = $this->getRequest()->getParam('id');
            if (is_numeric($id))
                $model->load($id);

            if (isset($data['popup'])) {
                foreach ($data['popup'] as $key => $value) {
                    $model->setData($key, $value);
                }
            }

            Mage::getSingleton('adminhtml/session')->setFormData($data);

            try {
                $model->save();
                if (!$model->getId())
                    Mage::throwException(Mage::helper('exitoffer')->__('Unable to save'));

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('exitoffer')->__('Exit Intent Popup was successfully saved.')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back'))
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                else
                    $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId())
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                else
                    $this->_redirect('*/*/');
            }

            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('exitoffer')->__('No data found to save'));
        $this->_redirect('*/*/');
    }

    public function saveFieldAjaxAction()
    {
        $helper = Mage::helper('exitoffer');
        if (!$helper->isAjax()) {
            $this->_forward('noRoute');
            return;
        }

        $request = $this->getRequest();
        $popup = Mage::getModel('exitoffer/popup')->load($request->getParam('popup_id'));

        if (!$popup->getId()) {
            $result['error'] = $helper->__('Exit intent popup is not defined');
        } else {
            try {
                if (is_numeric($request->getParam('field_id'))) {
                    Mage::getModel('exitoffer/field')->update($request->getParams());
                    $this->_getSession()->addSuccess($helper->__('Additional field has been saved successful'));
                } else {
                    Mage::getModel('exitoffer/field')->addNew($request->getParams());
                    $type = MT_Exitoffer_Model_Popup::CONTENT_TYPE_NEWSLETTER_SUBSCRIPTION_FORM;
                    if ($popup->getContentType() == $type) {
                        Mage::getModel('exitoffer/subscriber')->addDbColumn(
                            $request->getParam('name'), $request->getParam('type')
                        );
                    }

                    $this->_getSession()->addSuccess($helper->__('Additional field has been added successful'));
                }

                $this->_initLayoutMessages('adminhtml/session');
                $result['messages']  = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
            } catch (Mage_Core_Exception $e) {
                $result['error'] = $e->getMessage();
            } catch (Exception $e) {
                $result['error'] = $helper->__(
                    'An error occurred while saving field. Please review the log and try again.'
                );
                Mage::logException($e);
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function deleteFieldAjaxAction()
    {
        $helper = Mage::helper('exitoffer');
        if (!$helper->isAjax()) {
            $this->_forward('noRoute');
            return;
        }

        $request = $this->getRequest();
        $field = Mage::getModel('exitoffer/field')->load($request->getParam('field_id'));

        if (!$field->getId()) {
            $result['error'] = $helper->__('Exit intent popup field is not defined');
        } else {
            try {
                $field->delete();
                $this->_getSession()->addSuccess($helper->__('Additional field has been deleted successful'));

                $this->_initLayoutMessages('adminhtml/session');
                $result['messages']  = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
            } catch (Mage_Core_Exception $e) {
                $result['error'] = $e->getMessage();
            } catch (Exception $e) {
                $result['error'] = $helper->__(
                    'An error occurred while deleting field. Please review the log and try again.'
                );
                Mage::logException($e);
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id')) {
            $id = $this->getRequest()->getParam('id');
            try {
                $model = Mage::getModel('exitoffer/popup');
                $model->load($id);

                if (!$model->getId())
                    Mage::throwException(Mage::helper('exitoffer')->__('Unable to delete'));
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('exitoffer')->__('Exit Intent Popup was successfully deleted.')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/');
            }

            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('exitoffer')->__('No data found to delete'));
        $this->_redirect('*/*/');
    }


    public function fieldGridAction()
    {
        if (!Mage::helper('exitoffer')->isAjax()) {
            $this->_forward('noRoute');
            return;
        }

        $model = Mage::getModel('exitoffer/popup')->load($this->getRequest()->getParam('popup_id'));
        Mage::register('exitoffer_popup_data', $model);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_fields_grid')->toHtml()
        );
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_list_grid')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('exitoffer/popup');
    }
}
