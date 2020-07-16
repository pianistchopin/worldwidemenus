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
 * Class Ophirah_Qquoteadv_Block_Renderers_Abstract
 */
abstract class Ophirah_Qquoteadv_Block_Renderers_Abstract extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * The first quote item or it could be a array with an error.
     * @var array| Mage_Catalog_Model_Product_Configuration_Item_Interface
     */
    protected $_addProductResult;

    protected function _construct()
    {
        // disable cache
        $this->setCacheLifetime(null);
        parent::_construct();

        //only run if SCP is enabled
        if (Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')) {
            $this->setScp(true);
        }
    }

    /**
     * @var array
     */
    protected $_productValues;

    /**
     * Get the first Magento quote item.
     * @param $product
     * @param $attribute
     * @return false|Mage_Catalog_Model_Product_Configuration_Item_Interface
     */
    public function getQuoteItem($product, $attribute)
    {
        $this->setQuote($product, $attribute);

        if ($this->hasErrors()) {
            return false;
        }

        return $this->_addProductResult;
    }

    /**
     * Checks if an error is created when adding a product to the Magento quote.
     * @return bool
     */
    public function hasErrors()
    {
        if (!$this->_addProductResult instanceof Mage_Catalog_Model_Product_Configuration_Item_Interface) {
            Mage::getSingleton('adminhtml/session')->addError($this->_addProductResult);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool|string
     */
    public function getError(){
        if($this->hasErrors()){
            return $this->_addProductResult;
        }else{
            return false;
        }
    }

    /**
     * Get the Magento quote
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Create a Magento quote and add the product.
     * $this->_addProductResult is the first quote item or it could be a array with an error.
     * @param $product
     * @param $attribute
     */
    public function setQuote($product, $attribute)
    {
        $buyRequest = new Varien_Object($attribute);
        $quote = Mage::getModel('sales/quote');

        try{
            $this->_addProductResult = $quote->addProductAdvanced($product, $buyRequest);
        }catch(Exception $e){
            $errors = explode("\n", $e->getMessage());
            $errors = array_unique($errors);
            $this->_addProductResult = implode("\n", $errors);
        }

        $this->_quote = $quote;
    }

    /**
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
     * @param int $size
     * @return mixed
     */
    public function getImage($size = 180)
    {
        if ($this->getScp()) {
            $itemRendererScp = $this->getItemRendererScp();
            return $itemRendererScp->getProductThumbnail();
        }

        return $this->getProductImage($size, $this->getProduct());
    }

    /**
     * Get a image of a product.
     * @param int $size
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Helper_Image
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
     * @param $product
     * @return mixed
     */
    public function getProductName(Mage_Catalog_Model_Product $product)
    {
        return Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name');
    }

    /**
     * Get the name of the set product.
     * @return mixed
     */
    public function getName()
    {
        if ($this->getScp()) {
            $itemRendererScp = $this->getItemRendererScp();
            return $itemRendererScp->getProductName();
        }

        return $this->getProductName($this->getProduct());
    }

    /**
     * Get the price of this product.
     * @return string
     */
    public function getPrice()
    {
        $html = "";
        if (Mage::helper('qquoteadv/not2order')->getShowPrice($this->getProduct())) {
            $html = $this->getPriceHtml($this->getProduct());
        }

         return $html;
    }

    /**
     * Get a product price
     * @param Mage_Catalog_Model_Product $product
     * @return String
     */
    public function getProductPrice(Mage_Catalog_Model_Product $product)
    {
        return Mage::helper('core')->currency($product->getPrice(), true, false);
    }

    /**
     * Get the product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->getData('product') instanceof Mage_Catalog_Model_Product) {
            return $this->setProduct(Mage::getModel('Mage_Catalog_Model_Product'));
        } else {
            return $this->getData('product');
        }
    }

    /**
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
     * @return array
     */
    public function getOptions()
    {
        if ($this->getScp()) {
            $itemRendererScp = $this->getItemRendererScp();
            return $itemRendererScp->getOptionList();
        }

        if ($this->_productValues instanceof Varien_Object) {
            return $this->_productValues->getOptions();
        } else {
            return array();
        }
    }

    /**
     *  Init the Magento quote with one or more items.
     */
    public function _beforeToHtml()
    {
        $this->getQuoteItem($this->getProduct(), $this->getPostData());
        $this->_productValues = $this->getProductValues($this->getProduct());
        parent::_beforeToHtml();
    }

    /**
     * Get the SCP item renderer for a quote items
     *
     * @return \Ophirah_Qquoteadv_Block_Checkout_Miniquote_Item_Miniquoterenderer|boolean
     */
    public function getItemRendererScp()
    {
        $product = $this->getProduct();
        $infoBuyRequest = $product->getCustomOption('info_buyRequest');

        $attibutes = null;
        if (isset($infoBuyRequest) && $infoBuyRequest->getValue()) {
            $attibutes = $infoBuyRequest->getValue();
        }

        $quoteByProduct = Mage::helper('qquoteadv')->getQuoteItem($product, $attibutes);
        $quoteItems = $quoteByProduct->getAllItems();
        foreach ($quoteItems as $item) {
            $renderer = $this->getLayout()
                ->createBlock('qquoteadv/checkout_miniquote_item_miniquoterenderer')
                ->setTemplate('qquoteadv/checkout/quote/miniquoteitem.phtml')
                ->setRenderedBlock($this);

            $renderer->setItem($item);
            return $renderer;
        }

        return false;
    }
}
