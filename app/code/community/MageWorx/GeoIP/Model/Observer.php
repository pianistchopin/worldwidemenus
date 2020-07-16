<?php
/**
 * MageWorx
 * GeoIP Extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_GeoIP_Model_Observer
{
    /**
     * Updates database with cron
     *
     * @param mixed $schedule
     * @throws Exception
     */
    public function cronUpdateDatabase($schedule)
    {
        $helper = Mage::helper('mageworx_geoip');
        $geoip = Mage::getModel('mageworx_geoip/geoip');

        $geoip->downloadFile($helper->getDbUpdateSource(), $helper->getTempUpdateFile());

        if($helper->checkDatabaseFile()){
            copy($helper->getDatabasePath(), $helper->getDatabasePath() . '_backup_' . time());
        }

        $geoip->uncompressFile($helper->getTempUpdateFile(), $helper->getDatabasePath());

        unlink($helper->getTempUpdateFile());

        Mage::getModel('core/config')->saveConfig(MageWorx_GeoIP_Helper_Data::XML_GEOIP_UPDATE_DB, time());

        return true;
    }

    /**
     * Download GeoIp Database
     * 
     * @todo think about necessity of this code
     * @return MageWorx_GeoIP_Model_Observer
     */
    public function changeDbTypeAfter($observer)
    {
        if($observer->getObject()->getSection() != 'mageworx_geoip'){
            return $this;
        }
        $helper = Mage::helper('mageworx_geoip');

        if($helper->checkDatabaseFile()){
            return $this;
        }

        $geoip = Mage::getModel('mageworx_geoip/geoip');
        $errors = $geoip->downloadFile($helper->getDbUpdateSource(), $helper->getTempUpdateFile());
        if(!empty($errors)){
            $errors = implode(', ', $errors);
            throw new Exception($errors);
        }
        $success = Mage::getModel('mageworx_geoip/geoip')->uncompressFile($helper->getTempUpdateFile(), $helper->getDatabasePath());
        if(!$success){
            throw new Exception($helper->__('Cannot extract database'));
        }
        unlink($helper->getTempUpdateFile());

        return $this;
    }

    /**
     * Add warning to system config section
     *
     * @return MageWorx_GeoIP_Model_Observer
     */
    public function addSystemWarning($observer)
    {
        $message = Mage::helper('mageworx_geoip')->getMissingDbWarning();
        if (Mage::app()->getRequest()->getParam('section') == 'mageworx_geoip' && $message) {
            Mage::getSingleton('adminhtml/session')->addWarning($message);
        }
        return $this;
    }
}