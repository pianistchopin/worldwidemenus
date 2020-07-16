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
 * Class Ophirah_Qquoteadv_Model_Resource_Report_Salesrep_Salesrep_Collection
 */
class Ophirah_Qquoteadv_Model_Mysql4_Report_Salesrep_Salesrep_Collection extends Mage_Reports_Model_Resource_Report_Collection_Abstract
{
    /**
     * Ophirah_Qquoteadv_Model_Mysql4_Report_Salesrep_Salesrep_Collection constructor.
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')
            ->init(Mage_Reports_Model_Resource_Report_Product_Viewed::AGGREGATION_DAILY);
        $this->setConnection($this->getResource()->getReadConnection());

        // overwrite default behaviour
        $this->_applyFilters = false;
    }
}