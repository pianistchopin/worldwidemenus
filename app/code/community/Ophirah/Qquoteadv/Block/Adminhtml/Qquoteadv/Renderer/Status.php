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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Renderer_Status
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Show status and substatus in Grid
     *
     * @param Varien_Object $row
     * @return array
     */
    public function render(Varien_Object $row)
    {
        // Retrieve values
        $status = (int)$row->getData('status');
        $substatus = $row->getData('substatus');
        // Get array of all statuses incl. substatuses
        $gridOptionArray = Mage::getModel('qquoteadv/status')->getGridOptionArray(true);

        // Build combined array if substatuses exists
        if ($substatus && Ophirah_Qquoteadv_Model_Substatus::substatuses()){
            if (Mage::getModel('qquoteadv/substatus')->getParentStatus($substatus) == $status) {
                return $gridOptionArray[$substatus];
            } else {
                if (isset($gridOptionArray[$status])) {
                    return $gridOptionArray[$status];
                } else {
                    // If status is not found
                    // set a default status
                    //return Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN;
                    return Mage::helper('qquoteadv')->__('STATUS_PROPOSAL_BEGIN');
                }
            }
        }

        // Return only main statuses
        if (isset($gridOptionArray[$status])) {
            return $gridOptionArray[$status];
        } else {
            // If status is not found
            // set a default status
            //return Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN;
            return Mage::helper('qquoteadv')->__('STATUS_PROPOSAL_BEGIN');
        }
    }
}
