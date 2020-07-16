<?php
class Raveinfosys_Deleteorder_Model_Deleteorder extends Mage_Core_Model_Abstract
{
    public function _construct() 
    {
        parent::_construct();
        $this->_init('deleteorder/deleteorder');
    }

    public function _initOrder($id) 
    {
        $order = $this->getOrder($id);
        if (!$order->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('This order no longer exists.'));
            return false;
        }

        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    public function _remove($orderId) 
    {
        $resource = Mage::getSingleton('core/resource');
        $delete = $resource->getConnection('core_read');
        $orderTable = $resource->getTableName('sales_flat_order_grid');
        $invoiceTable = $resource->getTableName('sales_flat_invoice_grid');
        $shipmentTable = $resource->getTableName('sales_flat_shipment_grid');
        $creditmemoTable = $resource->getTableName('sales_flat_creditmemo_grid');
        $sql = "DELETE FROM  " . $orderTable . " WHERE entity_id = " . $orderId . ";";
        $delete->query($sql);
        $sql = "DELETE FROM  " . $invoiceTable . " WHERE order_id = " . $orderId . ";";
        $delete->query($sql);
        $sql = "DELETE FROM  " . $shipmentTable . " WHERE order_id = " . $orderId . ";";
        $delete->query($sql);
        $sql = "DELETE FROM  " . $creditmemoTable . " WHERE order_id = " . $orderId . ";";
        $delete->query($sql);        
        return true;
    }

    public function getOrder($id)
    {
        return Mage::getModel('sales/order')->load($id);
    }


}