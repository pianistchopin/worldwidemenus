<?php
class Aitoc_Aitcg_Model_Font_Family extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/font_family');
    }

    public function getFontsPath()
    {
        return Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'fonts' . DS;
    }
}
