<?php
/**
* @copyright  Copyright (c) 2013 AITOC, Inc. 
*/

class Aitoc_Aitcg_Block_Rewrite_SalesOrderItems extends Mage_Sales_Block_Order_Items
{
    protected function _toHtml()
    {   
		$result = parent::_toHtml();
		$result = Mage::helper('aitcg')->getSecureUnsecureUrl($result);
		return $result;
    }
}
