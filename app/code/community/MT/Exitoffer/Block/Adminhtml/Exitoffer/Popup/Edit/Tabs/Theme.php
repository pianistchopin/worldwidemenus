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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Theme
    extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('Theme')));


        $fieldset->addField('color_1', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Primary Color'),
            'required' => false,
            'class' => 'color',
            'name' => 'popup[color_1]',
            'value' => Mage::getStoreConfig('exitoffer/popup/color_1')
        ));

        $fieldset->addField('color_2', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Secondary Color'),
            'required' => false,
            'class' => 'color',
            'name' => 'popup[color_2]',
            'value' => Mage::getStoreConfig('exitoffer/popup/color_2')
        ));

        $fieldset->addField('color_3', 'text', array(
            'label' => Mage::helper('exitoffer')->__('Additional Color'),
            'class' => 'color',
            'required' => false,
            'name' => 'popup[color_3]',
            'value' => Mage::getStoreConfig('exitoffer/popup/color_3')
        ));

        $currentObj = Mage::registry('exitoffer_popup_data');
        if ($currentObj && $currentObj->getId()) {
            $form->setValues($currentObj->getData());
        }

        return parent::_prepareForm();
    }
}