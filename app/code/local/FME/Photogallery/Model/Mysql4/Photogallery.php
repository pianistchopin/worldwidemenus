<?php
/**
 * Photo Photogallery & Products Photogallery extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Photogallery
 * @copyright  Copyright 2010 � free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */
 
 
class FME_Photogallery_Model_Mysql4_Photogallery extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('photogallery/photogallery', 'photogallery_id');
    }
    
     protected function _afterLoad(Mage_Core_Model_Abstract $object)
     {
        
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('photogallery_store'))
            ->where('photogallery_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }

            $object->setData('store_id', $storesArray);
        }
    
    //Get Category Ids
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('photogallery_products'))
            ->where('photogallery_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $productsArray = array();
            foreach ($data as $row) {
                $productsArray[] = $row['product_id'];
            }

            $object->setData('product_id', $productsArray);
        }

        return parent::_afterLoad($object);
        
     }
     public function lookupStoreIds($photogalleryId)
     {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('photogallery_store'), 'store_id')
            ->where('photogallery_id = ?', (int)$photogalleryId);
        return $adapter->fetchCol($select);
     }
    
    /**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        
        $condition = $this->_getWriteAdapter()->quoteInto('photogallery_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('photogallery_products'), $condition);
        //Get All Selected Categories
        if(Mage::app()->getRequest()->getPost('productIds')) { 
                
            
            $productIdsString = Mage::app()->getRequest()->getPost('productIds');
            
            $productIdsString = trim($productIdsString, ',');    
            
            // if(Mage::app()->getRequest()->getPost('productIds')) {
            //     if($productIdsString != '') {
            //         $photogalleryProductsTable = Mage::getSingleton('core/resource')->getTableName('photogallery_products');
            //         $db = $this->_getWriteAdapter();
            //         try {
            //                 $db->beginTransaction();
            //                 $db->exec("DELETE FROM ".$photogalleryProductsTable." WHERE product_id in ($productIdsString) and photogallery_id='".$object->getId()."'");
            //                 $db->commit();
            //         } catch(Exception $e) {
            //             $db->rollBack();
            //             throw new Exception($e);
            //         }
            //     }
            // }

            $productIds = explode(",", $productIdsString);    
            $Result = array_unique($productIds);
                    
             foreach ($Result as $_productId) {
                $productsArray = array();
                $productsArray['photogallery_id'] = $object->getId();
                $productsArray['product_id'] = $_productId;
                $this->_getWriteAdapter()->insert($this->getTable('photogallery_products'), $productsArray);
             }
        }
        
        if(Mage::app()->getRequest()->getPost('stores')) {
             $this->_getWriteAdapter()->delete($this->getTable('photogallery_store'), $condition);
            foreach ((array)Mage::app()->getRequest()->getPost('stores') as $store) {
                $storeArray = array();
                $storeArray['photogallery_id'] = $object->getId();
                $storeArray['store_id'] = $store;
                $this->_getWriteAdapter()->insert($this->getTable('photogallery_store'), $storeArray);
            }
        }
    
    
        $this->_getWriteAdapter()->delete($this->getTable('photogallery_store'), $condition);
    
        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['photogallery_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert($this->getTable('photogallery_store'), $storeArray);
        }
    
        return parent::_afterSave($object);
        
    }


}
