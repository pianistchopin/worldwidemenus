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
 * Class Ophirah_Qquoteadv_Adminhtml_Report_SalesrepController
 */
class Ophirah_Qquoteadv_Adminhtml_Report_SalesrepController extends Mage_Adminhtml_Controller_Report_Abstract
{
    /**
     * Add report/sales breadcrumbs
     *
     * @return Mage_Adminhtml_Report_SalesController
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(
            Mage::helper('qquoteadv')->__('Salesrep'),
            Mage::helper('qquoteadv')->__('Salesrep')
        );

        return $this;
    }

    /**
     * salesrepAction
     */
    public function salesrepAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Salesrep'))
            ->_title($this->__('Salesrep'));
        $this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
            ->_setActiveMenu('report/salesrep')
            ->_addBreadcrumb(
                Mage::helper('qquoteadv')->__('Salesrep Report'),
                Mage::helper('qquoteadv')->__('Salesrep Report')
            );

        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_salesrep_salesrep.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(
            array(
                $gridBlock,
                $filterFormBlock
            )
        );

        $this->renderLayout();
    }

    /**
     * @see Mage_Adminhtml_Controller_Action::_isAllowed()
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
