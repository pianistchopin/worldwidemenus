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
 */

/**
 * Class Ophirah_Core_Model_Version
 */
class Ophirah_Core_Model_Version extends Mage_Core_Model_Abstract
{
    const INSTALL_TYPE_OS = 'opensource';
    const INSTALL_TYPE_EC = 'encrypted';

    const ARCHIVE_PREFIX = 'c2q';
    const PACKAGE_FOLDER = '1.7_1.9';
    const VERSION_PREFIX = '_v';
    const ARCHIVE_SUFFIX_EC = '.zip';
    const ARCHIVE_SUFFIX_OC = '_OS.zip';
    const ARCHIVE_FOLDER = 'var/ophirah';
    /**
     * @var Ophirah_Core_Helper_Data
     */
    public $helper;
    /**
     * @var string
     */
    public $version = '';
    /**
     * @var bool|null
     */
    private $_isOpenSource = null;

    /**
     * Construct function
     */
    public function _construct()
    {
        $this->helper = Mage::helper('ophirah_core');
        $this->version = $this->helper->getCart2QuoteCoreVersion();
        parent::_construct();
    }

    /**
     * Getter for the install type
     *
     * @return string
     */
    public function getInstallType()
    {
        if ($this->isOpenSourceEdition()) {
            return $this::INSTALL_TYPE_OS;
        }

        return $this::INSTALL_TYPE_EC;
    }

    /**
     * Function that checks if the opensource package is included
     *
     * @return bool
     */
    private function isOpenSourceEdition()
    {
        if ($this->_isOpenSource == null) {
            $this->_isOpenSource = true;

            $file = Mage::getBaseDir() . DS . $this::ARCHIVE_FOLDER . DS . $this->getArchiveNameOpenSource();
            if (!file_exists($file) || !is_file($file)) {
                $this->_isOpenSource = false;
            }
        }

        return $this->_isOpenSource;
    }

    /**
     * Function that gets the opensoure archive name
     * Example: c2q_1.7_1.9_v5.4.0_OS.zip
     *
     * @return string
     */
    private function getArchiveNameOpenSource()
    {
        $archiveName = $this::ARCHIVE_PREFIX . $this::VERSION_PREFIX . $this->version;
        return $archiveName . $this::ARCHIVE_SUFFIX_OC;
    }

    /**
     * Getter for the archive name
     *
     * @return string
     */
    public function getArchiveName()
    {
        if ($this->isOpenSourceEdition()) {
            return $this->getArchiveNameOpenSource();
        }

        return $this->getArchiveNameEncrypted();
    }

    /**
     * Function that gets the encrypted archive name
     * Example: c2q_1.7_1.9_v5.4.0.zip
     *
     * @return string
     */
    private function getArchiveNameEncrypted()
    {
        $archiveName = $this::ARCHIVE_PREFIX . $this::VERSION_PREFIX . $this->version;
        return $archiveName . $this::ARCHIVE_SUFFIX_EC;
    }

    /**
     * Function that returns a filter to unzip the package
     *
     * @param $targetPath
     * @return Zend_Filter_Decompress
     */
    public function getArchiveFilter($targetPath)
    {
        return new Zend_Filter_Decompress(
            array(
                'adapter' => 'Zend_Filter_Compress_Zip',
                'options' => array(
                    'target' => $targetPath,
                )
            )
        );
    }
}
