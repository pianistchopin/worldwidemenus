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

class MT_Exitoffer_Block_Popup_Theme_Default extends Mage_Core_Block_Template
{

    public function __construct()
    {
        $this->setTemplate('mt/exitoffer/popup/theme/default.phtml');
    }

    public function getContentType()
    {
        return $this->getParentBlock()->getContentType();
    }

    public function getPopup()
    {
        return $this->getParentBlock()->getPopup();
    }

    public function getColor($key)
    {
        return '#'.str_replace('#', '', $this->getPopup()->getData('color_'.$key));
    }
}