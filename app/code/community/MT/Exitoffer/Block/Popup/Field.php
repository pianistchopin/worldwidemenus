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

class MT_Exitoffer_Block_Popup_Field extends Mage_Core_Block_Template
{
    public function getJsonErrorMessage()
    {
        $data = $this->getData();
        $jsonErrorMessage = array();
        foreach ($data as $key => $value) {
            if (strpos($key, 'error_message_') === false) {
                continue;
            }

            $jsonErrorMessage[str_replace('error_message_', '', $key)] = $value;
        }

        return json_encode($jsonErrorMessage);
    }
}

