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
 * Class Ophirah_Qquoteadv_Helper_Email
 */
final class Ophirah_Qquoteadv_Helper_Email extends Mage_Core_Helper_Abstract
{
    /**
     * Function that adds support for SMTPPro and Amasty SMTP
     *
     * @param null $storeId
     * @return mixed
     */
    public function getEmailTemplateModel($storeId = null){
        if($storeId == null){
            $storeId = Mage::app()->getStore()->getId();
        }

        if(Mage::helper('core')->isModuleEnabled('Aschroder_SMTPPro')){
            return Mage::getModel('qquoteadv/core_email_templatesmtppro');
        }

        if(Mage::helper('core')->isModuleEnabled('Amasty_Smtp')){
            return Mage::getModel('qquoteadv/core_email_templatesmtpamasty');
        }

        if(Mage::helper('core')->isModuleEnabled('Ebizmarts_Mandrill')){
            $mandrillModel = Mage::getModel('qquoteadv/core_email_templatemandrill');
            $mandrillModel->setStoreId($storeId);
            return $mandrillModel;
        }

        if(Mage::helper('core')->isModuleEnabled('Bronto_Email')){
            return Mage::getModel('core/email_template');
        }

        //return Mage::getModel('qquoteadv/core_email_template');
        return Mage::getModel('core/email_template');
    }
}
