<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * PHP version 5
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category  FME
 * @package   FME_Fmebase
 * @author    Altaf Ahmed <support@fmeextension.com>
 * @copyright 2016 XFME Extensions (https://www.fmeextensions.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link      https://www.fmeextensions.com
 */

/**
 * @category FME
 * @package  FME_Fmebase
 * @author   Altaf Ahmed <support@fmeextension.com>
 * @license  http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link     https://www.fmeextensions.com
 */
class FME_Photogallery_Block_System_Config_Form_Fme_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    protected $_configFieldRenderer;
    
    /**
     * Render element
     *
     * @access public
     * @param  object $element
     * @author Altaf Ahmed
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $allModules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($allModules);

        foreach ($allModules as $module) {

            if (strstr($module, 'FME_') === false) {
                            continue;
            }

            if (in_array($module, array('FME_Fmebase'))) {
                continue;
            }

            if ((string)Mage::getConfig()->getModuleConfig($module)->is_system == 'true') {
                continue;
            }
            
            $html.= $this->_getFieldHtml($element, $module);
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
     
    protected function getFieldRenderer()
    {
        if (empty($this->_configFieldRenderer)) {
            $this->_configFieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }

        return $this->_configFieldRenderer;
    }
    protected function _getFooterHtml($element)
    {
        $html = parent::_getFooterHtml($element);
        $html .= Mage::helper('adminhtml/js')->getScript(
            "
            $$('td.form-buttons')[0].update('');
            $('{$element->getHtmlId()}' + '-head').setStyle('background: none;');
            $('{$element->getHtmlId()}' + '-head').writeAttribute('onclick', 'return false;');
            $('{$element->getHtmlId()}').show();
        "
        );
        $html = '<h4>' . $this->__('Installed FME Extensions') . '</h4>' . $html;

        return $html;
    }

   

    protected function _getFieldHtml($fieldset, $module)
    {

        $moduleConfig = Mage::getConfig()->getNode('modules/' . $module);

        if($moduleConfig->extensionName):

                     $extensionName=$moduleConfig->extensionName;
       
        else:

            $extensionName=str_replace('_', ' ', $module);

        endif;
        $status= 'Output Enabled';
        if (Mage::getStoreConfig('advanced/modules_disable_output/' . $module)) {
            $status="Output Disabled";
        }

        $value= 'v-' . $moduleConfig->version .'  '.$status;
        
        $field = $fieldset->addField(
            $module, 'label',
            array(
                'name'          => $module,
                'label'         => $extensionName,
                'value'         => $value,
            )
        )->setRenderer($this->getFieldRenderer());

        return $field->toHtml();
    }
}
