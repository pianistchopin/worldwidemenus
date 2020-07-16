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
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */
 

class FME_Photogallery_Model_Gallery extends Mage_Core_Model_Abstract
{
        
    public function _construct()
    {
        parent::_construct();
        $this->_init('photogallery/gallery');
    }
    
    /**
     * Retrieve related products
     *
     * @return array
     */
    public function getPhotogalleryRelatedProducts($photogalleryId)
    {
                
            $photogallery_productsTable = Mage::getSingleton('core/resource')->getTableName('photogallery_products');
            $collection = Mage::getModel('photogallery/photogallery')->getCollection()
                      ->addPhotogalleryFilter($photogalleryId);
                      
            $collection->getSelect()
            ->joinLeft(
                array('related' => $photogallery_productsTable),
                'main_table.photogallery_id = related.photogallery_id'
            )
            ->order('main_table.photogallery_id');
            
            return $collection->getData();

    }
    
    public function checkPhotogallery($id)
    {
        return $this->_getResource()->checkPhotogallery($id);
    }
    
    /*
     * Delete Photogallery Stores
     * @return Array
     */
    public function deletePhotogalleryStores($id)
    {
        return $this->getResource()->deletePhotogalleryStores($id);
        
    }
    
    /*
     * Delete Photogallery Product Links
     * @return Array
     */
    public function deletePhotogalleryProductLinks($id)
    {
        return $this->getResource()->deletePhotogalleryProductLinks($id);
        
    }
    
    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param   string $identifier
     * @param   int $storeId
     * @return  int
     */
    public function checkIdentifier($identifier)
    {
        return $this->_getResource()->checkIdentifier($identifier);
    }
    


}
