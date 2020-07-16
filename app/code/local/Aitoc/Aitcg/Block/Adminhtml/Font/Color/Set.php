    <?php
     
    class Aitoc_Aitcg_Block_Adminhtml_Font_Color_Set extends Mage_Adminhtml_Block_Widget_Grid_Container
    {
        public function __construct()
        {
            $this->_controller = 'adminhtml_font_color_set';
            $this->_blockGroup = 'aitcg';
            $this->_headerText = Mage::helper('aitcg')->__('Fonts Color Set Manager');
            $this->_addButtonLabel = Mage::helper('aitcg')->__('Add Color Set');
            parent::__construct();
        }
    }