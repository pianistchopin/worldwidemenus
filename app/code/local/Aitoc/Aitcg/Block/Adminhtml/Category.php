<?php
     
    class Aitoc_Aitcg_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            $this->_controller = 'adminhtml_category';
            $this->_blockGroup = 'aitcg';
            $this->_headerText = Mage::helper('aitcg')->__('Clipart Category Manager');
            $this->_addButtonLabel = Mage::helper('aitcg')->__('Add Category');
            parent::__construct();
        }
    }