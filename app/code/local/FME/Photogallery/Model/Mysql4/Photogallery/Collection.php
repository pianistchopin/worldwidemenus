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
 
 

class FME_Photogallery_Model_Mysql4_Photogallery_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('photogallery/photogallery');
    }

    public function prepareSummary($iden)
    {
            $this->setConnection($this->getResource()->getReadConnection());
            $this->getSelect()
                ->from(array('main_table'=>$this->getTable('photogallery')), '*')
                ->where('status = ?', 1)
                ->where('parent_gallery_id = ?', $iden)
                ->where('show_in = 1 OR show_in  = 3')
                ->order('gorder', 'asc');
            return $this;
    }
    
    
    /**
     * Retrieve Last Photogallery Id
     *
     * @return array
     */
    public function getLastId()
    {
    $this->setConnection($this->getResource()->getReadConnection());
    $this->getSelect()
        ->from(array('pg' => $this->getTable('photogallery')), 'photogallery_id')
        ->order('pg.photogallery_id DESC')
        ->limit(1, 0);
    return $this;
    }
    
   
    

    public function getDetalle($photogallery_id)
    {
        $this->setConnection($this->getResource()->getReadConnection());
        $this->getSelect()
            ->from(array('main_table'=>$this->getTable('photogallery')), '*')
            ->where('photogallery_id = ?', $photogallery_id);
        return $this;
    }
    
    public function getPhotogallery()
    {
            $this->setConnection($this->getResource()->getReadConnection());
            $this->getSelect()
                ->from(array('main_table'=>$this->getTable('photogallery')), '*')
                ->where('status = ?', 1)
                ->where('show_in = 1 OR show_in  = 3')
                ->order('gorder', 'ASC');
                //->order('date DESC')
                //->limit(5);
            return $this;
    }
    
     
    public function toOptionArray()
    {
        return $this->_toOptionArray('photogallery_id', 'title');
    }
    
    public function addPhotogalleryFilter($photogallery)
    {
        if (is_array($photogallery)) {
            $condition = $this->getConnection()->quoteInto('main_table.photogallery_id IN(?)', $photogallery);
        }
        else {
            $condition = $this->getConnection()->quoteInto('main_table.photogallery_id=?', $photogallery);
        }

        return $this->addFilter('photogallery_id', $condition, 'string');
    }
    
    /**
     * Retrieve Photogallery Images
     *
     * @return array
     */
    public function getImages($photogalleryId)
    {
        $this->setConnection($this->getResource()->getReadConnection());
        $this->getSelect()
            ->from(array('main_table'=>Mage::getSingleton('core/resource')->getTableName('photogallery_images')), '*')
            ->where('photogallery_id = ?', $photogalleryId)
            ->order('img_order', 'ASC');
        return $this;
    }
    
    /**
     * Retrieve Product Galleries
     *
     * @return array
     */
    public function getPgalleries($productId)
    {
    
    $this->setConnection($this->getResource()->getReadConnection());
    $this->getSelect()
        ->from(array('p' => $this->getTable('photogallery_products')), 'p.photogallery_id')
        ->joinLeft(
            array('g' => $this->getTable('photogallery')),
            'g.photogallery_id = p.photogallery_id', '*'
        )
        ->where('p.product_id = ?', $productId)
        ->where('g.show_in = 2 OR g.show_in = 3')
        ->order('g.gorder', 'ASC');
        
    return $this;
    }
    
    
    
    /**
     * Retrieve Product Photogallery Images
     *
     * @return array
     */
    public function getPimages($photogalleryIds)
    {
    $this->setConnection($this->getResource()->getReadConnection());
    $this->getSelect()
        ->from(array('g' => $this->getTable('photogallery_images')), '*')
        ->where('g.photogallery_id IN (?)', $photogalleryIds)
        ->order('g.img_order', 'ASC');
    return $this;
    }
    
    /*start Gallery Images shahzad*/
    public function getItemImages()
    {
    $this->setConnection($this->getResource()->getReadConnection());
    $this->getSelect()
        ->from(array('main_table'=> $this->getTable('photogallery_images')), '*')
        ->where('photogallery_id = ?', $id)
        ->order('img_order ASC');
    return $this;
    }
    /*end of gallery images shahzad*/

     public function addStoreFilter($store)
     {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        $this->getSelect()->join(
            array('store_table' => $this->getTable('photogallery_store')),
            'main_table.photogallery_id = store_table.photogallery_id',
            array()
        )
        ->where('store_table.store_id in (?)', array(0, $store));

        return $this;
     }

    protected function _renderFiltersBefore()
    {
        
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('photogallery/photogallery_store')),
                'main_table.photogallery_id = store_table.photogallery_id',
                array()
            )->group('main_table.photogallery_id');
            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }

        return parent::_renderFiltersBefore();
    }

}
