<?php
/**
 * This class should NOT be modified under any circumstances except major bug fixes
 * 
 * @copyright  Copyright (c) 2009 AITOC, Inc.
 */
class Aitoc_Common_Helper_Js_Abstract extends Mage_Core_Helper_Abstract
{
    const CACHE_PATH_PREFIX = 'aitoc_common_js_path_';
    const LIB_PATH = 'aitoc/common/';

    /**
     * Retrieve the latest library version
     * 
     * @return string
     */
    function getLatestFileName()
    {
        if (!Mage::app()->useCache('layout') || !$fileName = Mage::app()->loadCache(self::CACHE_PATH_PREFIX . $this->_cachePath)) {
            $items = glob(Mage::getBaseDir() . DS . 'js' . DS . self::LIB_PATH . $this->_libPath . '*.js');
            $items = array_filter(array_map(array($this, 'mapVersions'), $items));
            usort($items, 'version_compare');
            $fileName = self::LIB_PATH . $this->_libPath . array_pop($items) . '.js';
            if (Mage::app()->useCache('layout')) {
                Mage::app()->saveCache($fileName, self::CACHE_PATH_PREFIX . $this->_cachePath, array('layout'));
            }
        }
        return $fileName;
    }
    
    /**
     * Retrieve version of a lib from its file path
     * 
     * @param string $element
     * @return string
     */
    function mapVersions($element)
    {
        $matches = array();
        preg_match('/[\/\\\]+' . $this->_libPrefix . '(\d+[\w\.]+)\.js$/', $element, $matches);
        return array_pop($matches);
    }
}