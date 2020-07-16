<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_Resource_Inbox_Expired_Collection extends Mage_AdminNotification_Model_Mysql4_Inbox_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('is_remove', 0)
            ->addFieldToFilter('is_amasty', 1)
            ->addFieldToFilter('expiration_date', array('neq' => 'NULL'))
            ->addFieldToFilter('expiration_date', array('lt' => 'NOW()'));

        return $this;
    }
}
