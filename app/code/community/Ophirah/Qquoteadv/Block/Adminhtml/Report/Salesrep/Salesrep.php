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

class Ophirah_Qquoteadv_Block_Adminhtml_Report_Salesrep_Salesrep extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Ophirah_Qquoteadv_Block_Adminhtml_Report_Salesrep_Salesrep constructor.
     */
    public function __construct()
    {
        $this->_blockGroup = 'qquoteadv';
        $this->_controller = 'adminhtml_report_salesrep_salesrep';
        $this->_headerText = Mage::helper('qquoteadv')->__('Salesrep Report');
        parent::__construct();

        $this->setTemplate('report/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton(
            'filter_form_submit',
            array(
                'label' => Mage::helper('reports')->__('Show Report'),
                'onclick' => 'filterFormSubmit()'
            )
        );
    }

    /**
     * Return the salesrep filter url
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/salesrep', array('_current' => true));
    }
}
