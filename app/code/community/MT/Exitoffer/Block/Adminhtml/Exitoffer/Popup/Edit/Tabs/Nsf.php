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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Nsf
    extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {
        $helper = Mage::helper('exitoffer');



        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('Newsletter Subscription Form Settings')));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Theme'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'popup[theme]',
            'values' => Mage::getModel('exitoffer/adminhtml_system_config_nsf_theme')->toOptionArray()
        ));

        $fieldset->addField('show_coupon_code', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Show Coupon Code in Popup after Subscription'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'popup[show_coupon_code]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $fieldset->addField('use_captcha', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Show Captcha'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'popup[use_captcha]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));



        $fieldset->addField('text_1', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Text Line 1'),
            'required' => false,
            'name' => 'popup[text_1]',
            'value' => Mage::getStoreConfig('exitoffer/popup/text_1')
        ));

        $fieldset->addField('text_2', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Text Line 2'),
            'required' => false,
            'name' => 'popup[text_2]',
            'value' => Mage::getStoreConfig('exitoffer/popup/text_2')
        ));

        $fieldset->addField('text_3', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Text Line 3'),
            'required' => false,
            'name' => 'popup[text_3]',
            'value' => Mage::getStoreConfig('exitoffer/popup/text_3')

        ));

        $fieldset->addField('text_4', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Text Line 4'),
            'required' => false,
            'name' => 'popup[text_4]',
            'value' => Mage::getStoreConfig('exitoffer/popup/text_4')
        ));

        $currentObj = Mage::registry('exitoffer_popup_data');
        if ($currentObj && $currentObj->getId()) {
            $form->setValues($currentObj->getData());
        }



        return parent::_prepareForm();
    }
}