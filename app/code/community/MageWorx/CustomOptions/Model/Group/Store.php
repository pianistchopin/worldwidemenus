<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_Group_Store extends Mage_Core_Model_Abstract {

    protected function _construct() {
        parent::_construct();
        $this->_init('mageworx_customoptions/group_store');
    }
    
    public function loadByGroupAndStore($groupId, $storeId) {
	$this->getResource()->loadByGroupAndStore($this, $groupId, $storeId);
        return $this;
    }
    

}
