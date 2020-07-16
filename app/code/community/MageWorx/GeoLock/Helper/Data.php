<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_GeoLock
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Geo Lock extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoLock
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_GeoLock_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_GEOLOCK_ENABLED  = 'mageworx_geoip/geolock/enabled';

	const XML_GEOLOCK_RULE_TYPE    = 'mageworx_geoip/geolock/rule_type';
	const XML_GEOLOCK_COUNTRIES    = 'mageworx_geoip/geolock/countries';
	const XML_GEOLOCK_REDIRECT_URL = 'mageworx_geoip/geolock/redirect_url';

	const XML_GEOLOCK_IP_BLACK_LIST = 'mageworx_geoip/geolock/ip_black_list';
	const XML_GEOLOCK_IP_WHITE_LIST = 'mageworx_geoip/geolock/ip_white_list';

    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_GEOLOCK_ENABLED);
    }

	public function getRuleType()
	{
		return Mage::getStoreConfig(self::XML_GEOLOCK_RULE_TYPE);
	}

	public function getCountries()
	{
		$countriesCode = Mage::getStoreConfig(self::XML_GEOLOCK_COUNTRIES);
		if ($countriesCode) {
			$countriesCode = Mage::helper('mageworx_geoip')->prepareCode($countriesCode);
			$countriesCode = explode(',', $countriesCode);
		}
		return $countriesCode;
	}

	public function getIpBlackList()
	{
		return array_filter((array) preg_split('/\r?\n/', Mage::getStoreConfig(self::XML_GEOLOCK_IP_BLACK_LIST)));
	}

    public function getIpWhiteList()
    {
        return array_filter((array) preg_split('/\r?\n/', Mage::getStoreConfig(self::XML_GEOLOCK_IP_WHITE_LIST)));
    }

	public function getRedirectUrl()
	{
		return $this->prepareRedirectUrl(Mage::getStoreConfig(self::XML_GEOLOCK_REDIRECT_URL));
	}

	public function prepareRedirectUrl($url)
	{
		$prefix = '/';
		if (!empty($url) && !preg_match("/^https?:\/\/.+/i", $url) && (substr($url, 0, 1) != $prefix)) {
			$url = $prefix.$url;
		}
		return $url;
	}
}