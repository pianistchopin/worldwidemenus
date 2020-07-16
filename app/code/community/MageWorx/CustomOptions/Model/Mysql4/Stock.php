<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Model_Mysql4_Stock extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('catalog/product_option_type_value', 'option_type_id');
    }
    
}