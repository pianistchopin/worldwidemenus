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
 
 

class FME_Photogallery_Model_Option extends Mage_Core_Model_Abstract
{
    
    
    public function getOptions()
    { 
        $options = array(); 
        $collection = Mage::getModel('photogallery/photogallery')->getCollection()->prepareSummary();      
        $i=0;    
        foreach ($collection as $item) {
         $options[$i]['label'] = $item->getGal_name();
         $options[$i]['value'] = $item->getPhotogallery_id();
    
            $i++;
        }

        $this->_options = $options;           
        return $options;
    } 
    
     public function getGalname()
     { 
        $gal = array(); 
        $collection = Mage::getModel('photogallery/photogallery')->getCollection()->getGal();     
            
        foreach ($collection as $item) {              
         $gal[$item->getPhotogallery_id()] = $item->getGal_name();            
        }

        $this->_gal = $gal;           
        return $gal;
     } 
    
    
}
