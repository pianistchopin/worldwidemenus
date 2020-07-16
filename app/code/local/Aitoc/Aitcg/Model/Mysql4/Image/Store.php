<?php
class Aitoc_Aitcg_Model_Mysql4_Image_Store extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('aitcg/image_store', 'id');
    }
}