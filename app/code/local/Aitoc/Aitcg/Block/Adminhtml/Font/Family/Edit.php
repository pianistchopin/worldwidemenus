<?php

class Aitoc_Aitcg_Block_Adminhtml_Font_Family_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'aitcg';
        $this->_controller = 'adminhtml_font_family';
 
        $this->_updateButton('save', 'label', Mage::helper('aitcg')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('aitcg')->__('Delete Item'));
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('font_family_data') && Mage::registry('font_family_data')->getId() ) {
            return Mage::helper('aitcg')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('font_family_data')->getTitle()));
        } else {
            return Mage::helper('aitcg')->__('Add Item');
        }
    }
}