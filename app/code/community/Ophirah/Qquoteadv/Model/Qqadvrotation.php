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
 * Class Ophirah_Qquoteadv_Model_Qqadvrotation
 */
class Ophirah_Qquoteadv_Model_Qqadvrotation extends Mage_Sales_Model_Quote
{
    /**
     * Construct function
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/qqadvrotation');
    }

    /**
     * @param int $roleId
     * @return int
     * @throws Exception
     */
    public function getNextUserId($roleId)
    {
        $connection = $this->getResource()->getReadConnection();
        $connection->beginTransaction();

        try {
            /** @var $collection Ophirah_Qquoteadv_Model_Mysql4_Qqadvrotation_Collection */
            $collection = $this->getCollection();
            $collection->addFilter('role_id', $roleId);
            $collection->getSelect()->limit(1);

            $rotationModel = $collection->getFirstItem();
            $rotationModel->setRoleId($roleId);

            $lastUserId = $rotationModel->getUserId();

            // Next user from last user and group
            $nextUser = $this->getNextUser($lastUserId, $roleId);
            if ($nextUser->getId() !== null) {
                $nextUserId = $nextUser->getId();
            } else {
                // Reset rotation
                $nextUser = $this->getNextUser(0, $roleId);
                if ($nextUser->getId() !== null) {
                    $nextUserId = $nextUser->getId();
                } else {
                    $nextUserId = 0;
                }
            }

            $rotationModel->setUserId($nextUserId);
            $rotationModel->save();
            $connection->commit();

            return $nextUserId;
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param int $lastUserId
     * @param int|null $roleId
     * @return Mage_Admin_Model_User
     */
    protected function getNextUser($lastUserId, $roleId = null)
    {
        $roleId = (int)$roleId;
        $lastUserId = (int)$lastUserId;

        /** @var $userCollection Mage_Admin_Model_Mysql4_User_Collection */
        $userCollection = Mage::getModel('admin/user')->getCollection();
        $userCollection
            ->addFieldToFilter('main_table.user_id', array('gt' => $lastUserId))
            ->setOrder('main_table.user_id', Varien_Data_Collection::SORT_ORDER_ASC);

        if ($roleId !== null) {
            $userCollection
                ->join(array('role' => 'admin/role'), 'role.user_id = main_table.user_id', array())
                ->addFieldToFilter('role.parent_id', $roleId);
        }
        $userCollection->getSelect()->limit(1);

        return $userCollection->getFirstItem();
    }
}
