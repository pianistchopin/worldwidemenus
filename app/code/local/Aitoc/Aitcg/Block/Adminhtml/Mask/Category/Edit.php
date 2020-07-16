<?php

class Aitoc_Aitcg_Block_Adminhtml_Mask_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'aitcg';
        $this->_controller = 'adminhtml_mask_category';
 
        $this->_updateButton('save', 'label', Mage::helper('aitcg')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('aitcg')->__('Delete Item'));
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('category_data') && Mage::registry('category_data')->getId() ) {
            return Mage::helper('aitcg')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('category_data')->getName()));
        } else {
            return Mage::helper('aitcg')->__('Add Item');
        }
    }
}