<?php

class Aitoc_Aitcg_Block_Rewrite_Sales_Order_Item_Renderer_Default extends Mage_Sales_Block_Order_Item_Renderer_Default
{
    /**
     * Rewrite needs for option format converting.
     *
     * @param mixed $optionValue
     * @return array
     */
    public function getFormatedOptionValue($optionValue)
    {
        $optionValue = $this->_getConvertedOptionValue($optionValue);
        return parent::getFormatedOptionValue($optionValue);
    }

    /**
     * @return array
     */
    protected function _getConvertedOptionValue($optionValue)
    {
        return $this->_getConverter()->getConvertedOptionValue($optionValue);
    }

    /**
     * @return Aitoc_Aitcg_Model_Sales_Order_Item_Converter
     */
    protected function _getConverter()
    {
        return Mage::getModel('aitcg/sales_order_item_converter')->setOrderItem($this->getItem());
    }
}
