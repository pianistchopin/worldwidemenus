<?php
class Aitoc_Aitcg_Model_Mysql4_Font_Family extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('aitcg/font_family', 'font_family_id');
    }
}