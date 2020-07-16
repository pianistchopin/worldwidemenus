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

class MT_Exitoffer_Model_Adminhtml_Exitoffer_Campaign_Source_Mobile
{

    public function toOptionArray()
    {
        $helper = Mage::helper('exitoffer/adminhtml');
        $optionArray = array(
            array('value' => 'top', 'label'=> $helper->__('Back to top')),
            array('value' => 'scroll', 'label'=> $helper->__('Rapid scroll up')),
            array('value' => 'both', 'label'=> $helper->__('Both')),
        );

        return $optionArray;
    }
}
