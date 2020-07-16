<?php
/**
 * MageWorx
 * GeoIP Extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_GeoIP_Helper_Http extends Mage_Core_Helper_Http
{
	const ICONV_CHARSET = 'UTF-8';

    /**
     * checks string
     *
     * @param string $string
     * @return bool
     */
    public function cleanString($string)
    {
        return '"libiconv"' == ICONV_IMPL ? iconv(self::ICONV_CHARSET, self::ICONV_CHARSET . '//IGNORE', $string) : $string;
    }

    /**
     * Returns clean http value
     *
     * @param string $var
     * @param bool $clean
     * @return bool
     */
    protected function _getHttpCleanValue($var, $clean = true)
    {
        $value = $this->_getRequest()->getServer($var, '');
        if ($clean) {
            $value = $this->cleanString($value);
        }

        return $value;
    }

    /**
     * Gets http user agent
     *
     * @param bool $clean
     * @return string
     */
    public function getHttpUserAgent($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_USER_AGENT', $clean);
    }
}
