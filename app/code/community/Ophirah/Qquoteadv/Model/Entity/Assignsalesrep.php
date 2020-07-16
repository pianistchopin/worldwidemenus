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
 * Class Ophirah_Qquoteadv_Model_Entity_Assignsalesrep
 */
class Ophirah_Qquoteadv_Model_Entity_Assignsalesrep extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get all options renderer for the select of assignsalesrep
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = array();
            $this->_options[] = array(
                'value' => '',
                'label' => 'Auto assign'
            );

            $admins = Mage::helper('qquoteadv')->getAdmins();
            foreach ($admins as $admin){
                $this->_options[] = array(
                    'value' => $admin->getData('user_id'),
                    'label' => $admin->getData('firstname') . " " . $admin->getData('lastname')
                );
            }
        }

        return $this->_options;
    }
}
