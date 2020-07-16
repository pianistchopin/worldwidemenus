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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Settings
    extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => "ExitOfferPopup.setSettings('".$this->getContinueUrl()."', 'content_type')",
                    'class'     => 'save'
                ))
        );


        return $this;
    }

    protected function _prepareForm()
    {
        $helper = Mage::helper('exitoffer');
        $data = array();
        if (Mage::registry('exitoffer_popup_data'))
            $data = Mage::registry('exitoffer_popup_data')->getData();

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('general', array('legend' => Mage::helper('exitoffer')->__('Settings')));

        $fieldset->addField('content_type', 'select', array(
            'label' => Mage::helper('exitoffer')->__('Content Type'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'popup[content_type]',
            'values' => Mage::getModel('exitoffer/adminhtml_system_config_content')->toOptionArray()
        ));

        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }

    public function getContinueUrl()
    {
        return $this->getUrl('*/*/new', array(
            '_current'  => true,
            'content_type' => '{{content_type}}'
        ));
    }


}