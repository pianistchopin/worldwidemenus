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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Abstract
 */
abstract class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Abstract
    extends Mage_Adminhtml_Block_Template
    implements Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Columns_Interface
{

    /**
     * The qquoteadv product
     *
     * @var Ophirah_Qquoteadv_Model_Qqadvproduct
     */
    protected $item;

    /**
     * The qquoteadv quote
     *
     * @var Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    protected $quote;

    /**
     * The Magento product
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    /**
     * Get the row id of the column.
     * Normally label+productId.
     *
     * @return mixed
     */
    public function getRowId()
    {
        return $this->getColumnLabel().'_'.$this->getItem()->getId();
    }

    /**
     * The Magento Product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * The Cart2Quote Product
     *
     * @return Ophirah_Qquoteadv_Model_Qqadvproduct
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * The Cart2Quote Quote
     *
     * @return Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function getQuote()
    {
        return $this->getRenderedBlock()->getQuote();
    }

    /**
     * The Magento Product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return void
     */
    public function setProduct(Mage_Catalog_Model_Product $product){
        $this->product = $product;
    }

    /**
     * Sets a product
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $item
     * @return void
     */
    public function setItem(Ophirah_Qquoteadv_Model_Qqadvproduct $item){
        $this->item = $item;
        /** @noinspection PhpParamsInspection */
        $this->setProduct(
            Mage::getModel('catalog/product')->load($item->getProductId())
        );
    }

    /**
     * Sets a quote
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvcustomer $quote
     * @return void
     */
    public function setQuote(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote){
        $this->quote = $quote;
    }

    /**
     * Get the total singleton model
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getQuoteTotal(){
        return Mage::getSingleton('qquoteadv/quotetotal');
    }

    /**
     * Get the store of the totals.
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore(){
        return Mage::app()->getStore($this->getQuoteTotal()->getQuoteStore());
    }

    /**
     * Get tier qty
     *
     * @return int
     */
    public function getTierQty(){
        return $this->getItem()->getRequestedProductData();
    }

    /**
     * Get the multi currency rate
     *
     * @return mixed
     */
    public function getRate(){
        if(!$this->rate){
            $this->rate = $this->getQuote()->getData('base_to_quote_rate');
            if(!$this->rate){
                $this->rate = $this->getQuote()->getBaseToQuoteRate();
            }
        }

        return $this->rate;
    }

    /**
     * Get the css classes
     *
     * @return string
     */
    public function getCssHeaderClasses()
    {
        return $this->getLastCss();
    }

    /**
     * Returns the css class for the table footer column
     *
     * @return String
     */
    public function getColumnTotalCssClass()
    {
        return $this->getLastCss();
    }

    /**
     * Get the css classes for the content
     *
     * @return string
     */
    public function getCssContentClasses(){
        return $this->getLastCss();
    }

    /**
     * Get the class last if this column is the last column
     *
     * @return string
     */
    public function getLastCss(){
        if($this->hasData('last')){
            return 'last';
        }else{
            return '';
        }
    }
}