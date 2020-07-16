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
 * Class Ophirah_Qquoteadv_Model_Substatus
 */
class Ophirah_Qquoteadv_Model_Substatus extends Mage_Core_Model_Abstract
{
    // Character used for dividing
    // status and substatus
    const EXPLODE_CHARACTER = '.';

    // #### DEFINE SUBSTATUSES ####
    // substatus value is string
    // in this format:
    // [STATUSID][EXPOLODE_CHARACTER][SUBSTATUSID]
    // 
    // EXAMPLE:
    // SUBSTATUS FOR STATUS_REJECTED (value = 30) will look like: 
    // const SUBSTATUS_REJECTED_SUB100  = '30.100';
    //
    // MULTOPLE SUBSTATUSES LOOK LIKE:
    // const SUBSTATUS_REJECTED_SUB100  = '30.100';
    // const SUBSTATUS_REJECTED_SUB200  = '30.200';
    // etc...
    //

    // 30 - Substatus Rejected
    const SUBSTATUS_REJECTED_SUB100 = '30.100';

    // 40 - Substatus Cancelled
    const SUBSTATUS_CANCELLED_SUB100 = '40.100';

    // 50 - Substatus Proposal 
    const SUBSTATUS_PROPOSAL_SUB100 = '50.100';
    const SUBSTATUS_PROPOSAL_SUB200 = '50.200';
    const SUBSTATUS_PROPOSAL_SUB300 = '50.300';


    // 52 - Substatus Proposal Saved
    const SUBSTATUS_SAVED_SUB100 = '52.100';
    const SUBSTATUS_SAVED_SUB200 = '52.200';

    /**
     * create array of all substatuses
     *
     * @return array
     */
    static public function substatuses()
    {
        $substatuses = array(
            self::SUBSTATUS_REJECTED_SUB100 => Mage::helper('qquoteadv')->__('SUBSTATUS_REJECTED_SUB100'),
            self::SUBSTATUS_CANCELLED_SUB100 => Mage::helper('qquoteadv')->__('SUBSTATUS_CANCELLED_SUB100'),
            self::SUBSTATUS_PROPOSAL_SUB100 => Mage::helper('qquoteadv')->__('SUBSTATUS_PROPOSAL_SUB100'),
            self::SUBSTATUS_PROPOSAL_SUB200 => Mage::helper('qquoteadv')->__('SUBSTATUS_PROPOSAL_SUB200'),
            self::SUBSTATUS_PROPOSAL_SUB300 => Mage::helper('qquoteadv')->__('SUBSTATUS_PROPOSAL_SUB300'),
            self::SUBSTATUS_SAVED_SUB100 => Mage::helper('qquoteadv')->__('SUBSTATUS_SAVED_SUB100'),
            self::SUBSTATUS_SAVED_SUB200 => Mage::helper('qquoteadv')->__('SUBSTATUS_SAVED_SUB200'),
        );

        if (count($substatuses) > 0) {
            return $substatuses;
        }

        return array();
    }

    // #################### From here no customization needed #######################

    /**
     * Create Option Array for massupdate action
     *
     * @param $statusArray
     * @param bool $substatus
     * @return array
     */
    static public function getChangeSubOptionArray($statusArray, $substatus = false)
    {
        $returnArray = array();
        $subStatusArray = self::_createSubStatusArray();

        if (is_array($subStatusArray) && count($subStatusArray) > 0) {
            foreach ($statusArray as $mainStatus) {
                $returnArray[] = $mainStatus;
                if (isset($subStatusArray[$mainStatus['value']])){
                    foreach ($subStatusArray[$mainStatus['value']] as $k => $v) {
                        $label = ($substatus) ? $mainStatus['label'] . ' ' . Mage::helper('qquoteadv')->__($v) : $mainStatus['label'];
                        $returnArray[] = array('value' => $mainStatus['value'] . self::EXPLODE_CHARACTER . $k, 'label' => $label);
                    }
                }
            }
        }

        return $returnArray;
    }

