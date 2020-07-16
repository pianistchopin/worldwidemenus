<?php

class Aitoc_Aitcg_Block_Adminhtml_Font_Color_Set_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'aitcg';
        $this->_controller = 'adminhtml_font_color_set';
 
        $this->_updateSaveButton();
        $this->_updateButton('delete', 'label', Mage::helper('aitcg')->__('Delete Item'));
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('aitcg_font_color_set_data') && Mage::registry('aitcg_font_color_set_data')->getId() ) {
            return Mage::helper('aitcg')->__("Edit Color Set '%s'", $this->htmlEscape(Mage::registry('aitcg_font_color_set_data')->getName()));
        } else {
            return Mage::helper('aitcg')->__('Add Color Set');
        }
    }
    
    protected function _updateSaveButton()
    {
        $this->_updateButton('save', 'label', Mage::helper('aitcg')->__('Save Item'));
        $this->_updateButton('save', 'onclick', 'colorsetEditor.updateSourceValue(); editForm.submit();');
    }
}