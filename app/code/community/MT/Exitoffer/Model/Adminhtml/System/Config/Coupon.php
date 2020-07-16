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

class MT_Exitoffer_Model_Adminhtml_System_Config_Coupon
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'alphanum', 'label'=>Mage::helper('adminhtml')->__('Alphanumeric')),
            array('value' => 'alpha', 'label'=>Mage::helper('adminhtml')->__('Alphabetical')),
            array('value' => 'num', 'label'=>Mage::helper('adminhtml')->__('Numeric')),
        );
    }
}