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
 
 

class FME_Photogallery_Model_Mysql4_Gallery_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('photogallery/gallery');
    }

   

    /*Add Store Filter */
    public function addStoreFilter($store, $withAdmin = true)
    {
    if ($store instanceof Mage_Core_Model_Store) {
        $store = array($store->getId());
    }
   

    if (!is_array($store)) {
        $store = array($store);
    }

    $allstores = array('eq'=>0);
    $storeids = array('finset'=>$store);
    $this->addFieldToFilter('store_id', array($allstores,$storeids));
    return $this;
    }

    

}
