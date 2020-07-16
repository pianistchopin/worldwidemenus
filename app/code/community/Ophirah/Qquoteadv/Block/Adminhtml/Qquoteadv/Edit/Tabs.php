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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Tabs
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * Construct function
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('qquote_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('qquoteadv')->__('Quote view'));
    }

//    /**
//     * @var
//     */
//    private $parent;
//
//    /**
//     * Prepare layout function
//     * @deprecated: now managed in the qquoteadv.xml layout file
//     * @return mixed
//     */
//    protected function _prepareLayout()
//    {
//        //get all existing tabs
//        $this->parent = parent::_prepareLayout();
//        $this->addTab('product', array(
//            'label' => Mage::helper('qquoteadv')->__('Quote request'),
//            'title' => Mage::helper('qquoteadv')->__('Quote request'),
//            'content' => $this->getLayout()->createBlock('qquoteadv/adminhtml_qquoteadv_edit_tab_product')->toHtml(),
//        ));
//        return $this->parent;
//    }
}
