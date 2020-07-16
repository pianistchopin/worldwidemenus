<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Block_Adminhtml_Options_Edit_Tab_Options extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('mageworx/customoptions/catalog-product-edit-options.phtml');
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setChild('general_box', $this->getLayout()->createBlock('mageworx_customoptions/adminhtml_options_edit_tab_general'));
        $this->setChild('options_box', $this->getLayout()->createBlock('mageworx_customoptions/adminhtml_options_edit_tab_options_option'));
        $this->getChild('options_box')->getChild('select_option_type');
        return $this;
    }
    
    public function getSkuPolicySelectHtml($value) {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
                ->setData(array('class' => 'select'))
                ->setName('general[sku_policy]')
                ->setOptions(Mage::getSingleton('mageworx_customoptions/system_config_source_sku_policy')->toOptionArray(1))
                ->setValue($value);
        return $select->getHtml();
    }

    public function isPredefinedOptions() {
        return false;
    }

}