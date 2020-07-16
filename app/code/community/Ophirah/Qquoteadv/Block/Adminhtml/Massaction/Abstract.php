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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Massaction_Abstract
 */
abstract class Ophirah_Qquoteadv_Block_Adminhtml_Massaction_Abstract extends Mage_Adminhtml_Block_Widget
{
    /**
     * Customer groups cache
     *
     * @var array
     */
    protected $_customerGroups;

    /**
     * Websites cache
     *
     * @var array
     */
    protected $_websites;

    protected $_groupName;

    /**
     * Prepare group price values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array();
        $data = $this->getValue();

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }
        return $values;
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        return $data;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getCustomerGroups($groupId = null)
    {
        if ($this->_customerGroups === null) {
            if (!Mage::helper('catalog')->isModuleEnabled('Mage_Customer')) {
                return array();
            }
            $collection = Mage::getModel('customer/group')->getCollection();
            $this->_customerGroups = $this->_getInitialCustomerGroups();

            foreach ($collection as $item) {
                /** @var $item Mage_Customer_Model_Group */
                $this->_customerGroups[$item->getId()] = $item->getCustomerGroupCode();
            }
        }

        if ($groupId !== null) {
            return isset($this->_customerGroups[$groupId]) ? $this->_customerGroups[$groupId] : array();
        }

        return $this->_customerGroups;
    }

    /**
     * Retrieve list of initial customer groups
     *
     * @return array
     */
    protected function _getInitialCustomerGroups()
    {
        return array();
    }

    /**
     * Retrieve number of websites
     *
     * @return int
     */


    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */


    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     */


    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    public function getDefaultCustomerGroup()
    {
        return Mage_Customer_Model_Group::CUST_GROUP_ALL;
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     */
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return Mage::app()->getStore($this->getProduct()->getStoreId())->getWebsiteId();
        }
        return 0;
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $groupName = $this->getGroupName();
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('qquoteadv')->__('Add Group'),
                'onclick' => 'return '.$groupName.'GroupControl.addItem()',
                'class' => 'add'
            ));
        $button->setName('add_group_item_button');

        $this->setChild('add_button', $button);
        return parent::_beforeToHtml();
    }

    /**
     * Getter for the group name
     *
     * @return mixed
     */
    public function getGroupName(){
        return $this->_groupName;
    }

    /**
     * @param $_groupName
     * @return $this
     */
    public function setGroupName($_groupName){
        $this->_groupName = $_groupName ;
        return $this;
    }
}
