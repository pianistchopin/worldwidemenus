<?php
     
    class Aitoc_Aitcg_Block_Adminhtml_Font_Family extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            $this->_controller = 'adminhtml_font_family';
            $this->_blockGroup = 'aitcg';
            $this->_headerText = Mage::helper('aitcg')->__('Font Families Manager');
            $this->_addButtonLabel = Mage::helper('aitcg')->__('Add Item');
            parent::__construct();
        }
    }