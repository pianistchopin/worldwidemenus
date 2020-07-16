<?php
/**
 * MageWorx
 * GeoIP Extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_GeoIP_Model_Source_Dbtype
{
    const GEOIP_COUNTRY_DATABASE = 1;
    const GEOIP_CITY_DATABASE    = 2;

    /**
     * Return options source array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::GEOIP_COUNTRY_DATABASE,
                'label' => Mage::helper('mageworx_geoip')->__('GeoIP Country')
            ),
            array(
                'value' => self::GEOIP_CITY_DATABASE,
                'label' => Mage::helper('mageworx_geoip')->__('GeoIP City')
            ),
        );
    }

    /**
     * Return options array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::GEOIP_COUNTRY_DATABASE    => Mage::helper('mageworx_geoip')->__('GeoIP Country'),
            self::GEOIP_CITY_DATABASE       => Mage::helper('mageworx_geoip')->__('GeoIP City')
        );
    }
}