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
 * Catalog product abstract group price backend attribute model
 */
abstract class Ophirah_Qquoteadv_Model_Catalog_Product_Attribute_Backend_Qquoteadv_Group_Abstract
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Validate group price data
     *
     * @param Mage_Catalog_Model_Product $object
     * @return bool
     */
    public function validate($object)
    {
        return true;
    }

    /**
     * Assign group prices to product data
     *
     * @param Mage_Catalog_Model_Product $object
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Groupprice_Abstract
     */
    public function afterLoad($object)
    {

        $storeId = $object->getStoreId();
        $websiteId = null;
        if ($this->getAttribute()->isScopeGlobal()) {
            $websiteId = 0;
        } elseif ($storeId) {
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        } else {
            $websiteId = 0;
        }

        $data = $this->_getResource()->loadGroupData($object->getId(), $websiteId);
        foreach ($data as $k => $v) {
            //$data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }
        }


        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
    }

    /**
     * After Save Attribute manipulation
     *
     * @param Mage_Catalog_Model_Product $object
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Groupprice_Abstract
     */
    public function afterSave($object)
    {

        $websiteId = Mage::app()->getStore($object->getStoreId())->getWebsiteId();
        $isGlobal = $this->getAttribute()->isScopeGlobal() || $websiteId == 0;
        $groupRows = $object->getData($this->getAttribute()->getName());


        if (empty($groupRows)) {
            $this->_getResource()->deleteGroupData($object->getId());
            return $this;
        }

        $old = array();
        $new = array();

        $origGroupRows = $object->getOrigData($this->getAttribute()->getName());
        if (!is_array($origGroupRows)) {
            $origGroupRows = array();
        }
        foreach ($origGroupRows as $data) {
            if ($data['website_id'] > 0 || ($data['website_id'] == '0' && $isGlobal)) {
                $key = join('-', array_merge(
                    array($data['website_id'], $data['cust_group']),
                    $this->_getAdditionalUniqueFields($data)
                ));
                $old[$key] = $data;
            }
        }

        // prepare data for save
        foreach ($groupRows as $data) {
            $hasEmptyData = false;
            foreach ($this->_getAdditionalUniqueFields($data) as $field) {
                if (empty($field)) {
                    $hasEmptyData = true;
                    break;
                }
            }

            if ($hasEmptyData || !isset($data['cust_group']) || !empty($data['delete'])) {
                continue;
            }
            if ($this->getAttribute()->isScopeGlobal() && $data['website_id'] > 0) {
                continue;
            }
            if (!$isGlobal && (int)$data['website_id'] == 0) {
                continue;
            }

            if (!isset($data['website_id'])) $data['website_id'] = 0;

            $key = join('-', array_merge(
                array($data['website_id'], $data['cust_group']),
                $this->_getAdditionalUniqueFields($data)
            ));

            $useForAllGroups = $data['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL;
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;


            $new[$key] = array_merge(array(
                'website_id' => $data['website_id'],
                'all_groups' => $useForAllGroups ? 1 : 0,
                'customer_group_id' => $customerGroupId,
                'value' => $data['value'],
            ), $this->_getAdditionalUniqueFields($data));
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged = false;
        $productId = $object->getId();

        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->_getResource()->deleteGroupData($productId, null, $data['value_id']);
                $isChanged = true;
            }
        }

        if (!empty($insert)) {
            foreach ($insert as $data) {
                $group = new Varien_Object($data);
                $group->setEntityId($productId);
                $this->_getResource()->saveGroupData($group);

                $isChanged = true;
            }
        }

        if (!empty($update)) {
            foreach ($update as $k => $v) {


                if ($old[$k]['value'] != $v['value']) {
                    $group = new Varien_Object(array(
                        'value_id' => $old[$k]['value_id'],
                        'value' => $v['value']
                    ));
                    $this->_getResource()->saveGroupData($group);

                    $isChanged = true;
                }
            }
        }

        if ($isChanged) {
            $valueChangedKey = $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
        }

        return $this;
    }

    /**
     * Overwrite for _getAdditionalUniqueFields
     *
     * @param $data
     * @return array
     */
    protected function _getAdditionalUniqueFields($data)
    {
        return array();
    }
}
