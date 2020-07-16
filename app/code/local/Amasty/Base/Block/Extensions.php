<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Block_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);

        foreach ($modules as $moduleName) {
            $moduleFullName = explode('_', $moduleName);

            if (!in_array($moduleFullName[0], array('Amasty', 'Belitsoft', 'Mageplace', 'Magpleasure'))) {
                continue;
            }

            if (in_array($moduleName, array(
                'Amasty_Base', 'Magpleasure_Common', 'Magpleasure_Searchcore'
            ))) {
                continue;
            }

            if ((string)Mage::getConfig()->getModuleConfig($moduleName)->is_system == 'true') {
                continue;
            }

            $html .= $this->_getFieldHtml($element, $moduleName);
        }

        $html .= '<span class="amasty-info-block"></span>';//added for showing amasty logo in the top
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }

    protected function _getFieldHtml($fieldset, $moduleCode)
    {
        $currentVer = Mage::getConfig()->getModuleConfig($moduleCode)->version;
        if (!$currentVer) {
            return '';
        }

         // in case we have no data in the RSS
        $moduleName = (string)Mage::getConfig()->getNode('modules/' . $moduleCode . '/name');
        if ($moduleName) {
            $name = $moduleName;
            $url = (string)Mage::getConfig()->getNode('modules/' . $moduleCode . '/url');
            $moduleName = '<a href="' . $url . '" target="_blank" title="' . $name . '">' . $name . "</a>";
        } else {
            $moduleName = substr($moduleCode, strpos($moduleCode, '_') + 1);
        }

        $baseKey = (string)Mage::getConfig()->getNode('modules/' . $moduleCode . '/baseKey');
        $allExtensions = Amasty_Base_Helper_Module::getAllExtensions();

        $status = '<a class="ambase-icon" target="_blank"><img src="' . $this->getSkinUrl('images/ambase/ok.gif') . '" title="'
            . $this->__("Installed") . '"/></a>';

        $isLatest = '_is_latest';
        if ($allExtensions && isset($allExtensions[$moduleCode])) {
            if (is_array($allExtensions[$moduleCode])
                && !array_key_exists('name', $allExtensions[$moduleCode])
            ) {
                if (!empty($baseKey) && isset($allExtensions[$moduleCode][$baseKey])) {
                    $ext = $allExtensions[$moduleCode][$baseKey];
                } else {
                    $ext = end($allExtensions[$moduleCode]);
                }
            } else {
                $ext = $allExtensions[$moduleCode];
            }
            
            $url     = $ext['url'];
            $name    = $ext['name'];
            $lastVer = $ext['version'];

            $moduleName = '<a class="ambase-module-name" href="' . $url . '" target="_blank" title="' . $name . '">'
                . $name . "</a>";
            if (version_compare($currentVer, $lastVer, '<')) {
                $isLatest = '';
                $status = sprintf(
                    '<a class="ambase-icon" href="%s" target="_blank"><img src="%s" alt="%s" title="%s"/></a>',
                    $url . '#changelog',
                    $this->getSkinUrl('images/ambase/update.gif'),
                    $this->__("Update available"),
                    $this->__("Update available")
                );
            }
        }

        // in case if module output disabled
        if (Mage::getStoreConfig('advanced/modules_disable_output/' . $moduleCode)) {
            $status = sprintf(
                '<a class="ambase-icon" target="_blank"><img src="%s" alt="%s" title="%s"/></a>',
                $this->getSkinUrl('images/ambase/bad.gif'),
                $this->__("Output disabled"),
                $this->__("Output disabled")
            );
        }

        $moduleName = $status . ' ' . $moduleName;
        $field = $fieldset->addField(
            $moduleCode . $isLatest,
            'label',
            array(
                'name'  => 'dummy',
                'label' => $moduleName,
                'value' => $currentVer
            )
        )->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}
