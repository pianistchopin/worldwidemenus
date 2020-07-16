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
 * Class FME_Fmebase_Block_System_Config_Form_Fme_Contact
 *
 * @category FME
 * @package  FME_Fmebase
 * @author   Altaf Ahmed <support@fmeextension.com>
 * @license  http://opensource.org/licenses/osl-3.0.php  
 * Open Software License (OSL 3.0)
 * @link     https://www.fmeextensions.com
 */

class FME_Photogallery_Block_System_Config_Form_Fme_Contact extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    protected $_configFieldRenderer;
    

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $formElements=array(
            array('type' => 'text',
                'name' => 'name',
                'label' => $this->__('Full Name:'),
                'class' => 'required-entry'),
            array('type' => 'text',
                'name' => 'email',
                'label' => $this->__('Email Address:'),
                'class' => 'required-entry validate-email'),
            array('type' => 'text',
                'name' => 'telephone',
                'label' => $this->__('Telephone:'),
                'class' => ' validate-'),
            array('type' => 'text',
                'name' => 'subject',
                'label' => $this->__('Subject'),
                'class' => 'required-entry'),
            array('type' => 'select',
                'name' => 'extension',
                'label' => $this->__('Regarding Extension'),
                'values' => $this->_getExtensions(),
                'class' => 'required-entry'),
            array('type' => 'textarea',
                'name' => 'message',
                'label' => $this->__('Message'),
                'class' => 'required-entry'),
            array('type' => 'label',
                'name' => 'send',
                'after_element_html' => '<div class="right">
                <button type="button" class="scalable save" onclick="fmeSupport();">'.$this->__('Send').'</button>
                </div><div class="notice" id="ajax-response"></div>'),
            );
            
        foreach ($formElements as $field) {
            $html.= $this->_getFieldHtml($element, $field);
        }

        $html .= $this->_getFooterHtml($element);
        return $html;
    }
    
    protected function _getExtensions()
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);
        $extension[] = array('label'=>$this->__('Please select'), 'value'=>'');
        $extension[] = array('label'=>$this->__('Magento Related Support (paid)'), 
            'value'=>'Magento Support v' . Mage::getVersion());
        $extension[] = array('label'=>$this->__('Request Custom Development (paid)'), 
            'value'=>'Customization Request');
        foreach ($modules as $moduleName) {
            $name = explode('_', $moduleName, 2);
            if (!isset($name) || $name[0] != 'FME') {
                continue;
            }

            if (in_array(
                $moduleName, array(
                'FME_Fmebase'
                )
            )) {
                continue;
            }

            $moduleConfig = Mage::getConfig()->getNode('modules/' . $moduleName); 
            if($moduleConfig->extensionName):

                     $extensionName=$moduleConfig->extensionName; 
       
            else:

                $extensionName=str_replace('_', ' ', $moduleName);

            endif;
            $extension[] = array(
                'label'=>$this->__('%s Support ', $extensionName . ' v' . $moduleConfig->version), 
                'value'=>$extensionName.' v '.$moduleConfig->version);
        }

        return $extension;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_configFieldRenderer)) {
            $this->_configFieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }

        return $this->_configFieldRenderer;
    }
    protected function _getFooterHtml($element)
    {
        $ajaxUrl = $this->getUrl('adminhtml/fmebase/send');
        $html = parent::_getFooterHtml($element);
        $html .= Mage::helper('adminhtml/js')->getScript(
            "
            $$('td.form-buttons')[0].update('');
            $('{$element->getHtmlId()}' + '-head').setStyle('background: none;');
            $('{$element->getHtmlId()}' + '-head').writeAttribute('onclick', 'return false;');
            $('{$element->getHtmlId()}').show();
        "
        );
        $html = '<h4 id="fmecontact">'.$this->__('Contact FME Support Team ').'</h4>' . $html;
        $html .= Mage::helper('adminhtml/js')->getScript(
            "
            supportForm = new varienForm($('{$element->getHtmlId()}'));
            fmeSupport = function()
            {
                if (supportForm.validator.validate())
                {
                    var request = new Ajax.Request(
                        '{$ajaxUrl}',
                        {
                            method:'post',
                            onSuccess: successResponse,
                            parameters: Form.serialize($('{$element->getHtmlId()}'))
                        }
                    );
                }
            }
            successResponse = function(transport)
            {
             if (transport && transport.responseText)
                {
                    try{
                        response = eval('(' + transport.responseText + ')');
                    }
                    catch (e) 
                    {
                        response = {};
                    }
                    
                }
                if ((typeof response.message) == 'string') 
                {
                    $('ajax-response').update(response.message);
                } else {
                   $('ajax-response').update(response.message.join(\"\\n\"));
                }
                new PeriodicalExecuter(function(pe){ $('ajax-response').update(''); pe.stop(); }, 5);
            }"
        );

        return $html;
    }
    protected function _getFieldHtml($fieldset, $field)
    {
        $type = $field['type'];
        unset($field['type']);
        $field = $fieldset->addField($field['name'], $type, $field)->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}