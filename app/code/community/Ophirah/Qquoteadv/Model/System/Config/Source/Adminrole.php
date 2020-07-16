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
 * Class Ophirah_Qquoteadv_Model_System_Config_Source_Adminrole
 */
class Ophirah_Qquoteadv_Model_System_Config_Source_Adminrole
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $roleCollection Mage_Admin_Model_Resource_Role_Collection */
        $roleCollection = Mage::getModel('admin/role')->getCollection();
        $roleCollection->addFilter('role_type', 'G')
            ->setOrder('role_name', Varien_Data_Collection::SORT_ORDER_ASC);

        $options = array();
        /** @var $role Mage_Admin_Model_Role */
        foreach ($roleCollection as $role) {
            $options[] = array('value' => $role->getId(), 'label' => $role->getRoleName());
        }
        return $options;
    }
}
