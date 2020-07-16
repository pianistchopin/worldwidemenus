<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_Mysql4_Group_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct() {
        $this->_init('mageworx_customoptions/group');
    }

    public function addStatusFilter() {
	$this->getSelect()->where('main_table.is_active = ?', MageWorx_CustomOptions_Helper_Data::STATUS_VISIBLE);
        return $this;
    }
    
    public function addSortOrder() {
        $this->getSelect()->order('title');
        return $this;
    }
    
    public function addProductsCount() {
        $this->getSelect()->joinLeft(array('relation' => $this->getTable('mageworx_customoptions/relation')), 'relation.group_id = main_table.group_id', array('products'=>'COUNT(DISTINCT relation.product_id)'))
            ->group('main_table.group_id');
        return $this;
    }
    
    public function setShellRequest() {              
        if ($this->getSelect()!==null) {            
            $sql = $this->getSelect()->assemble();
            $this->getSelect()->reset()->from(array('main_table' => new Zend_Db_Expr('('.$sql.')')), '*');
            //echo $this->getSelect()->assemble(); exit;
        }                        
        return $this;
    }

//    public function addStoreFilter($store, $withAdmin = true)
//    {
//        if ($store instanceof Mage_Core_Model_Store) {
//            $store = array($store->getId());
//        }
//        //$this->getSelect()->where('main_table.store_id IN (?)', ($withAdmin ? array(0, $store) : $store));
//        return $this;
//    }
}