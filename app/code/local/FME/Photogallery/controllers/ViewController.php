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
 
class FME_Photogallery_ViewController extends Mage_Core_Controller_Front_Action
{

	public function preDispatch()
    {
        parent::preDispatch();
        if(!Mage::helper('photogallery')->getEnable()) {
         Mage::getSingleton('core/session')->addError(Mage::helper('photogallery')->__('Sorry This Feature is disabled temporarily'));
         $this->norouteAction();
        }
    }
    public function indexAction()
    {    
        // echo "album"; exit();
           
       
        $this->loadLayout();            
        $this->renderLayout();
    }
}
