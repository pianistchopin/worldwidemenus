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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Yesno
    extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {
        $helper = Mage::helper('exitoffer');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('YES/NO Buttons Settings')));

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
            'value' => Mage::getStoreConfig('exitoffer/popup_yesno/text_1')
        ));

        $fieldset->addField('text_2', 'text', array(
            'label' => $helper->__('Text Line 2'),
            'required' => false,
            'name' => 'popup[text_2]',
            'value' => Mage::getStoreConfig('exitoffer/popup_yesno/text_2')
        ));

        $fieldset->addField('text_3', 'text', array(
            'label' => $helper->__('Text Line 3'),
            'required' => false,
            'name' => 'popup[text_3]',
            'value' => Mage::getStoreConfig('exitoffer/popup_yesno/text_3')

        ));


        $fieldset->addField('text_5', 'text', array(
            'label' => $helper->__('Button YES Text 1'),
            'required' => false,
            'name' => 'popup[text_5]',
            'value' => Mage::getStoreConfig('exitoffer/popup_yesno/text_5')
        ));

        $fieldset->addField('text_6', 'text', array(
            'label' => $helper->__('Button YES Text 2'),
            'required' => false,
            'name' => 'popup[text_6]',
            'value' => Mage::getStoreConfig('exitoffer/popup_yesno/text_6')
        ));

        $fieldset->addField('text_7', 'text', array(
            'label' => $helper->__('Button NO Text 1'),
            'required' => false,
            'name' => 'popup[text_7]',
            'value' => Mage::getStoreConfig('exitoffer/popup_yesno/text_7')
        ));

        $fieldset->addField('text_8', 'text', array(
            'label' => $helper->__('Button NO Text 2'),
            'required' => false,
            'name' => 'popup[text_8]',
            'value' => Mage::getStoreConfig('exitoffer/popup_yesno/text_8')
        ));

        $currentObj = Mage::registry('exitoffer_popup_data');
        if ($currentObj && $currentObj->getId()) {
            $form->setValues($currentObj->getData());
        }

        return parent::_prepareForm();
    }
}