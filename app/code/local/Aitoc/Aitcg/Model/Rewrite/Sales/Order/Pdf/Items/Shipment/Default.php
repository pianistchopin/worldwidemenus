<?php
class Aitoc_Aitcg_Model_Rewrite_Sales_Order_Pdf_Items_Shipment_Default extends Mage_Sales_Model_Order_Pdf_Items_Shipment_Default 
{
    public function getItemOptions() {
        $result = parent::getItemOptions();
        $result = Mage::helper('aitcg/options')->replaceAitTemplateWithText( $result );
        return $result;
    }
}
