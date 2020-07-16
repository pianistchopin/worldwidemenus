<?php

class Aitoc_Aitcg_Block_Adminhtml_Image_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                                        'id' => 'edit_form',
                                        'action' => $this->getUrl('*/*/save', array('id' => Mage::app()->getRequest()->getParam('id'), 'imgid' => $this->getRequest()->getParam('imgid'))),
                                        'method' => 'post',
                                        'enctype' => 'multipart/form-data',
                                     )
        );
        
        $fieldset = $form->addFieldset('image_form', array('legend'=>Mage::helper('aitcg')->__('Item information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('aitcg')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
        
        $fieldset->addField('filename', 'file', array(
                  'label'     => Mage::helper('aitcg')->__('Image file'),
                  'required'  => false,
                  'name'      => 'filename',
        ));
        $fieldset->addField('embossfilename', 'file', array(
            'label'     => Mage::helper('aitcg')->__('Emboss image file'),
            'required'  => false,
            'name'      => 'embossfilename',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        
        if ( Mage::registry('image_data') ) {
            $form->setValues(Mage::registry('image_data')->getData());
        }

        
        return parent::_prepareForm();
    }
}
