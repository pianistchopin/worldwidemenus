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

class MT_Exitoffer_Model_Adminhtml_System_Config_Couponlist
{
    public function toOptionArray()
    {
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();
        $list = array(
            '' => Mage::helper('adminhtml')->__('Please choose rule')
        );
        if ($rules) {
            foreach ($rules as $rule) {
                if ($rule->getCouponType()==2)
                    $list[$rule->getId()] = $rule->getName();
            }
        }
        return $list;
    }
}