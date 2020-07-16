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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Coupon
    extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {
        $helper = Mage::helper('exitoffer');
        $data = array();
        if (Mage::registry('exitoffer_popup_data'))
            $data = Mage::registry('exitoffer_popup_data')->getData();

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('Discount Coupon')));

        $fieldset->addField('coupon_status', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Is Active'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'popup[coupon_status]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

         $fieldset->addField('coupon_rule_id', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Shopping Cart Price Rule'),
            'required' => false,
            'name' => 'popup[coupon_rule_id]',
            'values' => Mage::getModel('exitoffer/adminhtml_system_config_couponlist')->toOptionArray(),
            'after_element_html' => '<br/><small>'.$helper->__('Add new rule: "Admin -> Promotion -> Shopping Cart Price Rule" ').'<small>',

        ));

        $fieldset->addField('coupon_length', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Code Length'),
            'required' => false,
            'name' => 'popup[coupon_length]',
            'after_element_html' => '<br/><small>'.$helper->__('Excluding prefix, suffix and separators.').'<small>',
        ));

        $fieldset->addField('coupon_format', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Code Format'),
            'class' => 'required-entry',
            'required' => false,
            'name' => 'popup[coupon_format]',
            'values' => Mage::getModel('exitoffer/adminhtml_system_config_coupon')->toOptionArray(),
        ));

        $fieldset->addField('coupon_prefix', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Code Prefix'),
            'required' => false,
            'name' => 'popup[coupon_prefix]',
        ));

        $fieldset->addField('coupon_suffix', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Code Suffix'),
            'required' => false,
            'name' => 'popup[coupon_suffix]',
        ));

        $fieldset->addField('coupon_dash', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Add dash after N symbols'),
            'required' => false,
            'name' => 'popup[coupon_dash]',
            'after_element_html' => '<br/><small>'.$helper->__('Integer. If empty no separation.').'<small>',

        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }
}