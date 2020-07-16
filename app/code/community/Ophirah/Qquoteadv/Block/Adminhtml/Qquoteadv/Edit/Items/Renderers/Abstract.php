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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Renderers_Abstract
 */
abstract class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Renderers_Abstract extends Mage_Adminhtml_Block_Template
{
    /**
     * Quote
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

    /**
     * The first quote item or it could be a array with an error.
     *
     * @var array| Mage_Catalog_Model_Product_Configuration_Item_Interface
     */
    protected $addProductResult;

    /**
     * Product Values
     *
     * @var array
     */
    protected $productValues;

    /**
     * Disable cache
     */
    protected function _construct()
    {
        $this->setCacheLifetime(null);
        parent::_construct();
    }

    /**
     * Get the Magento quote item.
     *
     * @return false|Mage_Sales_Model_Quote_Item
     */
    public function getQuoteItem()
    {
        $this->addProductResult = $this->getItem()->getQuoteItem();
        if ($this->hasErrors()) {
            return false;
        }

        return $this->addProductResult;
    }

    /**
     * Checks if an error is created when adding a product to the Magento quote.
     *
     * @return bool
     */
    public function hasErrors()
    {
        if (!$this->addProductResult instanceof Mage_Catalog_Model_Product_Configuration_Item_Interface) {
            if (!empty($this->addProductResult)) {
                Mage::getSingleton('adminhtml/session')->addError($this->addProductResult);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get possible errors
     *
     * @return bool|string
     */
    public function getError(){
        if($this->hasErrors()){
            return $this->addProductResult;
        }else{
            return false;
        }
    }

    /**
     * Get the Magento quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Create a Magento quote and add the product.
     * $this->_addProductResult is the first quote item or it could be a array with an error.
     *
     * @param $product
     * @param $attribute
     */
    public function setQuote($product, $attribute)
    {
        $buyRequest = new Varien_Object($attribute);
        $quote = Mage::getModel('sales/quote');

        try{
            $this->addProductResult = $quote->addProductAdvanced($product, $buyRequest);
        }catch(Exception $e){
            $errors = explode("\n", $e->getMessage());
            $errors = array_unique($errors);
            $this->addProductResult = implode("\n", $errors);
        }

        $this->quote = $quote;
    }

    /**
     * Get product values
     *
     * @param $product
     * @return Varien_Object
     */
    public function getProductValues($product)
    {
        if (!$this->hasErrors()) {
            return new Varien_Object(
                $product->getTypeInstance(true)->getOrderOptions($product)
            );
        } else {
            return false;
        }
    }

    /**
     * Get the product quantity.
     *
     * @return int
     */
    public function getQty()
    {
        if ($this->getProduct()->getQty()) {
            $qty = $this->getProduct()->getQty();
        } else {
            $qty = 1;
        }
        return $qty;
    }

    /**
     * Get the image of the set product.
     *
     * @param int $size
     * @return string
     */
    public function getImage($size = 180)
    {
        return $this->getProductImage($size, $this->getProduct());
    }

    /**
     * Get a image of a product.
     *
     * @param int $size
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getProductImage($size = 180, Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $productDataHelper = Mage::helper('qquoteadv/catalog_product_data');
            $childProduct = $productDataHelper->getConfChildProduct($product->getId(), $product);
            $imageProduct = $productDataHelper->getImageProduct(
                $product,
                $childProduct
            );
        } else {
            $imageProduct = $product;
        }

        return Mage::helper('catalog/image')->init($imageProduct, 'thumbnail')->resize($size);
    }

    /**
     * Get the name of a product.
     *
     * @param $product
     * @return string
     */
    public function getProductName(Mage_Catalog_Model_Product $product)
    {
        return Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name');
    }

    /**
     * Get the name of the set product.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getProductName($this->getProduct());
    }

    /**
     * Get the price of this product.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->getPriceHtml($this->getProduct());
    }

    /**
     * Get a product price
     *
     * @param Mage_Catalog_Model_Product $product
     * @return String
     */
    public function getProductPrice(Mage_Catalog_Model_Product $product)
    {
        return Mage::helper('core')->currency($product->getPrice(), true, false);
    }

    /**
     * Get the product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $quoteItem = $this->getItem()->getQuoteItem();
        if ($quoteItem instanceof Mage_Sales_Model_Quote_Item) {
            $product = $quoteItem->getProduct();
        }

        if (!isset($product) || $product == null) {
            $product = Mage::getModel('catalog/product');
        }

        return $product;
    }

    /**
     * Check if the product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        if ($this->getOptions() && count($this->getOptions())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the product options
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->productValues instanceof Varien_Object) {
            return $this->productValues->getOptions();
        } else {
            return array();
        }
    }

    /**
     * Init the Magento quote with one or more items.
     *
     * @return void
     */
    public function _beforeToHtml()
    {
        $this->getQuoteItem();
        $this->productValues = $this->getProductValues($this->getProduct());
        parent::_beforeToHtml();
    }

    /**
     * escapeHtml replacement
     *
     * @param mixed $data
     * @param null $allowedTags
     * @param bool $allowInnerQuotes
     * @return mixed
     */
    public function escapeHtml($data, $allowedTags = null, $allowInnerQuotes = false)
    {
        /** @var \Ophirah_Qquoteadv_Helper_Data $helper */
        $helper = Mage::helper('qquoteadv');
        return $helper->escapeHtml($data, $allowedTags, $allowInnerQuotes);
    }

    /**
     * Get the allowed HTML tags for usage in the escapeHtml function
     *
     * @return array
     */
    public function getAllowedHtmlTags()
    {
        $helper = Mage::helper('qquoteadv');
        return $helper->getAllowedHtmlTags();
    }
}
