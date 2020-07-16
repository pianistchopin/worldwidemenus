<?php
class Aitoc_Aitcg_Model_Mysql4_Mask_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('aitcg/mask');
    }
    public function massUpdate(array $data)
    {
        $this->getConnection()->update($this->getResource()->getMainTable(), $data, $this->getResource()->getIdFieldName() . ' IN(' . implode(',', $this->getAllIds()) . ')');

        return $this;
    }
}