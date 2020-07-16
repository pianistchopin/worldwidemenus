<?php

class Aitoc_Aitcg_Block_Adminhtml_Category_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                                        'id' => 'edit_form',
                                        'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                        'method' => 'post',
                                     )
        );
        
        $fieldset = $form->addFieldset('category_form', array('legend'=>Mage::helper('aitcg')->__('Item information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('aitcg')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
        
        $fieldset->addField('description', 'textarea', array(
            'label'     => Mage::helper('aitcg')->__('Description'),
            'name'      => 'description',
        ));   
        

        $form->setUseContainer(true);
        $this->setForm($form);
        
        if ( Mage::registry('category_data') ) {
            $form->setValues(Mage::registry('category_data')->getData());
        }        
        
        $fieldset = $form->addFieldset('store_labels_fieldset', array(
            'legend'       => Mage::helper('salesrule')->__('Store View Specific Labels'),
            'table_class'  => 'form-list stores-tree',
        ));
        $renderer = $this->getLayout()->createBlock('aitcg/store_switcher_form_renderer_fieldset');
        $fieldset->setRenderer($renderer);

        if ( Mage::registry('category_data') ) {
            $labels = Mage::registry('category_data')->getStoreLabels();
        }        
        
        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}", 'text', array(
                        'name'      => 'store_labels['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }
        return parent::_prepareForm();
    }
}
