<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */
class Amasty_GeoipRedirect_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LOCAL_IP = '127.0.0.1';

    /**
     * List of all directives, where we can find real ip address
     * @var array
     */
    private $addressPath = array(
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    );

    /**
     * Return real ip address
     * @return string
     */
    public function getIpAddress()
    {
        foreach ($this->addressPath as $path) {
            if (array_key_exists($path, $_SERVER) && !empty($_SERVER[$path])) {
                if (strpos($_SERVER[$path], ',') !== false) {
                    $addresses = explode(',', $_SERVER[$path]);
                    foreach ($addresses as $address) {
                        if (trim($address) != self::LOCAL_IP) {
                            return trim($address);
                        }
                    }
                } else {
                    if ($_SERVER[$path] != self::LOCAL_IP) {
                        return $_SERVER[$path];
                    }
                }
            }
        }

        return self::LOCAL_IP;
    }

    /**
     * @param string $string
     *
     * @return array|null
     */
    public function unserialize($string)
    {
        if (!@class_exists('Amasty_Base_Helper_String')) {
            $message = $this->getUnserializeError();
            Mage::logException(new Exception($message));
            Mage::helper('ambase/utils')->_exit($message);
        }

        return \Amasty_Base_Helper_String::unserialize($string);
    }

    /**
     * @return string
     */
    public function getUnserializeError()
    {
        return 'If there is the following text it means that Amasty_Base is not updated to the latest 
                             version.<p>In order to fix the error, please, download and install the latest version of 
                             the Amasty_Base, which is included in all our extensions.
                        <p>If some assistance is needed, please submit a support ticket with us at: '
            . '<a href="https://amasty.com/contacts/" target="_blank">https://amasty.com/contacts/</a>';
    }
}
