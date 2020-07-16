<?php 
class Raveinfosys_Deleteorder_Adminhtml_DeleteorderController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() 
    {
        $this->loadLayout();        
        return $this;
    } 

    public function indexAction() 
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function deleteAction() 
    {    
        $config = Mage::getStoreConfig('deleteorder/general');
        $configStatusArray = explode(',', $config['order_status']);
        if ($config['enable']) {
            $model = Mage::getModel('deleteorder/deleteorder');
            if ($order = $model->_initOrder($this->getRequest()->getParam('order_id'))) {
                if (in_array($order->getStatus(), $configStatusArray)) {
                    try {
                         $order->delete();
                        if ($model->_remove($this->getRequest()->getParam('order_id'))) {
                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Order was successfully deleted.'));
                            $this->_redirect('adminhtml/sales_order/index');
                        }
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('order_ids')));
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('adminhtml')->__(
                            'Only selected order status can be deleted. Please check delete order <a href="'.$this->getUrl('adminhtml/system_config/edit/section/deleteorder').'">configuration</a>.'
                        )
                    );
                }
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__(
                    'Delete order module is disabled.'
                )
            );
        }

        $this->_redirect('adminhtml/sales_order/index');
    }

    public function massDeleteAction() 
    {    
        $model = Mage::getModel('deleteorder/deleteorder');
        $deleteorderIds = $this->getRequest()->getParam('order_ids');
        if (!is_array($deleteorderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                $success = 0;
                $error = 0;
                $configStatus = Mage::getStoreConfig('deleteorder/general/order_status');
                $configStatusArray = explode(',', $configStatus);
                foreach ($deleteorderIds as $deleteorderId) {
                    $order = $model->getOrder($deleteorderId);
                    if (in_array($order->getStatus(), $configStatusArray)) {
                        $order->delete()->unsetAll();
                        $model->_remove($deleteorderId);
                        $success++;
                    } else {
                        $error++;
                    }
                }

                if ($success > 0) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                            'Total of %d order(s) were successfully deleted.', $success
                        )
                    );
                }

                if ($error > 0) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('adminhtml')->__(
                            'Only selected order status can be deleted. Please check delete order <a href="'.$this->getUrl('adminhtml/system_config/edit/section/deleteorder').'">configuration</a>.'
                        )
                    );
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/sales_order/index');
    }

    protected function _isAllowed()    
    {
        return Mage::getSingleton('admin/session')->isAllowed('deleteorder');
    }
    
}