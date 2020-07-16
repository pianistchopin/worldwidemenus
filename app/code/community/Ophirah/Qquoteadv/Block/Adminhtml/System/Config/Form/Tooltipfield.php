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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Form_Tooltipfield
 */
class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Form_Tooltipfield extends Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Formfield
{
    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $html = '<td class="label"><label for="' . $id . '">' . $element->getLabel() . '</label></td>';

        //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $isMultiple = $element->getExtType() === 'multiple';

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $options = $element->getValues();

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        } elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit() == 1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        if (!$this->versionCheck()) {
            $element->setTooltip(null);
        }

        if ($element->getTooltip()) {
            $html .= '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);

            $curSecure = Mage::app()->getStore()->isCurrentlySecure();
            if(!$curSecure){
                $curBasePath = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);
            } else {
                $curBasePath = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL) ;
            }

            //$adminBackendCode = Mage::getConfig()->getNode('admin/routers/adminhtml/args')->frontName;
            //$adminBackendCode = $adminBackendCode[0];
            $adminBackendCode = Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName')->asArray();

            $html .= '<div id="ttfu"  href="'. $curBasePath.$adminBackendCode.'" class="field-tooltip"><div>' .  $element->getTooltip() . '</div></div>';
        } else {
            $html .= '<td class="value">';
            $html .= $this->_getElementHtml($element);
        };

        if ($id === "qquoteadv_general_quotations_licence_text") {
            if ($this->getCart2QuoteLicense()) {
                $html .= '<tr><td>Current Cart2Quote Edition</td><td>' .
                    $this->getCart2QuoteEdition() . ' <a href="https://www.cart2quote.com/magento-quotation-module-plans-pricing.html"
                    target="_blank">(Compare Editions)</a></td>';
            } else {
                $html .= '<a target="_blank" href="https://www.cart2quote.com/magento-quotation-module-pricing.html">Buy a License</a> or
                          <a target="_blank" href="https://www.cart2quote.com/magento-quotation-module-plans-pricing.html">Compare Editions</a>.';
            }
        }

        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';

        if ($addInheritCheckbox) {

            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k => $v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif (isset($v['value']) && $v['value'] == $defText) {
                        $defTextArr[] = $v['label'];
                        break;
                    }
                }
                $defText = join(', ', $defTextArr);
            }

            // default value
            $html .= '<td class="use-default">';
            $html .= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html .= '<label for="' . $id . '_inherit" class="inherit" title="'
                . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html .= '</td>';
        }

        $html .= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html .= '</td>';

        $html .= '<td class="">';
        if ($element->getHint()) {
            $html .= '<div class="hint" >';
            $html .= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html .= '</div>';
        }
        $html .= '</td>';

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Decorate field row html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html)
    {
        if (!$this->versionCheck()) {
            return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
        } else {
            return parent::_decorateRowHtml($element, $html);
        }
    }

    /**
     * Get the Cart2Quote edition
     * This data is only available if Cart2Quote gets enabled in the global config page
     *
     * @return string
     */
    public function getCart2QuoteEdition(){
        $edition = Mage::getStoreConfig('qquoteadv_general/quotations/edition');

        if(!isset($edition) || empty($edition)){
            $edition = 'unknown';
        }

        return $edition;
    }

    /**
     * Get the Cart2Quote license
     *
     * @return mixed
     */
    public function getCart2QuoteLicense(){
        $license_key = Mage::getStoreConfig('qquoteadv_general/quotations/licence_key');
        return $license_key;
    }
}
