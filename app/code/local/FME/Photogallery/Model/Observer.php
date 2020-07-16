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
 
 

class FME_Photogallery_Model_Observer
{
    
    
   public function addToTopmenu($observer)
   {
    $menu = $observer->getMenu();
    $tree = $menu->getTree();
 
    $node = new Varien_Data_Tree_Node(
        array(
            'name'   => Mage::helper('photogallery')->getListPageTitle(),
            'url'    => Mage::getUrl().Mage::helper('photogallery')->getListIdentifier().Mage::helper('photogallery')
            ->getSeoUrlSuffix(),
            'id'     => 'photogallery',
            
        ), 'id', $tree, $menu
    );
    //$menu->addChild($node);
    $collection = Mage::getModel('photogallery/gallery')->getCollection()
                  ->addStoreFilter(Mage::app()->getStore(true)->getId())
                  ->addFieldToFilter('status', 1);
    foreach ($collection as $category) {
        $tree = $node->getTree();
        $data = array(
            'name'   => $category->getGalleryTitle(),
            'id'     => 'category-node-'.$category->getGalleryId(),
            'url'    => Mage::getUrl().Mage::helper('photogallery')->getListIdentifier().DS.$category->getGalleryIdentifier(),
        );
 
        $subNode = new Varien_Data_Tree_Node($data, 'id', $tree, $node);
        //$node->addChild($subNode);
    }
   }
    
    
}
