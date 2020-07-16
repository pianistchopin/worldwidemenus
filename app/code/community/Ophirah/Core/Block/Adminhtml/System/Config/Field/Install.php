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
 * Class Ophirah_Core_Block_Adminhtml_System_Config_Field_Install
 */
class Ophirah_Core_Block_Adminhtml_System_Config_Field_Install extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    const URL_LOADER = "https://www.ioncube.com/loaders.php";

    /**
     * @var Ophirah_Core_Helper_Data
     */
    public $helper;

    /**
     * @var Ophirah_Core_Model_Version
     */
    public $versionModel;

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
     * Default render call for the Magento config page.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        //We could check for the Magento verison and the PHP version here
        // but that part should be handled in the marketplace/connect checks

        $ionCubeCheck = $this->checkIonCube();
        if ($ionCubeCheck != false) {
            return $ionCubeCheck;
        }

        $quotationInstalled = $this->helper->isCart2QuoteInstalled();
        if ($quotationInstalled) {
            if (!$this->helper->isCart2QuoteUpdateable()) {
                $version = $this->helper->getCart2QuoteVersion();
                return $this->__('Cart2Quote %s is installed.', $version);
            }
        }

        if (!extension_loaded('zip')) {
            return $this->__('The PHP zip extension is not loaded, pleas enable the PHP zip extension');
        }

        //check if the cache is enabled
        if ($this->helper->checkCache()) {
            return $this->__('Cache is enabled, please disable the cache in "System>Cache Management" before updating or installing Cart2Quote');
        }

        //check if the compiler is enabed
        if ($this->helper->checkCompiler()) {
            return $this->__('The compiler is enabled, please disable the compiler in "System>Tools>Compilation" before updating or installing Cart2Quote');
        }

        //chose to show an update button or an install button
        if ($quotationInstalled) {
            if ($this->helper->isCart2QuoteUpdateable()) {
                return $this->renderUpdateButton();
            }
        } else {
            return $this->renderInstallButton();
        }

        return '';
    }

    /**
     * Check if ioncube is required and if it is installed
     */
    public function checkIonCube()
    {
        if ($this->versionModel->getInstallType() == Ophirah_Core_Model_Version::INSTALL_TYPE_EC) {
            $ioncubeVersion = $this->helper->getIonCubeVersion();
            if ($ioncubeVersion == false) {
                return $this->renderNoIc();
            } else {
                $phpVersion = $this->helper->getPHPVersion();
                if ((version_compare($phpVersion, '7.0.0') >= 0) && (version_compare($ioncubeVersion, '6.0.2') < 0)) {
                    return $this->__('The current version of ionCube for PHP7 is not viable for a production store, please install ionCube loader 6.0.2 or newer. Alternatively you could use the Open Source version of Cart2Quote');
                }
            }
        }

        return false;
    }

    /**
     * This function is called by the code that is added to encrypted files.
     * Only when ionCube is not installed. To prevent a white page, we show this info.
     *
     * @return string
     */
    public function renderNoIc()
    {
        $text = $this->__("<strong>IonCube is not installed:</strong> Cart2Quote requires ionCube to be installed. For more information, contact your server admin, your web host or <a href='%s'>look here</a>.", self::URL_LOADER);
        $html = '<div style="text-align: left; padding: 20px;';
        $html .= ' font-family: Arial, Helvetica, sans-serif; background-color: #EEEEEE;">';
        $html .= '<p>' . $text . '</p>';
        return $html;
    }

    /**
     * Function that renders the update buttons
     */
    public function renderUpdateButton()
    {
        $url = Mage::helper("adminhtml")->getUrl('adminhtml/ophirahcore/install');
        $button = Mage::app()->getLayout()->
        createBlock('adminhtml/widget_button')->
        setType('button')->
        setStyle('width: 100%;')->
        setLabel($this->__('Update Cart2Quote'))->
        setOnClick("setLocation('" . $url . "')")->
        toHtml();

        return $button;
    }

    /**
     * Function that renders the installation buttons
     */
    public function renderInstallButton()
    {
        $url = Mage::helper("adminhtml")->getUrl('adminhtml/ophirahcore/install');
        $button = Mage::app()->getLayout()->
        createBlock('adminhtml/widget_button')->
        setType('button')->
        setStyle('width: 100%;')->
        setLabel($this->__('Install Cart2Quote'))->
        setOnClick("setLocation('" . $url . "')")->
        toHtml();

        return $button;
    }
}
