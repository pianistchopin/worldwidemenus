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

class MT_Exitoffer_Adminhtml_Exitoffer_CampaignController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_title($this->__('Exit Intent Popup'))->_title($this->__('Campaigns'));
        $this->loadLayout();
        $this->_setActiveMenu('exitoffer/exitoffer_campaign');

        $this->_addContent($this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_list'));
        $this->renderLayout();
    }



    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id', null);

        $model = Mage::getModel('exitoffer/campaign');
        if ($id) {
            $model->load((int) $id);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('exitoffer')->__('Campaign does not exist'));
                $this->_redirect('*/*/');
            }
        }
        Mage::register('exitoffer_campaign_data', $model);

        $this->_title($this->__('Exit Intent Campaign'))->_title($this->__('Edit'));

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_edit'))
            ->_addLeft($this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_edit_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['campaign'])) {
                $data['campaign'] = $this->_filterDateTime(
                    $data['campaign'],
                    array(
                        'start_date',
                        'end_date'
                    )
                );
            }

            $model = Mage::getModel('exitoffer/campaign');
            $id = $this->getRequest()->getParam('id');
            if (is_numeric($id))
                $model->load($id);

            if (isset($data['campaign'])) {
                foreach ($data['campaign'] as $key => $value) {
                    $model->setData($key, $value);
                }
            }

            $data['campaign']['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);

            $model->loadPost($data['campaign']);


            Mage::getSingleton('adminhtml/session')->setPageData($model->getData());

            try {
                $model->save();
                if (!$model->getId())
                    Mage::throwException(Mage::helper('exitoffer')->__('Unable to save'));

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('exitoffer')->__('Campaign was successfully saved.')
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

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id')) {
            $id = $this->getRequest()->getParam('id');
            try {
                $model = Mage::getModel('exitoffer/campaign');
                $model->load($id);

                if (!$model->getId())
                    Mage::throwException(Mage::helper('exitoffer')->__('Unable to delete'));
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('exitoffer')->__('Campaign was successfully deleted.')
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

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_campaign_list_grid')->toHtml()
        );
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('exitoffer/campaign'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('exitoffer/campaign');
    }
}
