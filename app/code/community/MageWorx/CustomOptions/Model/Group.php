<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_Group extends Mage_Core_Model_Abstract {

    protected function _construct() {
        parent::_construct();
        $this->_init('mageworx_customoptions/group');
    }

    public function getActiveGruopsIds($store = null) {
        $result = array();
        $collection = $this->getResourceCollection()->addStatusFilter();

        $items = $collection->getItems();
        if ($items) {
            foreach ($items as $value) {
                $result[] = $value->getGroupId();
            }
        }
        return $result;
    }
    
    public function duplicate() {
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $table = $tablePrefix . 'mageworx_custom_options_group';
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $connection->query("INSERT INTO {$table} (title, is_active, hash_options) 
                    SELECT title, is_active, hash_options 
                    FROM {$table} 
                    WHERE group_id = '{$this->getId()}'");
        $newGroupId = $connection->lastInsertId();
        if (!$newGroupId) return false;
        
        // duplicate store group data
        $groupStoreCollection = Mage::getResourceModel('mageworx_customoptions/group_store_collection')->addFieldToFilter('group_id', $this->getId());
        if (count($groupStoreCollection)>0) {
            $groupStoreNew = Mage::getModel('mageworx_customoptions/group_store');
            foreach ($groupStoreCollection as $groupStore) {
                $groupStoreNew->setData($groupStore->getData())->setGroupStoreId(null)->setGroupId($newGroupId)->save();
            }
        }
                    
        return $newGroupId;
    }

    public function delete() {
        $hashOptions = unserialize($this->getHashOptions());
        if (is_array($hashOptions) && !empty($hashOptions)) {
            foreach ($hashOptions as $hashOption) {
                Mage::helper('mageworx_customoptions')->deleteOptionFile($this->getId(), $hashOption['id']);
                @rmdir(Mage::helper('mageworx_customoptions')->getCustomOptionsPath($this->getId()));
            }
        }

        parent::delete();
    }

    public function getStoreValues($store = null) {
        $result[] = array(
            'label' => Mage::helper('mageworx_customoptions')->__('None'),
            'value' => ''
        );
        $collection = $this->getResourceCollection()->addSortOrder();

        $items = $collection->getItems();
        if ($items) {
            foreach ($items as $value) {
                $result[] = array(
                    'label' => $value->getTitle() . ($value->getIsActive()==2?' (disabled)':''),
                    'value' => $value->getGroupId(),
                );
            }
        } else {
            return '';
        }
        return $result;
    }

}
