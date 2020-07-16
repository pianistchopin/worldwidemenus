<?php
/**
* @copyright  Copyright (c) 2012 AITOC, Inc. 
*/

class Aitoc_Aitcg_Block_Rewrite_AdminhtmlSalesItemsColumnName extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    protected function _toHtml()
    {    
		$result = parent::_toHtml();
		$result = Mage::helper('aitcg')->getSecureUnsecureUrl($result);

        $result = Mage::helper('aitcg')->removeSocialWidgetsFromHtml($result);

		return $result;
    }
    
     public function getOrderOptions()
    {
        $result = array();
        $options2 = $this->getItem();
        if ($options = $this->getItem()->getProductOptions()) 
        {
            if (isset($options['options'])) 
            {

                $k = 0;
                $idLastOption = null;
                $qtyRefunded = (int) $this->getItem()->getQtyRefunded();

                foreach($options['options'] as $option)
                {
                    $test = $option['option_type'];
                    if(isset($option['value']))
                    {
                        if($qtyRefunded > 0 && $option['option_type'] == 'aitcustomer_image')
                        {
                            $idLastOption = $k;
                        }
                        $k ++;
                    }
                }
                $href = '<a href="' . Mage::helper("adminhtml")->getUrl('cgadmin/products', array('id' => $this->getItem()->getId())) . '">Sell Customized item</a>'; 
                if(!($idLastOption === null))
                    $options['options'][$idLastOption]['value'] .= $href;
                
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
