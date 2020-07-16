<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Block_Adminhtml_Reports extends MageWorx_CustomOptions_Block_Adminhtml_Abstract {

    protected function _prepareLayout() {        
        $this->setChild('reports_grid', $this->getLayout()->createBlock('mageworx_customoptions/adminhtml_reports_grid', 'customoptions.reports.grid'));
        
        return parent::_prepareLayout();
    }
    
    public function getGridHtml() {
        return $this->getChildHtml('reports_grid');
    }

}