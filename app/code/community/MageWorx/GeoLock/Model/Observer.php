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

class MageWorx_GeoLock_Model_Observer
{
	public function geolock()
	{
		$helper      = Mage::helper('mageworx_geolock');
		if (!$helper->isEnabled()){
		    return $this;
		}

        $geoip = Mage::getSingleton('mageworx_geoip/geoip')->getCurrentLocation();

		$customerIp  = $geoip->getIp();
		$countries   = $helper->getCountries();
		$redirectUrl = $helper->getRedirectUrl();
		$isDenied  = false;

		$customerCountryCode = Mage::helper('mageworx_geoip')->prepareCode($geoip->getCode());

		if (count($countries)) {
			if ($helper->getRuleType() == MageWorx_GeoLock_Model_Ruletype::GEOLOCK_ALLOW) {
				if (!in_array($customerCountryCode, $countries)) {
					$isDenied = true;
				}
			} else {
				if (in_array($customerCountryCode, $countries)) {
					$isDenied = true;
				}
			}
		}

        $ipBlackList = $helper->getIpBlackList();
        if ($ipBlackList) {
            foreach ($ipBlackList as $ip) {
                $ip = str_replace(array('*', '.'), array('\d+', '\.'), $ip);
                if (preg_match("/^{$ip}$/", $customerIp)) {
                    $isDenied = true;
                    break;
                }
            }
        }

	   $ipWhiteList = $helper->getIpWhiteList();
        if ($ipWhiteList) {
            foreach ($ipWhiteList as $ip) {
                $ip = str_replace(array('*', '.'), array('\d+', '\.'), $ip);
                if (preg_match("/^{$ip}$/", $customerIp)) {
                    $isDenied = false;
                    break;
                }
            }
        }

		if ($isDenied === true) {
		    if ($redirectUrl){

                $currentUrl = Mage::helper('core/url')->getCurrentUrl();
		        if ($currentUrl != $redirectUrl){
                    Mage::app()->getResponse()->setRedirect($redirectUrl);
		        }

		    } else {
		        Mage::app()->getResponse()->setHttpResponseCode(403);
		        Mage::app()->getResponse()->clearBody();
                Mage::app()->getResponse()->sendHeaders();

		        echo <<<HTML
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Access forbidden!</title>
<style type="text/css"><!--/*--><![CDATA[/*><!--*/
    body { color: #000000; background-color: #FFFFFF; }
    a:link { color: #0000CC; }
    span {font-size: smaller;}
/*]]>*/--></style>
</head>

<body>
<h1>Access forbidden!</h1>
<p>
    You don't have permission to access the requested object.
    It is either read-protected or not readable by the server.
</p>
</body>
</html>
HTML;
		        exit;
		    }
		}
	}
}