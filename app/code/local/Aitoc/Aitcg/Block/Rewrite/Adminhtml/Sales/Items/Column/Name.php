<?php
/**
* @copyright  Copyright (c) 2012 AITOC, Inc. 
*/

class Aitoc_Aitcg_Block_Rewrite_Adminhtml_Sales_Items_Column_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    /**
     * @return array
     */
    protected function _getConvertedOrderItem()
    {
        return $this->_getConverter()->getConvertedOrderItem();
    }

    /**
     * @return Aitoc_Aitcg_Model_Sales_Order_Item_Converter
     */
    protected function _getConverter()
    {
        return Mage::getModel('aitcg/sales_order_item_converter')->setOrderItem($this->getItem());
    }

    protected function _toHtml()
    {    
        $result = parent::_toHtml();
        $result = Mage::helper('aitcg')->getSecureUnsecureUrl($result);

        $result = Mage::helper('aitcg')->removeSocialWidgetsFromHtml($result);

        return $result;
    }
    
    protected function _getIdLastOption($options)
    {
        $k = 0;
        $idLastOption = null;
        $qtyRefunded = (int) $this->getItem()->getQtyRefunded();

        foreach($options['options'] as $option)
        {
            if(isset($option['value']))
            {
                if($qtyRefunded > 0 && $option['option_type'] == 'aitcustomer_image')
                {
                    $idLastOption = $k;
                }
                $k ++;
            }
        }
        return $idLastOption;
    }
    
    public function getOrderOptions()
    {
        $result = array();

        $this->setItem($this->_getConvertedOrderItem());

        if ($options = $this->getItem()->getProductOptions()) 
        {
            if (isset($options['options'])) 
            {

                $idLastOption = $this->_getIdLastOption($options);
                if(!($idLastOption === null))
                    $options['options'][$idLastOption]['value'] .= '<a href="' . Mage::helper("adminhtml")->getUrl('cgadmin/products', array('id' => $this->getItem()->getId())) . '">Sell Customized item</a>';
                
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }
}
