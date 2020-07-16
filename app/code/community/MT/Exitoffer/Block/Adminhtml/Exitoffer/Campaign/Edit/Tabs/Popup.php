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


class MT_Exitoffer_Block_Adminhtml_Exitoffer_Campaign_Edit_Tabs_Popup
    extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {
        $helper = Mage::helper('exitoffer');
        $data = array();
        $campaign = Mage::helper('exitoffer/adminhtml')->getCurrentCampaign();
        if ($campaign) {
            $data = Mage::registry('exitoffer_campaign_data')->getData();
            $data['pages'] = $campaign->getPages();
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('Popup Settings')));

        $fieldset->addField('popup_id', 'select', array(
            'name' => 'campaign[popup_id]',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('exitoffer')->__('Popup'),
            'values' => Mage::getModel('exitoffer/popup')->getCollectionAsOptionArray()
        ));

        $fieldset->addField('params', 'textarea', array(
            'label' => Mage::helper('exitoffer')->__('Show popup if match flowing params'),
            'name' => 'campaign[params]',
            'after_element_html' => '<br/><small>'.$helper->__('E.g. utm_campaign=magetrend&utm_medium=default. Use "&" for separation.').'<small>',
        ));

        $fieldset->addField('pages', 'multiselect', array(
            'label' => Mage::helper('exitoffer')->__('Show popup only in pages'),
            'name' => 'campaign[pages]',
            'values' => Mage::getModel('exitoffer/adminhtml_exitoffer_campaign_source_page')->toOptionArray(),
            'value' => array('all')
        ));

        $fieldset->addField('show_to', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Show to'),
            'name' => 'campaign[show_to]',
            'values' => array(
                0 => Mage::helper('exitoffer')->__('All'),
                1 => Mage::helper('exitoffer')->__('Not customers'),
            ),
            'value' => array('all')
        ));

        $fieldset->addField('cookie_lifetime', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Show popup again after X days'),
            'name' => 'campaign[cookie_lifetime]',
            'value' => 0,
            'after_element_html' => '<br/><small>'.$helper->__('Integer. Default: 0 - never show again').'<small>',
        ));

        $fieldset->addField('show_in_last_tab', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Show in Last Tab'),
            'name' => 'campaign[show_in_last_tab]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'after_element_html' => '<br/><small>'.$helper->__('E.g. If you have opened 3 tabs, popup will show only than you will try to close last tab.').'<small>',
        ));

        $fieldset->addField('layer_close', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Close after Click on Popup Layer'),
            'name' => 'campaign[layer_close]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));


        $fieldset->addField('show_on_mobile', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Show on Mobile'),
            'name' => 'campaign[show_on_mobile]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('mobile_trigger', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Trigger on Mobile'),
            'name' => 'campaign[mobile_trigger]',
            'values' => Mage::getModel('exitoffer/adminhtml_exitoffer_campaign_source_mobile')->toOptionArray(),
        ));

        $fieldset->addField('show_on_load', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Show on load'),
            'name' => 'campaign[show_on_load]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('show_on_load_delay', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Show on load after X seconds'),
            'name' => 'campaign[show_on_load_delay]',
            'value' => 0,
            'after_element_html' => '<br/><small>'.$helper->__('0 - no delay').'<small>',
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }
}