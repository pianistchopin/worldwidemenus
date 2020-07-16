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
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Field_Support
 */
class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Field_Support extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    CONST URL_LOADER = "https://www.ioncube.com/loaders.php";
    CONST URL_CART2QUOTE_HC = "https://cart2quote.zendesk.com/hc/en-us/articles/201151339-Cart2Quote-IOnCube-files-installation";

    /**
     * Default render call for the Magento config page.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->renderInfo();
    }

    /**
     * Print basic info data
     *
     * @return string
     */
    public function renderInfo(){
        $html = '<tr><th style="min-width: 150px;">Name</th><th>Version/Data</th></tr>';
        $html .= '<tr><td>PHP version: </td><td>'.$this->getPHPVersion().'</td></tr>';
        $html .= '<tr><td>IonCube version: </td><td>'.$this->getIonCubeVersion().'</td></tr>';
        $html .= '<tr><td>Current domain: </td><td>'.$this->getCurrentDomain().'</td></tr>';
        $html .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
        $html .= '<tr><td>Magento version: </td><td>'.$this->getMagentoVersion().'</td></tr>';
        $html .= '<tr><td>Magento edition: </td><td>'.$this->getMagentoEdition().'</td></tr>';
        $html .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
        $html .= '<tr><td>Cart2Quote version: </td><td>'.$this->getCart2QuoteVersion().'</td></tr>';
        $html .= '<tr><td>Cart2Quote edition: </td><td>' . $this->getCart2QuoteEdition() .
            ' <a href="https://www.cart2quote.com/magento-quotation-module-plans-pricing.html"
            target="_blank">(Compare Editions)</a>' . '</td></tr>';
        $html .= '<tr><td>Cart2Quote license: </td><td>'.$this->getCart2QuoteLicense().'</td></tr>';
        if ($this->renderCart2QuoteExpiryDate()) {
            $html .= '<tr><td>Cart2Quote trial: </td><td>'.$this->renderCart2QuoteExpiryDate().'</td></tr>';
        }
        $html .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';

        if (Mage::getConfig()->getNode('modules/Ophirah_Not2Order')) {
            $html .= '<tr><td>Not2Order version: </td><td>'.$this->getNot2OrderVersion().'</td></tr>';
        }

        if (Mage::getConfig()->getNode('modules/Ophirah_Crmaddon')) {
            $html .= '<tr><td>CRMaddon version: </td><td>'.$this->getCRMaddonVersion().'</td></tr>';
        }
        return $html;
    }

    /**
     * This function is called by the code that is added to encrypted files.
     * Only when ionCube is not installed. To prevent a white page, we show this info.
     *
     * @return string
     */
    public function renderNoIc()
    {
        $text = "<strong>IonCube is not installed:</strong> Cart2Quote requires ionCube to be installed, for more information, contact your server admin, your web host or <a href='".self::URL_LOADER."'>look here</a>. ";

        $html = '<div style="text-align: left; padding: 20px; font-family: Arial, Helvetica, sans-serif; background-color: #EEEEEE;">';
        $html .= '<p>'.$text.'</p>';
        $html .= '<br>';

        $html .= '<table style="text-align: left;">';
        $html .= $this->renderInfo();
        $html .= '</table></div>';
        return $html;
    }

    /**
     * This code is run when a fatal error occurs because the wrong version of ionCube is used on Cart2Quote
     * If people have error reporting disabled in PHP, they only see a blank page. This prevents that.
     *
     * @return string
     */
    public function renderWrongIc(){
        $text = "<strong>You are not using the correct version of ionCube:</strong> Use the <a href='".self::URL_CART2QUOTE_HC."'>correct ionCube files</a> which comes with Cart2Quote, or <a href='".self::URL_LOADER."'>update ionCube</a>.";

        $html = '<div style="text-align: left; padding: 20px; font-family: Arial, Helvetica, sans-serif; background-color: #EEEEEE;">';
        $html .= '<p>'.$text.'</p>';
        $html .= '<br>';

        $html .= '<table style="text-align: left;">';
        $html .= $this->renderInfo();
        $html .= '</table></div>';
        return $html;
    }

    /**
     * Get the Cart2Quote version
     *
     * @return mixed
     */
    public function getCart2QuoteVersion(){
        $version = Mage::getConfig()->getModuleConfig("Ophirah_Qquoteadv")->version;
        return $version;
    }

    /**
     * Get the Not2Order version
     *
     * @return mixed
     */
    public function getNot2OrderVersion(){
        $version = Mage::getConfig()->getModuleConfig("Ophirah_Not2Order")->version;
        return $version;
    }

    /**
     * Get the CRMaddon version
     *
     * @return mixed
     */
    public function getCRMaddonVersion(){
        $version = Mage::getConfig()->getModuleConfig("Ophirah_Crmaddon")->version;
        return $version;
    }

    /**
     * If ionCube is loaded, get the version
     *
     * @return string
     */
    public function getIonCubeVersion(){
        if (extension_loaded('ionCube Loader')) {
            $ioncube_version = $this->ioncube_loader_version();
            return $ioncube_version;
        } else {
            return 'IonCube is not installed';
        }
    }

    /**
     * Get the PHP version
     *
     * @return string
     */
    public function getPHPVersion(){
        $version = phpversion();
        return $version;
    }

    /**
     * Get the Cart2Quote license
     *
     * @return mixed
     */
    public function getCart2QuoteLicense(){
        $license_key = Mage::getStoreConfig('qquoteadv_general/quotations/licence_key');
        return $license_key;
    }

    /**
     * Get the Cart2Quote edition
     * This data is only available if Cart2Quote gets enabled in the global config page
     *
     * @return string
     */
    public function getCart2QuoteEdition(){
        $edition = Mage::getStoreConfig('qquoteadv_general/quotations/edition');

        if(!isset($edition) || empty($edition)){
            $edition = 'unknown';
        }

        return $edition;
    }

    /**
     * Get the Magento version
     *
     * @return mixed
     */
    public function getMagentoVersion(){
        return Mage::getVersion();
    }

    /**
     * Get the Magento edition if that function is available
     * If not, then the Magento version is probably below 1.7
     *
     * @return string
     */
    public function getMagentoEdition(){
        if(method_exists('Mage', 'getEdition')){
            $edition = Mage::getEdition();
            return $edition;
        } else {
            return '';
        }
    }

    /**
     * Get the current domain
     *
     * @return mixed
     */
    public function getCurrentDomain(){
        $host = Mage::getStoreConfig('web/unsecure/base_url', 0);
        if (!$host) {
            $host = Mage::getStoreConfig('web/secure/base_url', 0);
            if (!$host) {
                if (!empty($_SERVER['HTTP_HOST'])) {
                    $host = $_SERVER['HTTP_HOST'];
                } else {
                    $host = $_SERVER['SERVER_NAME'];
                }
            }
        }
        $host = self::_stripUrlForLicenseKeyCheck($host);

        return $_SERVER['SERVER_NAME']." (".$host.")";
    }

    /**
     * Function to strip http/https, www and / form a domain
     *
     * @param $input
     * @return mixed
     */
    final private function _stripUrlForLicenseKeyCheck($input)
    {
        $input = trim($input, '/');

        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }
        $urlParts = parse_url($input);

        $domain = preg_replace('/^www\./', '', $urlParts['host']);

        return $domain;
    }

    /**
     * This function gets the ionCube version from the integer version sting
     * It also has a fallback for ionCube < v3.1
     *
     * @return string
     */
    public function ioncube_loader_version()
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
     * Get the Cart2Quote expiry date
     * This data is only available if Cart2Quote gets enabled in the global config page
     *
     * @return string
     */
    public function getCart2QuoteExpiryDate(){
        $expiryDate = Mage::getStoreConfig('qquoteadv_general/quotations/expiry_date');

        if(!isset($expiryDate) || empty($expiryDate)){
            $expiryDate = 'unknown';
        }

        return $expiryDate;
    }

    /**
     * Get the Cart2Quote trial expired
     * This data is only available if Cart2Quote gets enabled in the global config page
     *
     * @return string
     */
    public function getCart2QuoteTrialExpired(){
        return Mage::getStoreConfig('qquoteadv_general/quotations/has_expired');
    }

    /**
     * Function to render the expiry date message
     * Returns false if there is no need to share this information
     *
     * @return bool|string
     */
    public function renderCart2QuoteExpiryDate(){
        if($this->getCart2QuoteEdition() == Ophirah_Qquoteadv_Helper_License::FREE_LICENSE_NAME || $this->getCart2QuoteEdition() == 'Enterprise (trial)'){
            if($this->getCart2QuoteTrialExpired()){
                //expired
                if($this->getCart2QuoteExpiryDate() != 'unknown'){
                    if($this->getCart2QuoteExpiryDate() > date("Ymd")){
                        //forced expiry
                        return "Disabled";
                    } else {
                        //normal expired
                        $c2qExpDate = date('F j, Y', strtotime($this->getCart2QuoteExpiryDate()));
                        return "Has expired on ".$c2qExpDate;
                    }
                } else {
                    return "Has expired";
                }
            } else {
                //not expired
                $c2qExpDate = date('F j, Y', strtotime($this->getCart2QuoteExpiryDate()));
                return "Expires on ".$c2qExpDate;
            }
        } else {
            return false;
        }
    }
}
