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
 * Class Ophirah_Core_Adminhtml_OphirahcoreController
 */
class Ophirah_Core_Adminhtml_OphirahcoreController extends Mage_Adminhtml_Controller_Action
{
    const IONCUBE_FOLDER = 'ioncube_files';
    const IONCUBE_461_PHP_53 = 'ic_loader_4.6.1_php_5.3_5.4';
    const IONCUBE_461_PHP_55 = 'ic_loader_4.6.1_php_5.5';
    const IONCUBE_500_PHP_56 = 'ic_loader_5.0.0_php_5.6';
    const IONCUBE_1000_PHP_70 = 'ic_loader_10.0.0_php_7.0';
    const IONCUBE_1000_PHP_71 = 'ic_loader_10.0.0_php_7.1';
    const IONCUBE_1020_PHP_72 = 'ic_loader_10.2.0_php_7.2';
    const IONCUBE_OLD = 'ic_loader_older';

    /**
     * @var Ophirah_Core_Helper_Data
     */
    public $helper;

    /**
     * @var Ophirah_Core_Model_Version
     */
    public $versionModel;

    /**
     * Install function that installs Cart2Quote for the given environment
     */
    public function installAction()
    {
        $basedir = Mage::getBaseDir();
        $archivePath = Ophirah_Core_Model_Version::ARCHIVE_FOLDER . DS . $this->versionModel->getArchiveName();
        $fullArchivePath = $basedir . DS . $archivePath;
        $unzipPath = $basedir . DS . Ophirah_Core_Model_Version::ARCHIVE_FOLDER . DS . "unzip";
        $ioncubeVersion = $this->helper->getIonCubeVersion();
        $phpVersion = $this->helper->getPHPVersion();

        //make sure the unzip path exists
        if (!is_dir($unzipPath)) {
            mkdir($unzipPath);
        }

        //define the filter to unpack a zip file
        $filter = $this->versionModel->getArchiveFilter($unzipPath);

        //make sure that that ioncube is installed if this is not an opensource package
        if (($this->versionModel->getInstallType() == Ophirah_Core_Model_Version::INSTALL_TYPE_EC)
            && ($ioncubeVersion == false)
        ) {
            //no ioncube installed, so encrypted version can't be installed.
            Mage::log('Install (unpack): fail', null, 'c2q_install.log', true);
            $warning = $this->__('IonCube is not installed, installation aborted.');
            Mage::getSingleton('adminhtml/session')->addWarning($warning);
            $this->_redirectReferer();
        }

        //unpack the package
        try {
            $compressed = $filter->filter($fullArchivePath);
            if ($compressed) {
                Mage::log('Install (unpack): success', null, 'c2q_install.log', true);
            } else {
                Mage::log('Install (unpack): fail', null, 'c2q_install.log', true);
                $warning = $this->__('Unpack failed, installation aborted.');
                Mage::getSingleton('adminhtml/session')->addWarning($warning);
                $this->_redirectReferer();
            }
        } catch (Exception $e) {
            //report error
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_install.log', true);
            $warning = $this->__('Unpack error, installation aborted. Error message is: '.$e->getMessage());
            Mage::getSingleton('adminhtml/session')->addWarning($warning);
            $this->_redirectReferer();
        }

        //move the files to the final target
        try {
            //maintenace mode enable
            Mage::helper('backup')->turnOnMaintenanceMode();

            //move the files to the final target
            $this->helper->recursiveCopy(
                $unzipPath . DS . Ophirah_Core_Model_Version::PACKAGE_FOLDER,
                $basedir
            );

            //special actions for an encrypted release
            if ($this->versionModel->getInstallType() == Ophirah_Core_Model_Version::INSTALL_TYPE_EC) {

                //define the correct ioncube files folder
                if ((version_compare($phpVersion, '7.2.0') >= 0)
                    && (version_compare($ioncubeVersion, '10.2.0') >= 0)
                ) {
                    //PHP 7.2+ and ionCube 10.2.0+
                    $ioncubeFolderForEnvironment = $this::IONCUBE_1020_PHP_72;

                } elseif ((version_compare($phpVersion, '7.1.0') >= 0)
                    && (version_compare($ioncubeVersion, '10.0.0') >= 0)
                ) {
                    //PHP 7.1+ and ionCube 10.0.0+
                    $ioncubeFolderForEnvironment = $this::IONCUBE_1000_PHP_71;

                } elseif ((version_compare($phpVersion, '5.6.0') >= 0)
                    && (version_compare($ioncubeVersion, '10.0.0') >= 0)
                ) {
                    //PHP 5.6+ and ionCube 10.0.0+
                    $ioncubeFolderForEnvironment = $this::IONCUBE_1000_PHP_70;

                } elseif ((version_compare($phpVersion, '5.6.0') >= 0)
                    && (version_compare($ioncubeVersion, '5.0.0') >= 0)
                ) {
                    //PHP 5.6+ and ionCube 5+
                    $ioncubeFolderForEnvironment = $this::IONCUBE_500_PHP_56;

                } elseif ((version_compare($phpVersion, '5.5.0') >= 0)
                    && (version_compare($ioncubeVersion, '4.6.1') >= 0)
                ) {
                    //PHP 5.5+ and ionCube 4.6+
                    $ioncubeFolderForEnvironment = $this::IONCUBE_461_PHP_55;

                } elseif ((version_compare($phpVersion, '5.3.0') >= 0)
                    && (version_compare($ioncubeVersion, '4.6.1') >= 0)
                ) {
                    //PHP 5.3+ and ionCube 4.6+
                    $ioncubeFolderForEnvironment = $this::IONCUBE_461_PHP_53;
                } else {
                    //PHP 5.2+ and ionCube
                    $ioncubeFolderForEnvironment = $this::IONCUBE_OLD;
                }

                //copy the required ioncube files
                $this->helper->recursiveCopy(
                    $unzipPath . DS . $this::IONCUBE_FOLDER . DS . $ioncubeFolderForEnvironment,
                    $basedir
                );
            }

            //maintenace mode disable
            Mage::helper('backup')->turnOffMaintenanceMode();
        } catch (Exception $e) {
            //report error
            $error = $this->__('Installation aborted.');
            Mage::getSingleton('adminhtml/session')->addError($error);
            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_install.log', true);

            //maintenace mode disable
            Mage::helper('backup')->turnOffMaintenanceMode();

            //redirect
            $this->_redirectReferer();
        }

        //remove the unzip folder
        $this->helper->recursiveRemoveDirectory($unzipPath);

        //remove the quick guide
        $this->helper->removeQuickGuide();

        //show success message
        $success = $this->__('The required files are succesfuly loaded on the system.');
        Mage::getSingleton('adminhtml/session')->addSuccess($success);

        //note the user to logout and login again
        $logoutUrl = $this->getUrl('adminhtml/index/logout');
        $notice = $this->__("Please <a href='%s'>logout</a> and login again.", $logoutUrl);
        Mage::getSingleton('adminhtml/session')->addNotice($notice);

        //trow after event
        Mage::dispatchEvent('ophirah_core_install_after');

        //redirect
        $this->_redirectReferer();
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->helper = Mage::helper('ophirah_core');
        $this->versionModel = Mage::getModel('ophirah_core/version');
        parent::_construct();
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
