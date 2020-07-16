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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Report_Salesrep_Salesrep_Grid
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Report_Salesrep_Salesrep_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    /**
     * Count totals
     *
     * @var boolean
     */
    protected $_countTotals = false;

    /**
     * Column for grid to be grouped by
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * @var array
     */
    protected $_profit = array();

    /**
     * @var array
     */
    protected $_profitMargin = array();

    /**
     * @var array
     */
    protected $_quoteAmount = array();

    /**
     * @var array
     */
    protected $_grandTotalAmount = array();

    /**
     * @var null|object
     */
    protected $_collection = null;

    /**
     * Grid resource collection name
     *
     * @var string
     */
    protected $_resourceCollectionName = 'qquoteadv/report_salesrep_salesrep_collection';

    /**
     * Init grid parameters
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
        $this->_prepareCollection();
    }

    /**
     * Prepair collumn function
     *
     * @return mixed
     */
    protected function _prepareColumns()
    {
        $this->calculateProfit();
        $this->calculateQuoteValue();

        $_collection = Mage::getModel('admin/user')
            ->getCollection()
            ->setOrder('firstname', 'asc');

        $admins = array();
        foreach ($_collection as $model) {
            $name = $model->getFirstname() . ' ' . $model->getLastname();
            $admins[$model->getUserId()] = $name;
        }

        $this->addColumn(
            'salesrep_name',
            array(
                'header'   => Mage::helper('qquoteadv')->__('Salesrep Name'),
                'sortable' => true,
                'index'    => 'user_id',
                'align'    => 'right',
                'type'     => 'options',
                'options'  => $admins
            )
        );

        $this->addColumn(
            'number_of_quotes',
            array(
                'header'   => Mage::helper('qquoteadv')->__('Number of Quotes'),
                'index'    => 'user_id',
                'type'     => 'options',
                'options'  => $this->_quoteAmount,
                'align'    => 'right',
                'sortable' => true
            )
        );

        $this->addColumn(
            'total_value_of_quotes',
            array(
                'header'   => Mage::helper('qquoteadv')->__('Total value of quotes'),
                'index'    => 'user_id',
                'type'     => 'options',
                'options'  => $this->_grandTotalAmount,
                'align'    => 'right',
                'sortable' => true,
            )
        );

        $this->addColumn(
            'total_profit_value',
            array(
                'header'  => Mage::helper('qquoteadv')->__('Total profit value'),
                'index'   => 'user_id',
                'type'    => 'options',
                'align'   => 'right',
                'options' => $this->_profit
            )
        );

        $this->addColumn(
            'profit_margin',
            array(
                'header'  => Mage::helper('qquoteadv')->__('Profit margin %'),
                'index'   => 'user_id',
                'type'    => 'options',
                'align'   => 'right',
                'options' => $this->_profitMargin
            )
        );

        $this->addExportType('*/quote_export/exportSalesrepCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/quote_export/exportSalesrepExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Prepare collection function
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $resource = Mage::getModel('core/resource');

        $newestCollection = Mage::getModel('qquoteadv/grouppriceinfo')->getCollection();
        $newestCollection->getSelect()
            ->columns(
                array(
                    'MAX(group_price_id) as newest_group_price_id'
                )
            )
            ->group(
                array(
                    'product_id',
                    'quoteadv_product_id',
                    'quote_id',
                    'user_id',
                    'customer_group_id',
                    'qty'
                )
            );
        $newestGroupPriceIds = $newestCollection->getColumnValues('newest_group_price_id');

        $collection = Mage::getModel('qquoteadv/grouppriceinfo')->getCollection();
        $collection->getSelect()
            ->columns(
                array(
                    'concat(main_table.group_price_id, qp.quote_id, qp.quote_id, sfo.entity_id, sfoi.item_id) as group_price_id'
                )
            )
            ->joinLeft(
                array('qp' => $resource->getTableName('quoteadv_product')),
                'main_table.quoteadv_product_id = qp.id'
            )->joinLeft(
                array('qc' => $resource->getTableName('quoteadv_customer')),
                'qp.quote_id = qc.quote_id'
            )->joinLeft(
                array('sfo' => $resource->getTableName('sales_flat_order')),
                'qc.quote_id = sfo.c2q_internal_quote_id'
            )
            ->joinLeft(
                array('sfoi' => $resource->getTableName('sales_flat_order_item')),
                'main_table.product_id = sfoi.product_id
                    AND main_table.qty = FLOOR(sfoi.qty_ordered)
                    AND sfo.entity_id = sfoi.order_id',
                array('sfoi.base_row_total')
            )
            ->where('sfo.entity_id IS NOT NULL')
            ->where('main_table.group_price_id IN (?)', $newestGroupPriceIds);

        $this->setCollection($collection);
        return $this;
    }

    /**
     * @return $this|\Mage_Adminhtml_Block_Report_Grid_Abstract
     */
    protected function _prepareGrid()
    {
        $this->_prepareColumns();
        $this->_prepareMassactionBlock();
        $this->_prepareCollection();

        //add group by in the _prepareGrid to avoid duplicated rows but allow the specila index=>user_id structure
        $this->getCollection()->getSelect()->group('main_table.user_id');

        return $this;
    }

    /**
     * Don't use orders in collection
     *
     * @param Mage_Reports_Model_Resource_Report_Collection_Abstract $collection
     * @param Varien_Object $filterData
     * @return Mage_Adminhtml_Block_Report_Grid_Abstract
     */
    protected function _addOrderStatusFilter($collection, $filterData)
    {
        return $this;
    }

    /**
     * Calculate the profit
     */
    public function calculateProfit()
    {
        $subtotalArray = array();
        $groupPriceArray = array();
        $existArray = array();
        $quoteIds = array();

        foreach ($this->_collection as $data) {
            if (!$this->checkExist($existArray, $data)) {
                if (!isset($this->_profit[$data->getUserId()])) {
                    $this->_profit[$data->getUserId()] = 0;
                }

                if (!isset($subtotalArray[$data->getUserId()])) {
                    $subtotalArray[$data->getUserId()] = 0;
                }

                if (!isset($groupPriceArray[$data->getUserId()])) {
                    $groupPriceArray[$data->getUserId()] = 0;
                }

                $productId = $data->getProductId();

                //update exists array
                $existArray[$data->getGroupPriceId()] = array(
                    "product_id" => $productId,
                    "quote_id"   => $data->getQuoteId()
                );

                //load product and get info
                $product = Mage::getModel('catalog/product')->load($productId);
                $price = $product->getPrice();
                $qty = $data->getQty();
                $groupPrice = $data->getGroupPrice();
                $productSubtotal = $price * $qty;
                $profitData = $productSubtotal - $groupPrice;

                //update values
                $this->_profit[$data->getUserId()] += $profitData;
                $subtotalArray[$data->getUserId()] += $productSubtotal;
                $groupPriceArray[$data->getUserId()] += $groupPrice;
            }

            // Calculate the amount of Quotes
            if (!in_array($data->getQuoteId(), $quoteIds)) {
                if (!isset($this->_quoteAmount[$data->getUserId()])) {
                    $this->_quoteAmount[$data->getUserId()] = 0;
                }

                $this->_quoteAmount[$data->getUserId()]++;
                $quoteIds[] = $data->getQuoteId();
            }
        }

        foreach ($groupPriceArray as $key => $price) {
            $margin = round(
                (($subtotalArray[$key] - $price) / $subtotalArray[$key]) * 100,
                0
            );

            $formattedMargin = sprintf("%s%%", $margin);
            $this->_profitMargin[$key] = $formattedMargin;
        }

        $array = $this->formatPrices($this->_profit);
        $this->_profit = $array;
    }

    /**
     * Calculate value of quotes
     */
    public function calculateQuoteValue()
    {
        $this->_collection->getSelect()
            ->joinInner(
                'quoteadv_quote_address',
                'main_table.quote_id = quoteadv_quote_address.quote_id 
                and quoteadv_quote_address.address_type = "billing"',
                array('quoteadv_quote_address.grand_total')
            )
            ->group('main_table.quote_id');

        foreach ($this->_collection as $data) {
            if (!isset($this->_grandTotalAmount[$data->getUserId()])) {
                $this->_grandTotalAmount[$data->getUserId()] = 0;
            }

            $this->_grandTotalAmount[$data->getUserId()] += $data->getGrandTotal();
        }

        $array = $this->formatPrices($this->_grandTotalAmount);
        $this->_grandTotalAmount = $array;
    }

    /**
     * Check if value already exist
     *
     * @param $existArray
     * @param $data
     * @return bool
     */
    public function checkExist($existArray, $data)
    {
        $return = false;
        if ($existArray) {
            foreach ($existArray as $previousData) {
                if ($previousData['quote_id'] == $data->getQuoteId()
                    && $previousData['product_id'] == $data->getProductId()
                ) {
                    $return = true;
                }
            }
        }

        return $return;
    }

    /**
     * @return bool
     */
    public function getCountTotals()
    {
        return !$this->_countTotals;
    }

    /**
     * Format the price
     *
     * @param $array
     * @return mixed
     */
    public function formatPrices($array)
    {
        foreach ($array as $key => $data) {
            $array[$key] = strip_tags(Mage::app()->getStore()->formatPrice($data, false));
        }

        return $array;
    }
}
