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

class MT_Exitoffer_Model_Adminhtml_System_Config_Mime
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'application/pdf', 'label'=> '.pdf'),
            array('value' => 'application/zip', 'label'=> '.zip'),
            array('value' => 'image/jpeg', 'label'=> '.jpg'),
            array('value' => 'image/png', 'label'=> '.png'),
            array('value' => 'image/gif', 'label'=> '.gif'),
        );
    }
}