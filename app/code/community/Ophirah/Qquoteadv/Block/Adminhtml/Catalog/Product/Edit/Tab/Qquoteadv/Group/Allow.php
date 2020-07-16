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
 * Adminhtml tier price item renderer
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Catalog_Product_Edit_Tab_Qquoteadv_Group_Allow
    extends Ophirah_Qquoteadv_Block_Adminhtml_Catalog_Product_Edit_Tab_Qquoteadv_Group_Abstract

{

    /**
     * Initialize block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('qquoteadv/catalog/product/edit/allow.phtml');
    }

    /**
     * Retrieve list of initial customer groups
     *
     * @return array
     */
    protected function _getInitialCustomerGroups()
    {
        return array(); // array(Mage_Customer_Model_Group::CUST_GROUP_ALL => Mage::helper('catalog')->__('ALL GROUPS'));
    }

    /**
     * @return int|null|string
     */
    public function getWebsiteId() {
        $storeId = Mage::app()->getRequest()->getParam('store', 0);
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        return $websiteId;
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('catalog')->__('Add Group'),
                'onclick' => 'return quoteGroupControl.addItem()',
                'class' => 'add'
            ));
        $button->setName('add_group_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Function that generates a note of the default allowed customer groups
     */
    protected function getDefaultAllowedGroupsNote()
    {
        $note = '';
        $product = $this->getProduct();
        $allowed = (int)$product->getAllowedToQuotemode();
        $allowGroups = $product->getGroupAllowQuotemode();
        $customerGroups = $this->getCustomerGroups();

        //fall back to defaults when no groups are set
        if ($allowed && (!is_array($allowGroups) || empty($allowGroups))) {
            $allowGroups = Mage::helper('qquoteadv/licensechecks')->getDefaultAllowedGroups();
            if (is_array($allowGroups) && !empty($allowGroups)) {
                $note .= 'The following groups are enabled by default as there are no groups given on this product: <br />';
                foreach ($allowGroups as $allowGroup) {
                    if (isset($customerGroups[$allowGroup['cust_group']]) && ($allowGroup['value'] == 1)) {
                        $note .= ' - ' . $customerGroups[$allowGroup['cust_group']] . ' <br />';
                    }
                }

                $note .= '(You can change this with the setting: "System>Configuration>Cart2Quote>Advanced Settings>Mass Update>Default allowed customer groups")';
            }
        }

        return $note;
    }
}
