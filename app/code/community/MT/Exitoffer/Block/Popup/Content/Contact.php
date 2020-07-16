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

class MT_Exitoffer_Block_Popup_Content_Contact extends MT_Exitoffer_Block_Popup_Content
{

    public function __construct()
    {
        $this->setTemplate('mt/exitoffer/popup/content/contact.phtml');
    }

    public function getAdditionalFields()
    {
        return $this->getPopup()->getFieldCollection();
    }
}