<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Block_Adminhtml_Options_Edit_Tab_Options_Groups extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        if (Mage::helper('mageworx_customoptions')->isEnabled()) {
            $product = Mage::registry('product');
            if ($product->getTypeId() == 'bundle' && $product->getPriceType() == 0) {
                return $this;
            }
            $values = Mage::getSingleton('mageworx_customoptions/group')->getStoreValues($product->getStoreId());

            $form = new Varien_Data_Form();
            $form->addField('customoptions_groups', 'multiselect', array(
                //'label' => Mage::helper('mageworx_customoptions')->__('Options Templates'),
                'title' => Mage::helper('mageworx_customoptions')->__('Options Templates'),
                'name' => 'customoptions[groups][]',
                'values' => $values,
                'value' => Mage::getResourceSingleton('mageworx_customoptions/relation')->getGroupIds($product->getId()),
                'style' => 'width: 280px; height: 112px;',
            ));
            $this->setForm($form);
        }    

        return parent::_prepareForm();
    }

}