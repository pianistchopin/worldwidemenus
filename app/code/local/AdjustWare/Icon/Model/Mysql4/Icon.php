<?php
class AdjustWare_Icon_Model_Mysql4_Icon extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('adjicon/icon', 'icon_id');
    }
}