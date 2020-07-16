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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Crmaddon_Model_Observer_Email
 */
class Ophirah_Crmaddon_Model_Observer_Email{

    /**
     * Log email data when sending an email from Cart2Quote CRMAddon
     *
     * @param $observer
     */
    public function ophirahCrmaddonSendEmailBefore($observer){
        $mailTemplate = $observer->getEvent()->getData();
        if(is_array($mailTemplate)){
            foreach($mailTemplate as $template){
                if(is_object($template) && ($template instanceof Mage_Core_Model_Email_Template)){
                    $mailTemplate = $template;
                    break;
                } else {
                    if(is_object($template)){
                        Mage::log("DEBUG CRMAddon: Class of non logged email object: ".get_class($template), null, 'c2q.log');
                    }
                }
            }
        }

        if(!is_array($mailTemplate)){
            $logEnabled = (int) Mage::getStoreConfig('qquoteadv_advanced_settings/general/force_log', $mailTemplate->getTemplateFilter()->getStoreId());
            if($logEnabled > 0){
                $log['emailClass'] = get_class($mailTemplate);
                $log['emailClassMage'] = get_class(Mage::getModel('core/email_template'));
                $log['emailData'] = $mailTemplate->getData();
                $log['emailBcc'] = $mailTemplate->getMail()->getRecipients();
                $log['emailHeader'] = $mailTemplate->getMail()->getHeaders();

                Mage::log($log, null, 'c2q_email.log', true);
            }
        }
    }
}
