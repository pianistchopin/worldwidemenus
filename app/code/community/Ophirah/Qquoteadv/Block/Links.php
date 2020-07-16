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
 * Class Ophirah_Qquoteadv_Block_Links
 */
class Ophirah_Qquoteadv_Block_Links extends Mage_Core_Block_Template
{
    /**
     * Add Quote link to parent block
     *
     * @return Ophirah_Qquoteadv_Block_Links
     */
    public function addQuoteLink()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Ophirah_Qquoteadv')) {
            if(Mage::getStoreConfig('qquoteadv_general/quotations/active_c2q_tmpl')){
                $text = Mage::helper('qquoteadv')->totalItemsText();
                $parentBlock->addLink($text, 'qquoteadv/index', $text, true, array(), 50, null, 'class="top-link-qquoteadv"');
            }
        }
        return $this;
    }

    /**
     * Add admin link to parent block
     *
     * @return Ophirah_Qquoteadv_Block_Links
     */
    public function addAdminLink()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Ophirah_Qquoteadv')) {
            if(Mage::getStoreConfig('qquoteadv_general/quotations/active_c2q_tmpl')){
                $helper = Mage::helper('qquoteadv');
                if ($helper->getAdminUser() === null) {
                    $parentBlock->addLink('Sales representative', 'javascript:adminLogin(\'' . Mage::helper("adminhtml")->getUrl("adminhtml/index/login/") . '\');', 'Sales representative', false, array(), 50, null, 'id="top-link-qquoteadv-admin"');
                }
            }
        }
        return $this;
    }


}