<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_Ip
{
    /**
     * Local IP address
     */
    const LOCAL_IP = '127.0.0.1';

    protected $addressPath = array(
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR'
    );

    /**
     * @return string
     */
    public function getCustomerIp()
    {
        foreach ($this->addressPath as $path) {
            $ip = Mage::app()->getRequest()->getServer($path);
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $addresses = explode(',', $ip);
                    foreach ($addresses as $address) {
                        if (trim($address) != self::LOCAL_IP) {
                            return trim($address);
                        }
                    }
                } else {
                    if ($ip != self::LOCAL_IP) {
                        return $ip;
                    }
                }
            }
        }

        return Mage::helper('core/http')->getRemoteAddr();
    }
}
