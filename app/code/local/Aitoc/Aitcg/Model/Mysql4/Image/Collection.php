<?php
class Aitoc_Aitcg_Model_Mysql4_Image_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('aitcg/image');
    }
}