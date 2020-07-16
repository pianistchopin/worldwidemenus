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

class MT_Exitoffer_Model_Adminhtml_Exitoffer_Popup_Form_Field_Type
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'input', 'label'=>Mage::helper('adminhtml')->__('Text Field')),
            array('value' => 'textarea', 'label'=>Mage::helper('adminhtml')->__('Textarea Field')),
            array('value' => 'select', 'label'=>Mage::helper('adminhtml')->__('Drop Down')),
            array('value' => 'checkbox', 'label'=>Mage::helper('adminhtml')->__('Checkbox')),
            array('value' => 'radio', 'label'=>Mage::helper('adminhtml')->__('Radio')),
            array('value' => 'hidden', 'label'=>Mage::helper('adminhtml')->__('Hidden')),
        );
    }
}
