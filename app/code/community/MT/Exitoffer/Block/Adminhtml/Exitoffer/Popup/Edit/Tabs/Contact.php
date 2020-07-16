<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Contact
    extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {
        $helper = Mage::helper('exitoffer');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('Contact Popup Settings')));

        $templateList =Mage::getModel('adminhtml/system_config_source_email_template')->toOptionArray();
        //unset($templateList[0]);
        $fieldset->addField('email_template', 'select', array(
            'label' => $helper->__('Transaction Email Template'),
            'required' => false,
            'name' => 'popup[email_template]',
            'values' => $templateList
        ));




        $fieldset->addField('status', 'select', array(
            'label' => $helper->__('Theme'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'popup[theme]',
            'values' => Mage::getModel('exitoffer/adminhtml_system_config_nsf_theme')->toOptionArray()
        ));


        $fieldset->addField('text_1', 'text', array(
            'label' => $helper->__('Text Line 1'),
            'required' => false,
            'name' => 'popup[text_1]',
            'value' => Mage::getStoreConfig('exitoffer/popup_contact/text_1')
        ));

        $fieldset->addField('text_2', 'text', array(
            'label' => $helper->__('Text Line 2'),
            'required' => false,
            'name' => 'popup[text_2]',
            'value' => Mage::getStoreConfig('exitoffer/popup_contact/text_2')
        ));

        $fieldset->addField('text_3', 'text', array(
            'label' => $helper->__('Text Line 3'),
            'required' => false,
            'name' => 'popup[text_3]',
            'value' => Mage::getStoreConfig('exitoffer/popup_contact/text_3')

        ));

        $currentObj = Mage::registry('exitoffer_popup_data');
        if ($currentObj && $currentObj->getId()) {
            $form->setValues($currentObj->getData());
        }

        return parent::_prepareForm();
    }
}