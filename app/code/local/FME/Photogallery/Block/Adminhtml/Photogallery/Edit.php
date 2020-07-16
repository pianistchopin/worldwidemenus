<?php
/**
 * Photo Photogallery & Products Photogallery extension
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
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */
 
 
class FME_Photogallery_Block_Adminhtml_Photogallery_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
         parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'photogallery';
        $this->_controller = 'adminhtml_photogallery';
        
        $this->_updateButton('save', 'label', Mage::helper('photogallery')->__('Save Photogallery'));
        $this->_updateButton('delete', 'label', Mage::helper('photogallery')->__('Delete Photogallery'));
        
        $this->_addButton(
            'saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
            ), -100
        );
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        
    }

    public function getHeaderText()
    {
    
        if(Mage::registry('photogallery_data') && Mage::registry('photogallery_data')->getId()) {
            return Mage::helper('photogallery')->__("Edit Photogallery '%s'", $this->htmlEscape(Mage::registry('photogallery_data')->getGal_name()));
        } else {
            return Mage::helper('photogallery')->__('Add Photogallery');
        }
    }
}
