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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Field_Other
 */
class Ophirah_Qquoteadv_Block_Adminhtml_System_Config_Field_Other extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Main function that is called by system.xml
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';
        if($this->versionCheckEmailResponsive()){
            $html = $this->checkForEmailTemplateLoop($html);
        }
        $html .= $this->checkForCurrencySetup();
        $html .= $this->checkForDatabaseSetup('qquoteadv_general/quotations/last_update_version', 'Ophirah_Qquoteadv', 'qquoteadv_setup', "adminhtml/qquoteadv/fixdatabase");
        $html .= $this->checkForDatabaseSetup('qquoteadv_sales_representatives/last_update_version', 'Ophirah_Crmaddon', 'crmaddon_setup', "adminhtml/crmaddon/fixdatabase");
        $html .= $this->checkForDatabaseSetup('supplierbiddingtool/last_update_version', 'Ophirah_SupplierBiddingTool', 'supplierbiddingtool_setup', "adminhtml/supplier/fixdatabase");
        $html .= $this->checkForModSecurity();
        $html .= $this->checkShippingMethodEnabled();
        //$html .= $this->checkNotToOrderTemplateEnabled(); //This is not needed

        if($html == ''){
            $html = 'No possible issues found';
        }
        return $html;
    }

    /**
     * Function that checks for each store if Cart2Quote and Not2Order are enabled
     * and if both modules templates are enabled.
     * If so, generate an warning.
     *
     * @return string
     */
    public function checkNotToOrderTemplateEnabled() {
        $message = '';

        if(Mage::helper('core')->isModuleEnabled('Ophirah_Not2Order')){
            foreach (Mage::app()->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        //check if Cart2Quote is enabled for this store
                        $c2qEnabled = Mage::getStoreConfig('qquoteadv_general/quotations/enabled', $store->getId());
                        $n2oEnabled = Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/enabled', $store->getId());
                        $c2qTemplate = Mage::getStoreConfig('qquoteadv_general/quotations/active_c2q_tmpl', $store->getId());
                        $n2oTemplate = Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/usetemplates', $store->getId());

                        if($c2qEnabled && $n2oEnabled && $c2qTemplate && $n2oTemplate){
                            $storeFullName = $website->getName() . ' - ' . $group->getName() . ' - ' . $store->getName();
                            $message .= "Both Cart2Quote and Not2Order themplate overwrites are enabled in the store: ".$storeFullName."<br>";
                        }
                    }
                }
            }
        }

        if($message != ''){
            $message .= 'Please only enable the Cart2Quote Templates when using Not2Order and Cart2Quote.<br>';
            $message .= 'You can fix this by setting "System > Configuration > Cart2Quote > General > Orderability and Prices > Use Built-in Template Files" to "No" <br><br>';
        }

        return $message;
    }



    /**
     * This functions checks if the Cart2Quote shipping method is enabled
     *
     * @return string
     */
    public function checkShippingMethodEnabled(){
        $message = '';

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    //check if Cart2Quote is enabled for this store
                    $c2qEnabled = Mage::getStoreConfig('qquoteadv_general/quotations/enabled', $store->getId());
                    if($c2qEnabled){
                        $c2qShippingEnabled = Mage::getStoreConfig('carriers/qquoteshiprate/active', $store->getId());
                        if(!$c2qShippingEnabled){
                            $storeFullName = $website->getName() . ' - ' . $group->getName() . ' - ' . $store->getName();
                            $message .= "The Cart2Quote shipping method is not enabled for the store: ".$storeFullName."<br>";
                        }
                    }
                }
            }
        }

        if($message != ''){
            $message .= 'You can enable this under "System > Configuration > Sales > Shipping Methods > Cart2Quote" <br><br>';
        }

        return $message;
    }

    /**
     * This function makes a GET call to check if mod_security is triggered
     *
     * @return string
     */
    public function checkForModSecurity(){
        $message = '';

        //only check if curl is enabled
        if (in_array  ('curl', get_loaded_extensions())) {
            $url = rtrim(Mage::getBaseUrl(), '/');
            //$crmMessageBody = "<p><strong>Hello {{var or 1 ; and 2";
            //$url .= "?crm_message=".rawurlencode($crmMessageBody);
            $url .= "?abc=../../";
            $curl = curl_init($url);

            $options = array(
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_MAXREDIRS      => 10,
            );

            curl_setopt_array( $curl, $options );
            //$response = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if($statusCode === 403 || $statusCode === 406){
                $message .= "Note: It looks like mod_security is installed. <br> It is possible that it trows an 403 Forbidden page on CRM actions. (False positive) <br> Consider contacting your server administrator if you experience these kind of issues.";
            }

            curl_close($curl);
        }

        return $message;
    }

    /**
     * Function that checks if all the database scripts are executed
     *
     * @param $configPath
     * @param $moduleName
     * @param $resourceName
     * @param $fixUrl
     * @return string
     */
    public function checkForDatabaseSetup($configPath, $moduleName, $resourceName, $fixUrl)
    {
        $message = '';

        //check if last update version is set
        $last_update_version = Mage::getStoreConfig($configPath);
        if($last_update_version){
            //get all update files
            $filesDir   = Mage::getModuleDir('sql', $moduleName) . DS . $resourceName . DS;
            if (is_dir($filesDir) && is_readable($filesDir)) {
                $foundLastUpdateFile = false;
                foreach(glob($filesDir.'*.php') as $file) {
                    if($foundLastUpdateFile){
                        $message .= "This install script is not executed: ".$file."<br>";
                    } else {
                        if (strpos($file, $last_update_version.'.php') !== false) {
                            $foundLastUpdateFile = true;
                        }
                    }
                }
            }
        }

        if($message != ''){
            $fixDatabaseLink = Mage::helper("adminhtml")->getUrl($fixUrl);
            $message .= '<a href="'.$fixDatabaseLink.'">Click here to (re-)execute these scripts</a> (Make sure to disable cache and compilation) <br><br>';
        }

        return $message;
    }

    /**
     * Function that checks if all the currency rates are set and gives a message if it finds an error
     *
     * @return string
     */
    public function checkForCurrencySetup()
    {
        $message = '';
        $currencyModel = Mage::getModel('directory/currency');
        $currencies = $currencyModel->getConfigAllowCurrencies();
        $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();

        //check for each currency if the rates are set
        foreach ($defaultCurrencies as $currency) {
            $rates = Mage::getModel('directory/currency')->getCurrencyRates($currency, array_values($currencies));
            if(count($rates) != count($currencies)){
                if(count($rates) < 1){
                    $message .= 'No currency conversion rates are set for the currency '.$currency.'<br>';
                } else {
                    $message .= 'Not al currency conversion rates are set for the currency '.$currency.'<br>';
                }
            }
        }

        if($message != ''){
            $message .= 'You can find this setting under "System > Manage Currency > Rates" <br><br>';
        }

        return $message;
    }

    /**
     * Function that checks for each store the email template settings
     * Outputs the results in text format
     *
     * @param $html
     * @return string
     */
    public function checkForEmailTemplateLoop($html) {
        $message = '';

        $checkedDesigns = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    //check for possible header loop
                    $header = Mage::getStoreConfig('design/email/header', $store->getId());
                    if($header){
                        $checkedDesigns = $this->checkEmailTemplateText($header, $checkedDesigns, $store, $website, $group, 'header');
                    }

                    //check for possible footer loop
                    $footer = Mage::getStoreConfig('design/email/footer', $store->getId());
                    if($footer){
                        $checkedDesigns = $this->checkEmailTemplateText($footer, $checkedDesigns, $store, $website, $group, 'footer');
                    }
                }
            }
        }

        foreach($checkedDesigns as $templateId => $checkedDesign){
            if($checkedDesign['check'] == 'fail'){
                if($checkedDesign['type'] == 'header'){
                    $type = 'Email Header Template';
                }
                if($checkedDesign['type'] == 'footer'){
                    $type = 'Email Footer Template';
                }
                $message .= 'WARNING: The configuration scope "'.$checkedDesign['scope'].'" has as "'.$type.'" the email template "'.$checkedDesign['name'].'" that contains an include to "'.$checkedDesign['type_issue'].'", this could possibly result in an infinite loop. <br>';
            }
        }

        if($message != ''){
            $message .= 'You can find this setting under "System > Configuration > General > Design > Transactional Emails" <br><br>';
        }

        return $html . $message;
    }

    /**
     * Function that checks te email template text and generates the results of that
     *
     * @param $templateId
     * @param $checkedDesigns
     * @param $store
     * @param $website
     * @param $group
     * @param $type
     * @return mixed
     */
    public function checkEmailTemplateText(
        $templateId,
        $checkedDesigns,
        $store,
        $website,
        $group,
        $type
    ) {
        //check if this template is already checked
        if (!array_key_exists($templateId, $checkedDesigns)) {
            //load email template
            $template = Mage::getModel('core/email_template'); //note that we can always use core/email_template here
            $storeLocale = Mage::getStoreConfig('general/locale/code', $store->getId());
            if (is_numeric($templateId)) {
                $template->load($templateId);
            } else {
                $template->loadDefault($templateId, $storeLocale);
            }

            //check email template
            if ($template) {
                //get name
                if (is_numeric($templateId)) {
                    $templateName = $template->getTemplateCode();
                } else {
                    if($template->getTemplateCode()){
                        $templateName = $template->getTemplateCode();
                    } else {
                        $templateName = $templateId;
                    }
                }

                //get text
                $templateText = $template->getTemplateText();

                //check text
                if (strpos($templateText, 'design/email/header') !== false) {
                    $checkedDesigns[$templateId]['name'] = $templateName;
                    $checkedDesigns[$templateId]['check'] = 'fail';
                    $checkedDesigns[$templateId]['scope'] = $website->getName() . ' - ' . $group->getName() . ' - ' . $store->getName();
                    $checkedDesigns[$templateId]['type'] = $type;
                    $checkedDesigns[$templateId]['type_issue'] = 'design/email/header';
                } else if (strpos($templateText, 'design/email/footer') !== false) {
                    $checkedDesigns[$templateId]['name'] = $templateName;
                    $checkedDesigns[$templateId]['check'] = 'fail';
                    $checkedDesigns[$templateId]['scope'] = $website->getName() . ' - ' . $group->getName() . ' - ' . $store->getName();
                    $checkedDesigns[$templateId]['type'] = $type;
                    $checkedDesigns[$templateId]['type_issue'] = 'design/email/footer';
                } else {
                    $checkedDesigns[$templateId]['name'] = $templateName;
                    $checkedDesigns[$templateId]['check'] = 'success';
                }
            }
        }

        return $checkedDesigns;
    }

    /**
     * Checks if this functionality is supported in this version of Magento
     * (not available for magento CE <1.9 and <EE 1.14)
     *
     * @return bool|null
     */
    protected function versionCheckEmailResponsive()
    {
        if (method_exists('Mage', 'getEdition')) {
            $edition = Mage::getEdition();
            switch ($edition) {
                case "Community":
                    return version_compare(Mage::getVersion(), '1.9.0.0') <= 0 ? false : true;
                case "Enterprise":
                    return version_compare(Mage::getVersion(), '1.14.0.0') <= 0 ? false : true;
            }
        }

        return false;
    }
}
