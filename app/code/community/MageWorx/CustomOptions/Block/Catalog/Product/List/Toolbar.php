<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    
    protected $_totalRecords;
    public function getTotalNum() {
        if (Mage::helper('cataloginventory')->isShowOutOfStock()) return parent::getTotalNum();
        
        if (is_null($this->_totalRecords)) {
            $collection = clone $this->getCollection();
            $collection->getSelect()->reset(Zend_Db_Select::ORDER)->reset(Zend_Db_Select::LIMIT_COUNT)->reset(Zend_Db_Select::LIMIT_OFFSET);
            $collection->setCurPage(false)->setPageSize(false);
            $collection->clear()->load();
            $this->_totalRecords = count($collection);
        }
        return $this->_totalRecords;
    }

}
