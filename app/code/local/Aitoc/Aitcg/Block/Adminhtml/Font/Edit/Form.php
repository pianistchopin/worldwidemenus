<?php

class Aitoc_Aitcg_Block_Adminhtml_Font_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
            'name', 'text', array(
                'label'    => Mage::helper('aitcg')->__('Name'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'name',
            )
        );

        $fieldset->addField(
            'status', 'select', array(
                'label'  => Mage::helper('aitcg')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('aitcg')->__('Active'),
                    ),

                    array(
                        'value' => 0,
                        'label' => Mage::helper('aitcg')->__('Inactive'),
                    ),
                ),
            )
        );

        $fieldset->addField(
            'font_family_id', 'select', array(
                'required' => true,
                'label'    => Mage::helper('aitcg')->__('Font family'),
                'name'     => 'font_family_id',
                'values'   => Mage::getSingleton('aitcg/system_config_source_product_options_font_family')
                    ->toOptionArray(),
            )
        );

        $fieldset->addField(
            'new_font_family', 'text', array(
                'label'    => Mage::helper('aitcg')->__('New font family'),
                'required' => false,
                'name'     => 'new_font_family',
            )
        );

        $fieldset->addField(
            'filename', 'file', array(
                'label'    => Mage::helper('aitcg')->__('Font file'),
                'required' => false,
                'name'     => 'filename',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        if (Mage::registry('font_data')) {
            $form->setValues(Mage::registry('font_data')->getData());
        }


        return parent::_prepareForm();
    }
}
