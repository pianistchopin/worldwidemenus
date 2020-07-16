<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_Mysql4_Group_Store extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('mageworx_customoptions/group_store', 'group_store_id');
    }
    
    public function loadByGroupAndStore($object, $groupId, $storeId) {
	$read = $this->_getReadAdapter();
        if ($read) {  
            $select = $read->select()
                    ->from($this->getMainTable())
                    ->where('group_id = ?', $groupId)
                    ->where('store_id = ?', $storeId)
                    ->limit(1);
 
            $data = $read->fetchRow($select);
            if ($data) {
                $object->addData($data);
            }
        }
    }
    
}