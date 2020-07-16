<?php
/**
 * @copyright  Copyright (c) 2012 AITOC, Inc. 
 */
abstract class Aitoc_Aitcg_Model_Observer_Page_Html_Head_Js_Abstract
    extends Aitoc_Aitcg_Model_Observer_Abstract
{
    protected $_requiredBlockType ;
    protected $_positionedItems;
    
    protected function _isRequiredBlockType($objBlock)
    {
        if($objBlock->getType() == $this->_requiredBlockType)
        {
            if(!($objBlock instanceof Mage_Core_Block_Template))
            {
                Mage::throwException('Aitcg :: invalid block type');
            }
            return true;
        }
        return false;
    }
    
    abstract protected function _preparePositionedItems();

    protected function _addPositionedItem($type, $name, $params=null, $if=null, $cond=null, $place = null, $benchItem = null )
    {
        $this->_positionedItems[$type.'/'.$name]['position'] = array(
            'place'=>$place,
            'item' =>$type.'/'.$benchItem,
        );
        $this->_positionedItems[$type.'/'.$name]['item']= array(
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
       );
    }
    
    protected function _getPositionedItems()
    {
        return $this->_positionedItems;
    }

    protected function _insertPositionedItems()
    {
        $block = $this->_event->getBlock();
        $items = $this->_getPositionedItems();
        if(!isset($items))
        {
            return false;
        }
        foreach($items as $key=>$item)
        {
            $block->aitocAddPositionedItem(array($key => $item['item']), $item['position']); 
        }    
    }
    
    protected function _isRequiredBlock($objBlock)
    {
        if( !in_array( $this->_getRoute() , $this->_getAllowedRoutes()) )
        {
            return false ;
        }
        
        if(!$this->_isRequiredBlockType($objBlock))
        {
            return false;
        }
        return true;
    }
}
