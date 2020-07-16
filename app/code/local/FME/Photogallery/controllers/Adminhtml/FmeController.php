<?php

class FME_Photogallery_Adminhtml_FmeController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
       $this->loadLayout()
            ->_setActiveMenu('FME_Extensions')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Photogallery Blocks'), Mage::helper('adminhtml')->__('Manage Photogallery Blocks'));
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return true;
    }
}
