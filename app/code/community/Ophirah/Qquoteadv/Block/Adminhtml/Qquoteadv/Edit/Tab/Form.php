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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Tab_Form
 * @deprecated THIS CLASS IS DEPRECATED AND WILL BE REMOVED IN C2Q 7.0.0
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Set quote template to display quote information in admin tab
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('qquoteadv/quote.phtml');
    }

    /**
     * Get Quote information from qquote_customer table
     * @return object
     */
    public function getQuoteData()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $quote = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);
        return $quote;
    }
}
