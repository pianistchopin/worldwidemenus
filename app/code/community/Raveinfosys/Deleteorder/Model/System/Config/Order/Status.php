<?php
class Raveinfosys_Deleteorder_Model_System_Config_Order_Status
{
    public function toOptionArray()
    {
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        
        $options = array(
            array(
               'value' => false,
               'label' => Mage::helper('adminhtml')->__('-- Please Select --')
            )
        );
            
        foreach ($statuses as $code => $label) {
            $options[] = array(
               'value' => $code,
               'label' => $label
            );
        }
        
        return $options;
    }
}
