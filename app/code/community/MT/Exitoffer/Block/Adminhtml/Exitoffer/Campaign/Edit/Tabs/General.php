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


class MT_Exitoffer_Block_Adminhtml_Exitoffer_Campaign_Edit_Tabs_General
    extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {
        $helper = Mage::helper('exitoffer');
        $data = array();
        $campaign = Mage::helper('exitoffer/adminhtml')->getCurrentCampaign();
        if ($campaign) {

            $data = $campaign->getData();
            $data['store_ids'] = $campaign->getStoreIds();
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('Campaign Information')));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Is Active'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'campaign[status]',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));



        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Campaign Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'campaign[name]',
        ));



        $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('start_date', 'date', array(
            'label' => Mage::helper('exitoffer')->__('Start At'),
            'name' => 'campaign[start_date]',
            'title'  => Mage::helper('exitoffer')->__('Start At'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => $dateFormatIso,
            'format'       => $dateFormatIso,
            'time' => true
        ));

        $fieldset->addField('end_date', 'date', array(
            'label' => Mage::helper('exitoffer')->__('End At'),
            'name' => 'campaign[end_date]',
            'title'  => Mage::helper('exitoffer')->__('End At'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => $dateFormatIso,
            'format'       => $dateFormatIso,
            'time' => true
        ));

        $fieldset->addField('store_ids', 'multiselect', array(
            'label' => Mage::helper('exitoffer')->__('Store'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'campaign[store_ids]',
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            'value' => array(0)
        ));


        $form->setValues($data);

        return parent::_prepareForm();
    }
}