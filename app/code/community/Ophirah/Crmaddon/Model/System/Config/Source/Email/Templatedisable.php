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
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Adminhtml config system template source
 */
class Ophirah_Crmaddon_Model_System_Config_Source_Email_Templatedisable extends Varien_Object
{
    /**
     * Config xpath to email template node
     *
     */
    const XML_PATH_TEMPLATE_EMAIL = 'global/template/email/';
    const VALUE_DISABLED_EMAIL = 'disabled_template';

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        $defaultTemplate = $this->_setTemplate('Default Template from Locale');
        $responsiveTemplate = $this->_setTemplate('',"_responsive");
        $freeTemplate = $this->_setTemplate('',"_free");
        $responsiveFreeTemplate = $this->_setTemplate('',"_responsive_free");
        $customerTemplate = $this->_setTemplate('',"_customer");
        $adminTemplate = $this->_setTemplate('',"_admin");
        $customerTemplateResponsive = $this->_setTemplate('',"_customer_responsive");
        $adminTemplateResponsive = $this->_setTemplate('',"_admin_responsive");

        $options = $this->_iniOptions();
        $options = $this->_setOption($defaultTemplate['value'], $defaultTemplate['label'], $options);
        $options = $this->_setOption($customerTemplate['value'], $customerTemplate['label'], $options);
        $options = $this->_setOption($adminTemplate['value'], $adminTemplate['label'], $options);

        //check for support for responsive templates
        if($this->_checkVersion()) {
            $options = $this->_setOption($responsiveTemplate['value'], $responsiveTemplate['label'], $options);
            $options = $this->_setOption($customerTemplateResponsive['value'], $customerTemplateResponsive['label'], $options);
            $options = $this->_setOption($adminTemplateResponsive['value'], $adminTemplateResponsive['label'], $options);
        }

        //show free templates in case of a free user
        if(Mage::helper('qquoteadv/licensechecks')->showFreeUserOptions()) {
            $options = $this->_setOption($freeTemplate['value'], $freeTemplate['label'], $options);

            //check for support for responsive templates
            if($this->_checkVersion()) {
                $options = $this->_setOption($responsiveFreeTemplate['value'], $responsiveFreeTemplate['label'], $options);
            }
        }

        $options = $this->_setOption(self::VALUE_DISABLED_EMAIL,
            Mage::helper('core')->__('Disable Email Communications'), $options);

        return $options;
    }

    /**
     * Sets a template based on the email template in config.xml
     *
     * @param $addedText
     * @param null $addedTemplateFile
     * @return array
     */
    protected function _setTemplate($addedText, $addedTemplateFile = null){
        $templateName = Mage::helper('adminhtml')->__($addedText);
        $nodeName = str_replace('/', '_', $this->getPath()).$addedTemplateFile;
        $templateLabelNode = Mage::app()->getConfig()->getNode(self::XML_PATH_TEMPLATE_EMAIL. $nodeName . '/label');
        if ($templateLabelNode) {
            $templateName = Mage::helper('adminhtml')->__((string)$templateLabelNode);
            $templateName = Mage::helper('adminhtml')->__('%s (Default Template from Locale)', $templateName);
            //$templateName = Mage::helper('adminhtml')->__('%s '.$addedText, $templateName);
            //$templateName = Mage::helper('adminhtml')->__($addedText).' '.$templateName;
        }
        return array(
            'value' => $nodeName,
            'label' => $templateName
        );
    }

    /**
     * Initialize the options.
     * @return array
     */
    protected function _iniOptions(){
        if (!$collection = Mage::registry('config_system_email_template')) {
            $collection = Mage::getResourceModel('core/email_template_collection')
                ->load();

            Mage::register('config_system_email_template', $collection);
        }
        $options = $collection->toOptionArray();
        return $options;
    }

    /**
     * Adds an extra option to the $options var.
     * @param $value
     * @param $label
     * @param $options
     * @return array
     */
    protected function _setOption($value, $label, $options){
        if(isset($label) && !empty($label)){
            array_unshift(
                $options,
                array(
                    'value' => $value,
                    'label' => $label
                )
            );
        }
        return $options;
    }

    /**
     * Checks if the version is 1.9.1 or higher (since the release of responsive email templates)
     */
    protected function _checkVersion(){
        if(method_exists('Mage', 'getEdition')){
            $edition = Mage::getEdition();
            switch ($edition) {
                case "Community":
                    return version_compare(Mage::getVersion(), '1.9.1.0') < 0 ? false : true;
                case "Enterprise":
                    return version_compare(Mage::getVersion(), '1.14.1.0') < 0 ? false : true;
            }
        } else {
            //version is below v1.7.0.0
            return false;
        }

        return null;
    }

}
