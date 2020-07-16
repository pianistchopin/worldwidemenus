<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tab_Form
 */
class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare layout
     *
     * @return mixed
     */
    protected function _prepareLayout()
    {
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return parent::_prepareLayout();
    }

    /**
     * Prepare form
     *
     * @return mixed
     */
    protected function _prepareForm()
    {
        $return = parent::_prepareForm();

        // Create Form
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('crmaddon_');

        // Assign helper
        $helper = $this->getLocalHelper();

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $helper->__('Load default Message'),
            'class' => 'fieldset-wide'
        ));
        // Load templates from DB
        $bodyTemplates = $helper->getTemplates();

        if ($this->getRequest()->getParam('crmbodytmpl')) {
            $crmtpl = Mage::app()->getRequest()->getParam('crmbodytmpl');
            if (array_key_exists($crmtpl, $bodyTemplates)) {
                $bodyTemplates['default'] = $bodyTemplates[$crmtpl];
            }
        }

        $options = $helper->createOptions($bodyTemplates);

        $fieldset->addField('message', 'select', array(
            'label' => $helper->__('Template'),
            'name' => 'crm_bodyId',
            'values' => $options
        ));

        // Adding custom field types
        $fieldset->addType('custom_loadfield', 'Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tab_Field_Loadtemplate');

        $fieldset->addField('custom_loadfield', 'custom_loadfield', array(
            'label' => '',
            'name' => 'loadCrmTemplate',
        ));

        // Adding new Fieldset
        $fieldset = $form->addFieldset('template_fieldset',
            array('legend' => $helper->__('Message Information'),
                'class' => 'fieldset-wide'
            ));

        // Adding custom field types
        $fieldset->addType('custom_buttonsfield', 'Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit_Tab_Field_Buttonstemplate');

        $request = $this->getRequest()->getParams();
        (isset($request['crmbodytmpl'])) ? $templateId = $request['crmbodytmpl'] : $templateId = 1;

        try {
            $loadedTemplate = Mage::getModel('crmaddon/crmaddontemplates')->getCrmbodyTemplate($templateId);
        } catch (Exception $e) {
            $errorMsg = $helper->__('Could not load CRM template');
            Mage::getSingleton('adminhtml/session')->addError($errorMsg);
            Mage::log('Exception: CRMAddon: ' .$e->getMessage(), null, 'c2q_exception.log', true);
        }

        if ($loadedTemplate < 1) {
            $loadedTemplate['default'] = 0;
        }

        // Setting checkbox
        if ($loadedTemplate['default'] == 1) {
            $checked = true;
            $checkedvalue = 0;
        } else {
            $checked = false;
            $checkedvalue = 1;;
        }

        $fieldset->addField('default', 'checkbox', array(
            'label' => '',
            'name' => 'crm_templatedefault',
            'onclick' => "",
            'onchange' => "",
            'checked' => $checked,
            'value' => $checkedvalue,
            'after_element_html' => '<small>Set as default template</small>'
        ));
        $value = (isset($loadedTemplate['name'])) ? $loadedTemplate['name'] : '';
        $fieldset->addField('name', 'text', array(
            'label' => $helper->__('Template name'),
            'name' => 'crm_templatename',
            'value' => $value
        ));

        $value = (isset($loadedTemplate['subject'])) ? $loadedTemplate['subject'] : '';
        $fieldset->addField('subject', 'text', array(
            'label' => $helper->__('Subject'),
            'name' => 'crm_templatesubject',
            'value' => $value
        ));

        $value = (isset($loadedTemplate['template_id'])) ? $loadedTemplate['template_id'] : '';
        $fieldset->addField('bodytemplate id', 'hidden', array(
            'name' => 'crm_bodytemplateid',
            'value' => $value
        ));

        $value = (isset($loadedTemplate['template'])) ? html_entity_decode($loadedTemplate['template']) : '';
        $fieldset->addField('templatebody', 'editor', array(
            'name' => 'crm_templatebody',
            'label' => $helper->__('Content'),
            'title' => $helper->__('Content'),
            'style' => 'height:16em;',
            'value' => $value,
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig()
        ));

        $fieldset->addField('custom_buttonsfield', 'custom_buttonsfield', array(
            'label' => '',
            'name' => 'buttonstemplate',
            'value' => 'none'
        ));

        $this->setForm($form);

        return $return;
    }

    /**
     * Get the crmaddon helper
     *
     * @return mixed
     */
    public function getLocalHelper()
    {
        return Mage::helper('crmaddon');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('qquoteadv')->__('Message Centre');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return 'Message Centre';
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
