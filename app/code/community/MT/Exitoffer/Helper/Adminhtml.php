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

class MT_Exitoffer_Helper_Adminhtml
    extends Mage_Core_Helper_Abstract
{

    public function getCurrentCampaign()
    {
        $campaign = Mage::registry('exitoffer_campaign_data');
        if (!$campaign) {
            return null;
        }

        return $campaign;
    }
}