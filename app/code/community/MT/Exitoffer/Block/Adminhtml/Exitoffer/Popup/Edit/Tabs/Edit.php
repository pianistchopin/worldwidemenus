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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Edit extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {
        $helper = Mage::helper('exitoffer');
        $data = array();
        if (Mage::registry('exitoffer_popup_data')) {
            $data = Mage::registry('exitoffer_popup_data')->getData();
        }
        if (Mage::app()->getRequest()->getParam('content_type')!= '') {
            $data['content_type'] = Mage::app()->getRequest()->getParam('content_type');
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('Popup Information')));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Is Active'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'popup[status]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Popup Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'popup[name]',
        ));

        $fieldset->addField('content_type', 'hidden', array(
            'name' => 'popup[content_type]',
            'values' => Mage::getModel('exitoffer/adminhtml_system_config_content')->toOptionArray()
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }
}