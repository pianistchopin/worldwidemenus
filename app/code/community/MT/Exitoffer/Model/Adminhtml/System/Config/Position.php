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

class MT_Exitoffer_Model_Adminhtml_System_Config_Position
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'leftcenter', 'label'=>Mage::helper('adminhtml')->__('Left Center')),
            array('value' => 'rightcenter', 'label'=>Mage::helper('adminhtml')->__('Right Center')),
            array('value' => 'leftbottom', 'label'=>Mage::helper('adminhtml')->__('Left Bottom')),
            array('value' => 'rightbottom', 'label'=>Mage::helper('adminhtml')->__('Right Bottom')),
        );
    }
}