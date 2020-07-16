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

class MT_Exitoffer_Model_Adminhtml_System_Config_Backend_File
    extends Mage_Adminhtml_Model_System_Config_Backend_File
{
    protected function _getAllowedExtensions()
    {
        $mimeTypes = new MT_Exitoffer_Model_Adminhtml_System_Config_Mime();
        $mimeTypesList = $mimeTypes->toOptionArray();
        $ext = array();
        foreach ($mimeTypesList as $opt) {
            $ext[] = str_replace('.', '', $opt['label']);
        }

        return $ext;
    }
}
