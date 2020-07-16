<?php

/**
 * Blackbird AjaxVatChecker Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category            Blackbird
 * @package		Blackbird_AjaxVatChecker
 * @copyright           Copyright (c) 2015 Blackbird (http://black.bird.eu)
 * @author		Blackbird Team
 * @license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Blackbird_AjaxVatChecker_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        //getting vat number and country
        $vat = strtoupper(str_replace(array('-', ' ', '.'), '', $this->getRequest()->getParam('vat')));
        $country = '' . $this->getRequest()->getParam('country');

        //add default country
        if (!preg_match("#[a-zA-Z]#", $vat) && !$country) {
            
            //if customer is logged in and have default shipping address
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if ($customer->getDefaultBillingAddress() && $customer->getDefaultBillingAddress()->getCountry()) {
                   
                    $countryCodeDefault = $customer->getDefaultBillingAddress()->getCountry();
                    $vat = $countryCodeDefault . $vat;
                }
            }
        }

        //checking first 2 characters of VAT number
        if (preg_match("#[a-zA-Z]#", $vat)) {
            $codeCountry = mb_strimwidth($vat, 0, 2);
            $vat = mb_strimwidth($vat, 2, strlen($vat) - 2);
        }
        
        //checking VAT number via API
        $newCountryCode = $codeCountry ? $codeCountry : $country;
        $status = Mage::helper('customer')->checkVatNumber($newCountryCode, $vat);

        //if the VAT number is valid send 'valid' message/status
        if ($status->getData('is_valid')) {

            $country = Mage::getModel('directory/country')->loadByCode($newCountryCode);
            $result = array(
                'status' => true,
                'vat' => $vat,
                'codecountry' => $newCountryCode,
                'message' => Mage::helper('ajaxvatchecker')->__('Your VAT number %s is valid for %s', $vat, $country->getName())
            );
        }
        //else send 'wrong' message/status
        else {
            $result = array(
                'status' => false,
                'message' => Mage::helper('ajaxvatchecker')->__('VAT number is not valid. Please enter country prefix or chose your country below')
            );
        }

        //send data in json format
        $jsonData = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

}
