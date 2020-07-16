<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Core
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 * @version     1.0.0
 */

/**
 * Class Ophirah_Core_Helper_Data
 */
class Ophirah_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    CONST QUICK_GUIDE_PATH = "Cart2Quote-Quick-guide.pdf";

    /**
     * Get the PHP version
     *
     * @return string
     */
    public function getPHPVersion()
    {
        $version = phpversion();
        return $version;
    }

    /**
     * If ionCube is loaded, get the version
     *
     * @return string
     */
    public function getIonCubeVersion()
    {
        if (extension_loaded('ionCube Loader')) {
            $ioncubeVersion = $this->ioncubeLoaderVersion();
            return $ioncubeVersion;
        } else {
            return false;
        }
    }

    /**
     * This function gets the ionCube version from the integer version sting
     * It also has a fallback for ionCube < v3.1
     *
     * @return bool|string
     */
    public function ioncubeLoaderVersion()
    {
        $ioncubeLoaderVersion = '';
        if (function_exists('ioncube_loader_iversion')) {
            $ioncubeLoaderIversion = ioncube_loader_iversion();
            $extra = 0;
            if ($ioncubeLoaderIversion >= 100000) {
                $extra = 1;
            }

            $ioncubeLoaderVersionMajor = (int)substr($ioncubeLoaderIversion, 0, 1 + $extra);
            $ioncubeLoaderVersionMinor = (int)substr($ioncubeLoaderIversion, 1 + $extra, 2);
            $ioncubeLoaderVersionRevision = (int)substr($ioncubeLoaderIversion, 3 + $extra, 2);
            $ioncubeLoaderVersion = sprintf(
                '%d.%d.%d',
                $ioncubeLoaderVersionMajor,
                $ioncubeLoaderVersionMinor,
                $ioncubeLoaderVersionRevision
            );
        } else {
            if (function_exists('ioncube_loader_version')) {
                $ioncubeLoaderVersion = ioncube_loader_version();
            }
        }

        return $ioncubeLoaderVersion;
    }

    /**
     * Function that checks if the compiler is enabled
     *
     * @return bool
     */
    public function checkCompiler()
    {
        if (defined('COMPILER_INCLUDE_PATH')) {
            return true;
        }

        return false;
    }

    /**
     * Function that checks if the cache is enabled
     *
     * @return bool
     */
    public function checkCache()
    {
        $caseEnabled = false;
        $usedCaches = Mage::app()->useCache();
        if (is_array($usedCaches) && array_search('1', $usedCaches)) {
            $caseEnabled = true;
        }

        return $caseEnabled;
    }

    /**
     * Function that checks if Cart2Quote can be updated to a newer version
     *
     * @return bool
     */
    public function isCart2QuoteUpdateable()
    {
        if (Mage::getConfig()->getNode('modules/Ophirah_Qquoteadv')) {
            $quotationVersion = Mage::getConfig()->getModuleConfig("Ophirah_Qquoteadv")->version;
            $coreVersion = Mage::getConfig()->getModuleConfig("Ophirah_Core")->version;

            if (version_compare($coreVersion, $quotationVersion) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Function that checks if Cart2Quote is installed
     *
     * @return bool
     */
    public function isCart2QuoteInstalled()
    {
        if (Mage::getConfig()->getNode('modules/Ophirah_Qquoteadv')) {
            return true;
        }

        return false;
    }

    /**
     * Get the Cart2Quote version
     *
     * @return mixed
     */
    public function getCart2QuoteVersion()
    {
        if (Mage::getConfig()->getNode('modules/Ophirah_Qquoteadv')) {
            $version = Mage::getConfig()->getModuleConfig("Ophirah_Qquoteadv")->version;
            return $version;
        }

        return false;
    }

    /**
     * Get the Cart2Quote Core (this module) version
     *
     * @return mixed
     */
    public function getCart2QuoteCoreVersion()
    {
        if (Mage::getConfig()->getNode('modules/Ophirah_Core')) {
            $version = Mage::getConfig()->getModuleConfig("Ophirah_Core")->version;
            return $version;
        }

        return false;
    }

    /**
     * Recursive copy funcion
     *
     * @param $source
     * @param $destination
     */
    public function recursiveCopy($source, $destination)
    {
        if (is_dir($source)) {
            if (!is_dir($destination)) {
                mkdir($destination);
            }

            $files = scandir($source);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $this->recursiveCopy($source . DS . $file, $destination . DS . $file);
                }
            }
        } else {
            if (file_exists($source)) {
                copy($source, $destination);
            }
        }
    }

    /**
     * Recursive remove directory function
     *
     * @param $directory
     */
    public function recursiveRemoveDirectory($directory)
    {
        if (is_dir($directory)) {
            $files = scandir($directory);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $this->recursiveRemoveDirectory($directory . DS . $file);
                }
            }

            rmdir($directory);
        } else {
            if (file_exists($directory)) {
                unlink($directory);
            }
        }
    }

    /**
     * Function that removes the quick start guide
     * That guide is added for users that extracts the achive instead of installing it,
     * so removing it is the best thing to do, oterwise users endup with a pdf file in the root.
     */
    public function removeQuickGuide()
    {
        $quickQuidePath = Mage::getBaseDir() . DS . $this::QUICK_GUIDE_PATH;

        //only remove when the file exists
        if (file_exists($quickQuidePath)) {
            unlink($quickQuidePath);
        }
    }
}
