<?php

class Aitoc_Aitcg_TipsController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
       $this->loadLayout()
            ->_setActiveMenu('catalog/aitcg/tips')
               
            ->_addBreadcrumb(Mage::helper('aitcg')->__('Aitoc Custom Product Preview Font Color'), Mage::helper('aitcg')->__('Aitoc Custom Product Preview Font Color'));
        return $this;
    }   

    public function indexAction() 
    {
        //$this->loadLayout();
        $this->_initAction();       
        
        //$this->_addContent($this->getLayout()->getBlock());
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aitcg/easy_tips');
    }
    

}