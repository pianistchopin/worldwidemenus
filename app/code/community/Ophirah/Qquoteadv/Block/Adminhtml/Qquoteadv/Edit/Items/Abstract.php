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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Abstract
 */
class  Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Abstract extends Mage_Adminhtml_Block_Template
{
    /**
     * Renderers with render type key
     * block    => the block name
     * template => the template file
     * renderer => the block object
     *
     * @var array
     */
    protected $_itemRenders = array();

    /**
     * Renderers for other column with column name key
     * block    => the block name
     * template => the template file
     * renderer => the block object
     * sort     => sort order
     *
     * @var array
     */
    protected $_columnRenders = array();

    /**
     * Flag - if it is set method canEditQty will return value of it
     *
     * @var boolean | null
     */
    protected $_canEditQty = null;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_magentoQuote;

    /**
     * @var Ophirah_Qquoteadv_Model_Mysql4_Qqadvproduct_Collection
     */
    protected $_items;


    /**
     * Add item renderer
     *
     * @param string $type
     * @param string $block
     * @param string $template
     * @return Mage_Adminhtml_Block_Sales_Items_Abstract
     */
    public function addItemRender($type, $block, $template)
    {
        $this->_itemRenders[$type] = array(
            'block' => $block,
            'template' => $template,
            'renderer' => null
        );
        return $this;
    }

    /**
     * Add column renderer
     *
     * @param $column
     * @param $block
     * @param $template
     * @param int $sort
     * @return $this
     */
    public function addColumnRender($column, $block, $template, $sort = 99999)
    {
        if (!is_null($sort)) {
            $column = $sort;
        }
        $this->_columnRenders[$column] = array(
            'block' => $block,
            'template' => $template,
            'renderer' => null
        );
        $this->_columnRenders;
        return $this;
    }

    /**
     * Retrieve item renderer block
     *
     * @param string $type
     * @return Mage_Core_Block_Abstract
     */
    public function getItemRenderer($type)
    {
        if (!isset($this->_itemRenders[$type])) {
            $type = 'default';
        }
        if (is_null($this->_itemRenders[$type]['renderer'])) {
            $this->_itemRenders[$type]['renderer'] = $this->getLayout()
                ->createBlock($this->_itemRenders[$type]['block'])
                ->setTemplate($this->_itemRenders[$type]['template']);
        }
        return $this->_itemRenders[$type]['renderer'];
    }

    /**
     * Retrieve column renderer block
     *
     * @param string $column
     * @return Mage_Core_Block_Abstract
     */
    public function getColumnRenderer($column)
    {
        if (!isset($this->_columnRenders[$column])) {
            return false;
        }
        if (is_null($this->_columnRenders[$column]['renderer'])) {
            try {
                $block = $this->getLayout()
                    ->createBlock($this->_columnRenders[$column]['block']);
                if (!$block instanceof Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Interface) {
                    $this->_columnRenders[$column]['renderer'] = false;
                    Mage::throwException(
                        Mage::helper('qquoteadv')->__(
                            'Could not create block "' . $this->_columnRenders[$column]['block'] .
                            '"; the column block does not exists (or configured poorly in qquoteadv.xml) or' .
                            ' the block does not implement the interface Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Interface')
                    );

                }
                $block->setTemplate($this->_columnRenders[$column]['template'])->setRenderedBlock($this);

                if ($block->isHidden()) {
                    return false;
                }
                $this->_columnRenders[$column]['renderer'] = $block;
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }
        }

        return $this->_columnRenders[$column]['renderer'];
    }

    /**
     * Retrieve rendered item html content
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getItemHtml(Varien_Object $item)
    {
        /** @var Mage_Catalog_Model_Product $type */
        $type = $item->getProduct()->getTypeId();

