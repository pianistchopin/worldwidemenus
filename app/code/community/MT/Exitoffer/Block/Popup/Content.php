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

class MT_Exitoffer_Block_Popup_Content extends Mage_Core_Block_Template
{
    public function getPopup()
    {
        return $this->getParentBlock()->getPopup();
    }

    public function getText($key)
    {
        return $this->getPopup()->getData('text_'.$key);
    }

    public function getColor($key)
    {
        return $this->getParentBlock()->getColor($key);
    }

}

