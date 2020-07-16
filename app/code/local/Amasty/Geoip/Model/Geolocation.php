<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */
class Amasty_Geoip_Model_Geolocation extends Varien_Object
{
    public function locate($ip)
    {
        if (!empty($ip)) {
            $ip = str_replace(' ', '', $ip);
            $ip = explode(',', $ip);
            $ip = $ip[0];

            //$ip = '213.184.225.37';//Minsk
            $ip = substr($ip, 0, strrpos($ip, ".")) . '.0'; // Mask IP according to EU GDPR law

            /* @var Amasty_Geoip_Model_Import $geoIpModel */
            $geoIpModel = Mage::getModel('amgeoip/import');
            if ($geoIpModel->isDone()) {
                $db = Mage::getSingleton('core/resource')->getConnection('core_read');
                $longIP = sprintf("%u", ip2long($ip));
                $blockSelect = $db->select()
                    ->from(Mage::getSingleton('core/resource')->getTableName('amgeoip/block'))
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array('geoip_loc_id', 'latitude', 'longitude'))
                    ->where('start_ip_num <= ?', $longIP)
                    ->order('start_ip_num DESC')
                    ->limit(1);

                $select = $db->select()
                    ->from(array('b' => $blockSelect))
                    ->joinInner(
                        array('l' => Mage::getSingleton('core/resource')->getTableName('amgeoip/location')),
                        'l.geoip_loc_id = b.geoip_loc_id',
                        null
                    )
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array('l.*', 'b.latitude', 'b.longitude'));

                if ($res = $db->fetchRow($select))
                    $this->setData($res);
            }
        }

        return $this;
    }
}
