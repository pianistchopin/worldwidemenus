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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Fields_Form
    extends Mage_Adminhtml_Block_Widget_Form
{

    protected $_htmlIdPrefix = 'additional_field_fieldset';

    protected function _prepareForm()
    {
        if (!Mage::registry('exitoffer_popup_data'))
            return;

        $gridBlock = $this->getLayout()->createBlock('exitoffer/adminhtml_exitoffer_popup_edit_tabs_fields_grid');
        $gridBlockJsObject = '';
        if ($gridBlock) {
            $gridBlockJsObject = $gridBlock->getJsObjectName();
        }

        $helper = Mage::helper('exitoffer');
        $popup = Mage::registry('exitoffer_popup_data');
        $this->setData(
            array('popup_id' => $popup->getId())
        );

        $form = new Varien_Data_Form();
        $form->setId('additional_field_fieldset');
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            $this->_htmlIdPrefix,
            array(
                'legend' => $helper->__('Add New Additional Field ')
            )
        );
        $fieldset->addClass('ignore-validate');

        $fieldset->addField(
            'popup_id',
            'hidden',
            array(
                'name'     => 'field[popup_id]',
                'value' => $popup->getId()
            )
        );

        $fieldset->addField(
            'entity_id',
            'hidden',
            array(
                'name' => 'field[entity_id]',
                'value' => ''
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'name'     => 'field[name]',
                'label'    => $helper->__('Name'),
                'required' => true,
                'class'    => 'required-entry',
                'after_element_html' => '<br/><small>'.$helper->__('without spaces, lowercase, for word separation use "_" ').'<small>',
            )
        );

        $fieldset->addField(
            'type',
            'select',
            array(
                'label' => Mage::helper('exitoffer')->__('Field Frontend Type'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'field[type]',
                'values' => Mage::getModel('exitoffer/adminhtml_exitoffer_popup_form_field_type')->toOptionArray()
            )
        );


        $fieldset->addField('admin_title', 'text', array(
            'name'     => 'field[admin_title]',
            'label'    => $helper->__('Admin Label'),
            'required' => true,
            'class'    => 'required-entry'
        ));

        $fieldset->addField('title', 'text', array(
            'name'     => 'field[title]',
            'label'    => $helper->__('Frontend Label'),
            'required' => true,
            'class'    => 'required-entry'
        ));

        $fieldset->addField('default_value', 'text', array(
            'name'     => 'field[default_value]',
            'label'    => $helper->__('Default Value'),
            'required' => false,
        ));

        $fieldset->addField('options', 'textarea', array(
            'name'     => 'field[options]',
            'label'    => $helper->__('Options'),
            'required' => false,
            'after_element_html' => '<br/><small>'.$helper->__('Required only for drop down input type. E.g. Male|Female. Use "|" for separation.').'<small>',
        ));

        $fieldset->addField('is_required', 'select', array(
            'label'    => $helper->__('Is Required'),
            'name'     => 'format',
            'values'  => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'required' => false,
        ));

        $fieldset->addField('error_message_is_required', 'text', array(
            'name'     => 'field[error_message_is_required]',
            'label'    => $helper->__('Custom Error message: field is required'),
            'required' => false,
        ));

        $fieldset->addField('position', 'text', array(
            'name'     => 'field[position]',
            'label'    => $helper->__('Position'),
            'required' => false,
            'after_element_html' => '<br/><small>'.$helper->__('Position less than 0, means in the end of form.').'<small>',
        ));

        $saveFieldUrl = $this->getSaveFieldUrl($popup->getId());
        $fieldset->addField('field_add_button', 'note', array(
            'text' => $this->getButtonHtml(
                $helper->__('Add'),
                "ExitOfferPopup.addField('{$this->_htmlIdPrefix}', '{$saveFieldUrl}', '{$gridBlockJsObject}')",
                'add'
            )
        ));

        $fieldset->addField('field_edit_button', 'note', array(
            'text' => $this->getButtonHtml(
                $helper->__('Save'),
                "ExitOfferPopup.addField('{$this->_htmlIdPrefix}', '{$saveFieldUrl}', '{$gridBlockJsObject}')",
                'save'
            ).$this->getButtonHtml(
                    $helper->__('Add New'),
                    "ExitOfferPopup.resetFieldForm();",
                    'add'
                )
        ));

        return parent::_prepareForm();
    }

    public function getSaveFieldUrl($popupId)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/exitoffer_popup/saveFieldAjax/', array(
            'popup_id' => $popupId
        ));
    }


}