<?php

class Aitoc_Aitcg_Block_Adminhtml_Font_Family_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            )
        );

        $fieldset = $form->addFieldset('font_form', array('legend' => Mage::helper('aitcg')->__('Item information')));

        $fieldset->addField(
            'title', 'text', array(
                'label'    => Mage::helper('aitcg')->__('Name'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'title',
            )
        );

        $fileNote = 'A zip file should include properly named font files.';
        $fileNote .= 'Two options are possible: 1. "[font family]-[style].ttf", i.e. "Palatino-italic.ttf".';
        $fileNote .= '2. "style.ttf", i.e. "italic.ttf".';

        $fieldset->addField(
            'filename', 'file', array(
                'label'    => Mage::helper('aitcg')->__('Font family archive'),
                'required' => false,
                'name'     => 'filename',
                'note'     => $fileNote,
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        if (Mage::registry('font_family_data')) {
            $form->setValues(Mage::registry('font_family_data')->getData());
        }

        return parent::_prepareForm();
    }
}
