<?php
class AdjustWare_Icon_Model_Mysql4_Cpp extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('adjicon/cpp', 'id');
    }

    /*
     * @param integer $cppOptionId
     * @return mixed
     */
    public function getCPPTemplateId($cppOptionId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from( $this->getTable('catalog/product_option_aitimage'),'image_template_id' )
            ->where('option_id=?', $cppOptionId);

        $templateId = $this->_getReadAdapter()->fetchOne($select);
        if($templateId == ''){
            return false;
        }

        return $templateId;
    }

    /*
     * @param integer $cppOptionId
     * @return mixed
     */
    public function getCPPOptionTitle($cppOptionId)
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('catalog/product_option_title'), array('title'))
            ->where('option_id=?', $cppOptionId)
            ->where('store_id=?', $storeId);

        $title = $this->_getReadAdapter()->fetchOne($select);

        return $title;
    }
}