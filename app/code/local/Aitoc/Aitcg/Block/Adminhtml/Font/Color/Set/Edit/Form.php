<?php

class Aitoc_Aitcg_Block_Adminhtml_Font_Color_Set_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        $this->_prepareFormAfterChild();
        return parent::_prepareLayout();
    }
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));
        
        $fieldset = $form->addFieldset('font_form', array('legend'=>Mage::helper('aitcg')->__('Color Set information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('aitcg')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
        
        $fieldset->addField('value', 'hidden', array(
            'name'      => 'value',
        ));

	    $fieldset->addField('label', 'hidden', array(
		    'name'      => 'label',
	    ));
        
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('aitcg')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('aitcg')->__('Active'),
                ),
 
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('aitcg')->__('Inactive'),
                ),
            ),
        ));   

        $form->setUseContainer(true);
        $this->setForm($form);
        
        if ( Mage::registry('aitcg_font_color_set_data') ) {
            $form->setValues(Mage::registry('aitcg_font_color_set_data')->getData());
        }

        
        return parent::_prepareForm();
    }
    
    protected function _prepareFormAfterChild()
    {
        $this->setChild('form_after',
            $this->getLayout()->createBlock('aitcg/adminhtml_font_color_set_editor')
        );
    }
}
