<?php 
class Raveinfosys_Deleteorder_Model_Observer
{
    public function addMassDelete(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $enable = Mage::getStoreConfig('deleteorder/general/enable');
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction && $block->getRequest()->getControllerName() == 'sales_order' && $enable) {
            $block->addItem(
                'delete_order', array(
                'label'=> Mage::helper('sales')->__('Delete Orders'),
                'url' => $block->getUrl('*/deleteorder/massDelete'),
                'sort_order'=>'100',
                 'confirm'  => Mage::helper('sales')->__('Are you sure you want to delete order(s)?')
                )
            );
        }
    }

    public function orderViewDeleteButton(Varien_Event_Observer $observer)
    {
        $block = Mage::app()->getLayout()->getBlock('sales_order_edit');
        $config = Mage::getStoreConfig('deleteorder/general');
        if ($config['enable'] && $block) {   
            $allowedStatus = explode(',', $config['order_status']);
            $status = $this->getOrderStatus(Mage::app()->getRequest()->getParam("order_id"));
            if (in_array($status, $allowedStatus)) {
                $message = Mage::helper('sales')->__('Are you sure you want to delete this order?');
                $block->addButton(
                    'button_id', array(
                    'label'     => Mage::helper('Sales')->__('Delete Order'),
                    'onclick'   => 'deleteConfirm(\''.$message.'\', \'' . Mage::getUrl('*/deleteorder/delete', array('_current'=>true)) . '\')',
                    'class'     => 'delete'
                    ), 0, 100, 'header', 'header'
                );
            }
        }
    }

    public function getOrderStatus($orderId)
    {
        return Mage::getModel('sales/order')->load($orderId)->getStatus();
    }

    public function addActionColumn($observer)
    {
        $_block = $observer->getBlock();
        $_type = $_block->getType();
        $enable = Mage::getStoreConfig('deleteorder/general/enable');
        if ($_type == 'adminhtml/sales_order_grid' && $enable) {
            $_block->addColumn(
                'action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '100px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'renderer'  => 'deleteorder/adminhtml_sales_order_render_delete',
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                )
            );
        }
    }
}