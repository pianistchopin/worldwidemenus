<?php
/**
 * Photogallery Photogallery & Product Gimages extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Photogallery
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 **/

class FME_Photogallery_Block_Adminhtml_Photogalleryblocks_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'photogallery_block_id';
        $this->_blockGroup = 'photogallery';
        $this->_controller = 'adminhtml_photogallery';
        
        $this->_updateButton('save', 'label', Mage::helper('photogallery')->__('Save Photogallery Block'));
        $this->_updateButton('delete', 'label', Mage::helper('photogallery')->__('Delete Photogallery Block'));
        
       
    }

    public function getHeaderText()
    {
        if(Mage::registry('photogallery_block_data') && Mage::registry('photogallery_block_data')->getId()) {
            return Mage::helper('photogallery')->__("Edit Photogallery Block '%s'", $this->htmlEscape(Mage::registry('photogallery_block_data')->getBlockTitle()));
        } else {
            return Mage::helper('photogallery')->__('Add Photogallery Block');
        }
    }
}
