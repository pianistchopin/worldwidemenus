<?php
/**
 * @copyright  Copyright (c) 2012 AITOC, Inc. 
 */
class Aitoc_Aitcg_Model_Observer_Page_Html_Head_Js_Catalogproductview
    extends Aitoc_Aitcg_Model_Observer_Page_Html_Head_Js_Abstract
{
    protected $_requiredBlockType = 'page/html_head';
    
    protected function _getAllowedRoutes()
    {
        return array(
            'catalog_product_view',
        );
    }
    
    protected function _preparePositionedItems()
    {
        $this->_addPositionedItem(
            'js', 
            'aitoc/aitcg/prototype/rewrite.js', 
            null, 
            null, 
            null, 
            'after', 
            'prototype/prototype.js' );
    }
    
    public function add(Varien_Event_Observer $observer)
    {
        $this->_initEvent($observer);
        $block = $this->_event->getBlock();
        if(!$this->_isRequiredBlock($block))
        {
            return;
        }
        $this->_preparePositionedItems();
        $this->_insertPositionedItems();            
    }
}

