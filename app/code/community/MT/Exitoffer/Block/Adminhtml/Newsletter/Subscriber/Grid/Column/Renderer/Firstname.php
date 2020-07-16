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

class MT_Exitoffer_Block_Adminhtml_Newsletter_Subscriber_Grid_Column_Renderer_Firstname
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $rowValue = $row->getCustomerFirstname();
        if (empty($rowValue)) {
            $rowValue = $row->getSubscriberFirstname();
        }
        if (empty($rowValue)) {
            $rowValue = '---';
        }
        return $rowValue;
    }
}