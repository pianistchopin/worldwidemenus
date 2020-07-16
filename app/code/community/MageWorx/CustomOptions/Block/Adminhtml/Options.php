<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Block_Adminhtml_Options extends MageWorx_CustomOptions_Block_Adminhtml_Abstract {

    protected function _prepareLayout() {
        $this->setChild('add_new_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('mageworx_customoptions')->__('Add Options Template'),
                            'onclick' => "setLocation('" . $this->getUrl('*/*/new', array('store' => $this->getStoreId())) . "')",
                            'class' => 'add'
                        ))
        );

        $this->setChild('grid', $this->getLayout()->createBlock('mageworx_customoptions/adminhtml_options_grid', 'customoptions.grid'));
        
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml() {
        return $this->getChildHtml('add_new_button');
    }
    
    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }

}