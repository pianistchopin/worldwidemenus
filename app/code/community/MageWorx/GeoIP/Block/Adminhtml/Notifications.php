<?php
/**
 * MageWorx
 * GeoIP Extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_GeoIP_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    /**
     * Get Notification message
     *
     * @return string
     */
    public function getMessage()
    {
        return Mage::helper('mageworx_geoip')->getMissingDbWarning();
    }

    /**
     * ACL validation before html generation
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('system/config/mageworx_geoip/geoip')) {
            return parent::_toHtml();
        }
        return '';
    }
}