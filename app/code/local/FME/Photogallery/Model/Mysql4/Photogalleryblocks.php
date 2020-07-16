<?php
/**
 * Media Photogallery & Product Videos extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Mediaappearance
 * @copyright  Copyright 2010 � free-magentoextensions.com All right reserved
 **/

class FME_Photogallery_Model_Mysql4_Photogalleryblocks extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('photogallery/photogalleryblocks', 'photogallery_block_id');
    }
    
     /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param   string $identifier
     * @param   int $storeId
     * @return  int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()), 'photogallery_block_id')
            ->where('main_table.identifier = ?', $identifier)
            ->where('main_table.status = 1');
            
        return $this->_getReadAdapter()->fetchOne($select);
    }
    
    public function load(Mage_Core_Model_Abstract $object, $value, $field=null)
    {

        if (!intval($value) && is_string($value)) {
            $field = 'block_identifier';
        }

        return parent::load($object, $value, $field);
    }
    
    /**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
            
        $condition = $this->_getWriteAdapter()->quoteInto('photogallery_block_id = ?', $object->getId());
        //Get Related Media
        if (Mage::app()->getRequest()->getPost('links')) {
                 $links = Mage::app()->getRequest()->getPost('links');
            $photogalleryIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['relatedphotogallery']);
            $this->_getWriteAdapter()->delete($this->getTable('photogallery_block_gimages'), $condition);
        
        
            //Save Related Articles
             foreach ($photogalleryIds as $_photogallery) {
                $photogalleryArray = array();
                $photogalleryArray['photogallery_block_id'] = $object->getId();
                $photogalleryArray['img_id'] = $_photogallery;
                $this->_getWriteAdapter()->insert($this->getTable('photogallery_block_gimages'), $photogalleryArray);
             }
        } 
        
        return parent::_afterSave($object);
        
    }
    
    /**
     * Check for unique of identifier of block.
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function getIsUniqueBlockToStores(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getWriteAdapter()->select()
                ->from($this->getMainTable())
                ->join(array('cbs' => $this->getTable('cms/block_store')), $this->getMainTable().'.block_id = `cbs`.block_id')
                ->where($this->getMainTable().'.identifier = ?', $object->getData('identifier'));
        if ($object->getId()) {
            $select->where($this->getMainTable().'.block_id <> ?', $object->getId());
        }

        $select->where('`cbs`.store_id IN (?)', (array)$object->getData('stores'));

        if ($this->_getWriteAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }
    
}