    /**
     * Create Option Array for Option listing
     *
     * @param $statusArray
     * @param bool $substatus
     * @return array
     */
    static public function getSubOptionArray($statusArray, $substatus = false)
    {
        $returnArray = array();
        $subStatusArray = self::_createSubStatusArray();

        if (is_array($subStatusArray) && count($subStatusArray) > 0) {
            foreach ($statusArray as $mainId => $mainLabel) {
                $returnArray[$mainId] = $mainLabel;
                if (isset($subStatusArray[$mainId])){
                    foreach ($subStatusArray[$mainId] as $k => $v) {
                        $label = ($substatus) ? $mainLabel . ' ' . $v : $mainLabel;
                        $returnArray[$mainId . self::EXPLODE_CHARACTER . $k] = $label;
                    }
                }
            }
        }

        return $returnArray;
    }


    /**
     * Create ordered array of substatusses
     *
     * @return array
     */
    static protected function _createSubStatusArray()
    {
        $returnArray = array();
        $subStatusArray = self::substatuses();

        foreach ($subStatusArray as $statusKey => $statusValue) {
            $subKeys = explode(self::EXPLODE_CHARACTER, $statusKey);
            if (is_array($subKeys) && count($subKeys) >= 2) {
                $returnArray[$subKeys[0]][$subKeys[1]] = $statusValue;
            }
        }

        return $returnArray;
    }

    /**
     * @param string $status
     * @return Varien Object
     */
    public function getStatus($status)
    {
        $subStatus = explode(self::EXPLODE_CHARACTER, $status);
        if (count($subStatus) > 1) {
            return (int)$subStatus[0];
        }

        return (int)$status;
    }

    /**
     * @param string $status
     * @return Varien Object
     */
    public function getSubstatus($status)
    {
        $subStatus = explode(self::EXPLODE_CHARACTER, $status);
        if (count($subStatus) > 1) {
            return $status;
        }

        return null;
    }

    /**
     * Check for substatus
     *
     * @param string $status
     * @return Varien Object
     */
    public function getStatuses($status)
    {
        $subStatus = explode(self::EXPLODE_CHARACTER, $status);
        if (count($subStatus) > 1) {
            $this->setStatus((int)$subStatus[0]);
            $this->setSubstatus($status);
        } else {
            $this->setStatus((int)$status);
            $this->setSubstatus();
        }

        return $this;
    }

    /**
     * Get parent status from substatus
     *
     * @param int | string $subStatus
     * @return boolean | array
     */
    static public function getParentStatus($subStatus)
    {
        $statusArray = explode(self::EXPLODE_CHARACTER, $subStatus);

        if (isset($statusArray[0]) && $statusArray[0] != null) {
            return (int)$statusArray[0];
        }
        return false;
    }

    /**
     * Get the current status based on a status and optionally a substatus
     *
     * @param $status
     * @param null $substatus
     * @return mixed
     */
    static public function getCurrentStatus($status, $substatus = null)
    {
        $gridOptionArray = Mage::getModel('qquoteadv/status')->getGridOptionArray(true);
        if ($substatus != null && self::getParentStatus($substatus) == $status) {
            return $gridOptionArray[$substatus];
        }
        if (isset($gridOptionArray[$status])) {
            return $gridOptionArray[$status];
        } else {
            // If status is not found
            // set a default status
            //return Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN;
            return Mage::helper('qquoteadv')->__('STATUS_PROPOSAL_BEGIN');
        }
    }

    /**
     * Prevent setting hold status again
     * when unholding quote
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @return $this
     */
    public function checkUnholdStatus(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote)
    {
        // Check for old status
        if ($this->getData('status') == Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED) {
            $this->setData('substatus', null);
            if (Mage::helper('qquoteadv')->isDate($quote->getData('proposal_sent'))) {
                $this->setData('status', Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL);
            } else {
                $this->setData('status', Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_BEGIN);
            }
        }

        return $this;
    }
}
