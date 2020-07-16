<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Type_Text extends
Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Type_Text {

    public function __construct() {
        parent::__construct();
        if (!Mage::helper('mageworx_customoptions')->isEnabled()) return $this;
        $this->setTemplate('mageworx/customoptions/catalog-product-edit-options-type-text.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('add_select_row_button',
                        $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('catalog')->__('Add New Row'),
                            'class' => 'add add-select-row',
                            'id' => 'add_select_row_button_{{option_id}}',
                        ))
        );

        $this->setChild('delete_select_row_button',
                        $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('catalog')->__('Delete Row'),
                            'class' => 'delete delete-select-row icon-btn',
                        ))
        );

        $this->setChild('add_image_button',
                        $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => '{{image_button_label}}',
                            'class' => 'add',
                            'id' => 'new-option-file-{{option_id}}',
                            'onclick' => 'productOptionTypeText.createFileField(this.id)'
                        )));

        return parent::_prepareLayout();
    }

    public function getAddImageButtonHtml() {
        return $this->getChildHtml('add_image_button');
    }

}
