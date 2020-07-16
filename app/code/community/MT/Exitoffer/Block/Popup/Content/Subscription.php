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

class MT_Exitoffer_Block_Popup_Content_Subscription
    extends MT_Exitoffer_Block_Popup_Content
{


    public function __construct()
    {
        $this->setTemplate('mt/exitoffer/popup/content/subscription.phtml');
    }

    public function getAdditionalFields()
    {
        return $this->getPopup()->getFieldCollection();
    }

    /**
     * Is only on visible checkbox
     * if yes, we can add it after email field and keep submit button in the same line as email field
     * @return bool
     */
    public function isOnlyOneVisibleCheckbox()
    {
        $additionalFields = $this->getAdditionalFields();
        if (empty($additionalFields) || $this->getPopup()->getUseCaptcha()) {
            return false;
        }

        $counter = 0;
        foreach ($additionalFields as $field) {
            if ($field['type'] == 'hidden') {
                continue;
            }

            if ($field['type'] == 'checkbox') {
                $counter++;
                continue;
            }

            return false;
        }

        return $counter == 1;
    }
}