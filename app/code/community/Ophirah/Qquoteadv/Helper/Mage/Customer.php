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
 * Class Ophirah_Qquoteadv_Helper_Mage_Customer
 */
final class Ophirah_Qquoteadv_Helper_Mage_Customer extends Mage_Core_Helper_Abstract {
    /**
     * Generate a random password
     *
     * @param int $length
     * @return string           // Random password
     */
    protected function _generatePassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    /**
     * Get customer session data
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Function that verifies if an email already exists in the database
     *
     * @param $email
     * @return bool
     */
    public function userEmailAlreadyExists($email)
    {
        $return = false;
        $customer = Mage::getModel('customer/customer');

        $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

        if ($customer->getId()) {
            $return = true;
        }

        return $return;
    }

    /**
     * Add customer account with random password
     * NOTE: It looks like this function isn't used by default
     * It is the same function as \Ophirah_Qquoteadv_IndexController::_createCustomerAccount
     *
     * @param string $email
     * @param Mage_Customer_Model_Address $billingAddress
     * @return Mage_Customer_Model_Customer
     * @throws Exception
     */
    public function _createCustomerAccount($email, Mage_Customer_Model_Address $billingAddress)
    {
        //#customer account and address
        if (isset($email) && is_string($email)) {
            //#create new account and autologin
            if (!$this->getCustomerSession()->isLoggedIn() && !$this->_isEmailExists()) {
                $password = Mage::helper('qquoteadv/mage_customer')->_generatePassword(7);
                $is_subscribed = 0;

                //# NEW USER REGISTRATION
                if (!$this->getCustomerSession()->isLoggedIn()) {
                    $customerModel = Mage::getModel('customer/customer');
                    $customerModel->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

                    //#create new user
                    if (!$customerModel->getId()) {
                        /** @var Ophirah_Qquoteadv_Model_Customer_Customer $customer */
                        $customer = Mage::getModel('qquoteadv/customer_customer');
                        $customer
                            ->setFirstname($billingAddress->getFirstname())
                            ->setLastname($billingAddress->getLastname())
                            ->setEmail($email)
                            ->setPassword($password)
                            ->setPasswordHash(md5($password))
                            ->setIsSubscribed($billingAddress->getTaxVat())
                            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

                        Mage::dispatchEvent(
                            'qquoteadv_qqadvcustomer_before_newCustomer',
                            array('customer' => $customer)
                        );
                        try {
                            $customer->save();
                        } catch (Exception $e) {
                            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                            return $this->__("Unable to to save customer. Email: %s", $email);
                        }
                        Mage::dispatchEvent(
                            'qquoteadv_qqadvcustomer_after_newCustomer',
                            array('customer' => $customer)
                        );
                        $customerId = $customer->getId();

                        try {
                            if ($customer->isConfirmationRequired()) {
                                $this->getCustomerSession()->setNotConfirmedId($customer->getId());
                                $customer->sendNewQuoteAccountEmail(
                                    'confirmation',
                                    $this->getCustomerSession()->getBeforeAuthUrl(),
                                    Mage::app()->getStore()->getId()
                                );
                            } else {
                                $this->getCustomerSession()->login($email, $password);
                                $customer->sendNewQuoteAccountEmail(
                                    'registered_qquoteadv',
                                    '',
                                    Mage::app()->getStore()->getId()
                                );
                            }
                        } catch (Exception $e) {
                            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                        }
                    }
                }
            } else {
                $customer = $this->getCustomerSession()->getCustomer();
                $customerId = $customer->getId();
            }

            //EMAIL IS REGESTERED BUT CUSTOMER IS STILL NOT LOGGED IN
            if (empty($customerId) && $this->_isEmailExists()) {
                $email = trim($email);
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);
                $customerId = $customer->getId();
            }

            if (empty($customerId)) throw new Exception('Customer id does not exist. Cannot place quote request');
            if(!isset($customer)){
                throw new Exception('Customer does not exist. Cannot place quote request');
            }else{
                return $customer;
            }

        }
        throw new Exception('Customer does not exist. Cannot place quote request');
    }
}
