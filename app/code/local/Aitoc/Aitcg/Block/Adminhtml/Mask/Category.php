<?php
     
    class Aitoc_Aitcg_Block_Adminhtml_Mask_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            $this->_controller = 'adminhtml_mask_category';
            $this->_blockGroup = 'aitcg';
            $this->_headerText = Mage::helper('aitcg')->__('Shape Masks Category Manager');
            $this->_addButtonLabel = Mage::helper('aitcg')->__('Add Shape Category');
            parent::__construct();
        }
    }