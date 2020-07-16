<?php
/**
 * Photogallery extension
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
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Photogallery_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function initPhotogalleryControllerRouters($observer)
    {            
        $front = $observer->getEvent()->getFront();
        $router = new FME_Photogallery_Controller_Router();
        $front->addRouter('photogallery', $router);
        
    }


     public function match(Zend_Controller_Request_Http $request)
     {
 
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            return true;
        }

        $route = Mage::helper('photogallery')->getListIdentifier();
        $identifier = trim($request->getPathInfo(), '/'); 
        $identifier = str_replace(
            Mage::helper('photogallery')
            ->getSeoUrlSuffix(), '', $identifier
        );
        $CheckPage = explode('/', $identifier);
        if (count($CheckPage)==1) {
            if ($route==$CheckPage[0]) {
                $request->setModuleName('photogallery')
                ->setControllerName('view')
                ->setActionName('index');
                return true;
            }
        } elseif (count($CheckPage)==2) {
            $gallery= Mage::getModel('photogallery/gallery')
                ->load($CheckPage[1], 'gallery_identifier')->getId();
            if ($gallery) {
                $request->setModuleName('photogallery')
                    ->setControllerName('index')
                    ->setActionName('index')
                    ->setParam('id', $gallery);
                return true;
            }

            return false;
        }
 
        return false;

     }
}