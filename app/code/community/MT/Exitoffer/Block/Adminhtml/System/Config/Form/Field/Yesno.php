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

class MT_Exitoffer_Block_Adminhtml_System_Config_Form_Field_Yesno extends Mage_Core_Block_Html_Select
{
    private $_values;

    protected function _getValues($fieldId = null)
    {
        if (is_null($this->_values)) {
            $this->_values = array();
            $helper = Mage::helper('exitoffer');
            $this->_values['1'] = $helper->__('Yes');
            $this->_values['0'] = $helper->__('No');
        }

        if (!is_null($fieldId)) {
            return isset($this->_values[$fieldId]) ? $this->_values[$fieldId] : null;
        }

        return $this->_values;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getValues() as $fieldId => $groupLabel) {
                $this->addOption($fieldId, addslashes($groupLabel));
            }
        }
        return parent::_toHtml();
    }
}