        return $this->getItemRenderer($type)
            ->setItem($item)
            ->toHtml();
    }

    /**
     * Retrieve rendered item extra info html content
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getItemExtraInfoHtml(Varien_Object $item)
    {
        $extraInfoBlock = $this->getChild('order_item_extra_info');
        if ($extraInfoBlock) {
            return $extraInfoBlock
                ->setItem($item)
                ->toHtml();
        }
        return '';
    }

    /**
     * Retrieve rendered column html content
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $item
     * @param string $column the column key
     * @param string $field the custom item field
     * @return string
     */
    public function getColumnHtml(Ophirah_Qquoteadv_Model_Qqadvproduct $item, $column, $field = null)
    {
        if ($item->getProduct()) {
            $block = $this->getColumnRenderer($column);
        }

        if (isset($block) && $block instanceof Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Interface) {
            $block->setItem($item);
            if (!is_null($field)) {
                $block->setField($field);
            }
            return $block->toHtml();
        }
        return '';
    }

    /**
     * Get all columns for a product
     * @param $item
     * @return string
     */
    public function getAllColumnsHtml($item)
    {
        $html = '';
        foreach ($this->getSortedColumnRenders() as $columnType => $renderer) {
            $html .= $this->getColumnHtml($item, $columnType);
        }
        return $html;
    }

    /**
     * Get the sorted column renders
     *
     * @return array
     */
    public function getSortedColumnRenders()
    {
        $renders = $this->_columnRenders;
        ksort($renders);

        $this->setLastColumnCss(end($renders));

        return $renders;
    }

    /**
     * Get the column header info
     *
     * @return array
     */
    public function getColumnHeaderInfo()
    {
        $columns = array();
        foreach ($this->getSortedColumnRenders() as $columnType => $renderer) {
            $column = $this->getColumnRenderer($columnType);
            if ($column) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Sets the last column flag. This is used for the css.
     *
     * @param $lastColumn
     */
    public function setLastColumnFlag($lastColumn)
    {
        if (isset($lastColumn['renderer']) && $lastColumn instanceof Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Interface) {
            $lastColumnRenderer = $lastColumn['renderer'];
            $lastColumnRenderer->setLast(true);
        }
    }

    /**
     * Retrieve available quote
     *
     * @return Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function getQuote()
    {
        if ($this->hasQuote()) {
            return $this->getData('quote');
        }
        if (Mage::registry('current_quote')) {
            return Mage::registry('current_quote');
        }
        if (Mage::registry('quote')) {
            return Mage::registry('order');
        }
        if ($this->getItem()->getQuote()) {
            return $this->getItem()->getQuote();
        }

        Mage::throwException(Mage::helper('qquoteadv')->__('Cannot get quote instance'));
        return Mage::getModel('qquoteadv/qqadvcustomer');
    }

    /**
     * Retrieve price data object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getPriceDataObject()
    {
        $obj = $this->getData('price_data_object');
        if (is_null($obj)) {
            return $this->getQuote();
        }
        return $obj;
    }

    /**
     * Retrieve price attribute html content
     *
     * @param string $code
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPriceAttribute($code, $strong = false, $separator = '<br />')
    {
        if ($code == 'tax_amount' && $this->getQuote()->getRowTaxDisplayPrecision()) {
            return $this->displayRoundedPrices(
                $this->getPriceDataObject()->getData('base_' . $code),
                $this->getPriceDataObject()->getData($code),
                $this->getQuote()->getRowTaxDisplayPrecision(),
                $strong,
                $separator
            );
        } else {
            return $this->displayPrices(
                $this->getPriceDataObject()->getData('base_' . $code),
                $this->getPriceDataObject()->getData($code),
                $strong,
                $separator
            );
        }
    }

    /**
     * Retrieve price formated html content
     *
     * @param float $basePrice
     * @param float $price
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPrices($basePrice, $price, $strong = false, $separator = '<br />')
    {
        return $this->displayRoundedPrices($basePrice, $price, 2, $strong, $separator);
    }

    /**
     * Display base and regular prices with specified rounding precision
     *
     * @param   float $basePrice
     * @param   float $price
     * @param   int $precision
     * @param   bool $strong
     * @param   string $separator
     * @return  string
     */
    public function displayRoundedPrices($basePrice, $price, $precision = 2, $strong = false, $separator = '<br />')
    {
        if ($this->getQuote()->isCurrencyDifferent()) {
            $res = '';
            $res .= $this->getQuote()->formatBasePricePrecision($basePrice, $precision);
            $res .= $separator;
            $res .= $this->getQuote()->formatPricePrecision($price, $precision, true);
        } else {
            $res = $this->getQuote()->formatPricePrecision($price, $precision);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        }
        return $res;
    }

    /**
     * Retrieve include tax html formated content
     *
     * @param Varien_Object $item
     * @return string
     */
    public function displayPriceInclTax(Varien_Object $item)
    {
        $qty = ($item->getQtyOrdered() ? $item->getQtyOrdered() : ($item->getQty() ? $item->getQty() : 1));
        $baseTax = ($item->getTaxBeforeDiscount() ? $item->getTaxBeforeDiscount() : ($item->getTaxAmount() ? $item->getTaxAmount() : 0));
        $tax = ($item->getBaseTaxBeforeDiscount() ? $item->getBaseTaxBeforeDiscount() : ($item->getBaseTaxAmount() ? $item->getBaseTaxAmount() : 0));

        $basePriceTax = 0;
        $priceTax = 0;

        if (floatval($qty)) {
            $basePriceTax = $item->getBasePrice() + $baseTax / $qty;
            $priceTax = $item->getPrice() + $tax / $qty;
        }

        return $this->displayPrices(
            $this->getQuote()->getStore()->roundPrice($basePriceTax),
            $this->getQuote()->getStore()->roundPrice($priceTax)
        );
    }

    /**
     * Retrieve subtotal price include tax html formated content
     *
     * @param Varien_Object $item
     * @return string
     */
    public function displaySubtotalInclTax($item)
    {
        $baseTax = ($item->getTaxBeforeDiscount() ? $item->getTaxBeforeDiscount() : ($item->getTaxAmount() ? $item->getTaxAmount() : 0));
        $tax = ($item->getBaseTaxBeforeDiscount() ? $item->getBaseTaxBeforeDiscount() : ($item->getBaseTaxAmount() ? $item->getBaseTaxAmount() : 0));

        return $this->displayPrices(
            $item->getBaseRowTotal() + $baseTax,
            $item->getRowTotal() + $tax
        );
    }

    /**
     * Retrieve tax calculation html content
     *
     * @param Varien_Object $item
     * @return string
     */
    public function displayTaxCalculation(Varien_Object $item)
    {
        if ($item->getTaxPercent() && $item->getTaxString() == '') {
            $percents = array($item->getTaxPercent());
        } else if ($item->getTaxString()) {
            $percents = explode(Mage_Tax_Model_Config::CALCULATION_STRING_SEPARATOR, $item->getTaxString());
        } else {
            return '0%';
        }

        foreach ($percents as &$percent) {
            $percent = sprintf('%.2f%%', $percent);
        }
        return implode(' + ', $percents);
    }

    /**
     * Retrieve tax with persent html content
     *
     * @param Varien_Object $item
     * @return string
     */
    public function displayTaxPercent(Varien_Object $item)
    {
        if ($item->getTaxPercent()) {
            return sprintf('%s%%', $item->getTaxPercent() + 0);
        } else {
            return '0%';
        }
    }

    /**
     * Format the price
     *
     * @param $price
     * @return mixed
     */
    public function formatPrice($price)
    {
        return $this->getQuote()->formatPrice($price);
    }

    /**
     * Create a Magento quote and add the product.
     * $this->_addProductResult is the first quote item or it could be a array with an error.
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $item
     * @return Mage_Sales_Model_Quote_Item $this
     * @deprecated since version 6.1.2
     */
    public function addToMageQuote($item)
    {
        $product = $item->getProduct();

        if ($product instanceof Mage_Catalog_Model_Product) {
            /** @var Ophirah_Qquoteadv_Helper_Data $helper */
            $helper = Mage::helper('qquoteadv');

            try {
                /** @var Mage_Sales_Model_Quote $quote */
                $quote = $helper->getQuoteItem($product, $item->getAttribute(), $this->getQuote(), $item);
                $items = $quote->getAllVisibleItems();
                foreach ($items as $item) {
                    return $item;
                }
            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }
        }

        return Mage::getModel('sales/quote_item');
    }

    /**
     * Create a Magento quote and add the products
     *
     * @param Ophirah_Qquoteadv_Model_Mysql4_Qqadvproduct_Collection $items
     */
    public function multiAddToMageQuote($items)
    {
        /** @var Ophirah_Qquoteadv_Helper_Data $helper */
        $helper = Mage::helper('qquoteadv');

        try {
            $helper->getQuoteItem(null, null, $this->getQuote(), $items);
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
        }
    }

    /**
     * Return Mage_Sales_Model_Quote
     */
    public function getMagentoQuote()
    {
        if (!$this->_magentoQuote instanceof Mage_Sales_Model_Quote) {
            $this->_magentoQuote = Mage::getModel('sales/quote');
        }
        return $this->_magentoQuote;
    }

    /**
     * Add Magento Quote with products to this block.
     */
    public function initMagentoQuote()
    {
        /** @var Ophirah_Qquoteadv_Model_Mysql4_Qqadvproduct_Collection $item */
        $items = $this->getItems();
        $this->multiAddToMageQuote($items);

        return $this;
    }

    /**
     * Get Product information from qquote_product table
     *
     * @return Ophirah_Qquoteadv_Model_Mysql4_Qqadvproduct_Collection
     */
    public function getItems()
    {
        if (!$this->_items instanceof Ophirah_Qquoteadv_Model_Mysql4_Qqadvproduct_Collection) {
            $this->_items = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
                ->addFieldToFilter('quote_id', $this->getQuoteId());
            $this->loadProduct($this->_items);
            $this->initMagentoQuote();
            $this->iniRequestedProductData();
            $this->initAdditionalTotals();
        }
        return $this->_items;
    }

    /**
     * Loads a product
     *
     * @param $items
     * @return $this
     */
    public function loadProduct($items)
    {
        foreach ($items as $item) {
            $buyRequest = new Varien_Object();
            $serializedBuyRequest = $item->getAttribute();

            if (isset($serializedBuyRequest)) {
                $buyRequest->addData(unserialize($serializedBuyRequest));
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')
                ->setStoreId($item->getStoreId())
                ->load($item->getProductId());
            $product->processBuyRequest($buyRequest);

            $item->setProduct($product);
        }
        return $this;
    }

    /**
     * Checks if the quote is read only
     *
     * @param string $type
     * @return bool
     */
    public function isQuoteReadOnly($type = "")
    {
        $status = $this->getQuote()->getStatus();
        $readyOnly = intval($status) >= 50;
        $html = "";
        switch ($type) {
            case 'input':
                if ($readyOnly) {
                    $html = 'readonly';
                }
                break;
            case 'radio':
                if ($readyOnly) {
                    $html = 'disabled';
                }
                break;
            case 'onclick':
                if ($readyOnly) {
                    $html = 'if (confirm("This quote is already send to the customer, would you like to edit it?")) {edit();}';
                }
                break;
            default:
                $html = $readyOnly;
                break;
        }
        return $html;
    }

    /**
     * Sets the requested product data; (tier qtys, tier cost prices etc.)
     * @return $this
     */
    public function iniRequestedProductData()
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            $item->setRequestedProductData($this->getRequestedProductData($item));
        }
        return $this;
    }

    /**
     * Get Product information from qquote_request_item table
     *
     * @param $item
     * @return Ophirah_Qquoteadv_Model_Mysql4_Requestitem_Collection
     */
    public function getRequestedProductData($item)
    {
        $requestedProductData =
            Mage::getModel('qquoteadv/requestitem')->getCollection()
                ->setQuote($this->getQuote())
                ->addFieldToFilter('quoteadv_product_id', $item->getId());
        $requestedProductData
            ->getSelect()
            ->order('request_qty asc');
        return $requestedProductData;
    }

    /**
     * Get the total singleton model
     * @return Ophirah_Qquoteadv_Model_Quotetotal
     */
    public function getQuoteTotal()
    {
        $quoteTotal = Mage::getSingleton('qquoteadv/quotetotal');
        return $quoteTotal;
    }

    /**
     * Get the store of the totals.
     */
    public function getStore()
    {
        return Mage::app()->getStore($this->getQuoteTotal()->getQuoteStore());
    }

    /**
     * Set the cost price and original price column totals on the total model
     *
     * @return void
     */
    protected function initAdditionalTotals()
    {
        $originalPriceSum = 0;
        $costPriceSum = 0;
        $items = $this->getItems();
        /** @var Ophirah_Qquoteadv_Model_Qqadvproduct $item */
        foreach ($items as $item) {
            $requestCollection = $item->getRequestedProductData();
            /** @var Ophirah_Qquoteadv_Model_Requestitem $requestItem */
            foreach ($requestCollection as $requestItem) {
                if ($item->getQty() == $requestItem->getQty()) {
                    $originalPriceSum += ($requestItem->getOriginalCurPrice() * $requestItem->getQty());
                    $costPriceSum += ($this->getCostPrice($item, $requestItem) * $requestItem->getQty());
                }
            }
        }

        $this->getQuoteTotal()->setTotalOrgcost($originalPriceSum);
        $this->getQuoteTotal()->setTotalCost($costPriceSum);
    }

    /**
     * Get cost price for an item
     *
     * @param $item
     * @param $requestItem
     * @return int
     */
    protected function getCostPrice($item, $requestItem)
    {
        $item->setRequestItem($requestItem);
        $cost = $item->getQuoteItemCost($item->getProduct(), $item->getId());
        // Adding to quotetotals
        if ($cost && $requestItem->getCostPrice()) {
            $costPrice = $cost;
            return $costPrice;
        } elseif ($cost && !$requestItem->getCostPrice()) {
            $costPrice = $cost * $this->getParentBlock()->getRate();
            return $costPrice;
        } else {
            $costPrice = 0;
            return $costPrice;
        }
    }
}
