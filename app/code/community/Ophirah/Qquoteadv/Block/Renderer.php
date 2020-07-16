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
 * Class Ophirah_Qquoteadv_Block_Renderer
 */
class Ophirah_Qquoteadv_Block_Renderer extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * @var array
     */
    protected $_itemRenders = array();

    /**
     * Add renderer for item product type
     *
     * @param   string $productType
     * @param   string $blockType
     * @param   string $template
     * @return  Mage_Checkout_Block_Cart_Abstract
     */
    public function addItemRender($productType, $blockType, $template)
    {
        $this->_itemRenders[$productType] = array(
            'block' => $blockType,
            'template' => $template,
            'blockInstance' => null
        );
        return $this;
    }

    /**
     * Get renderer information by product type code
     *
     * @deprecated please use getItemRendererInfo() method instead
     * @see getItemRendererInfo()
     * @param   string $type
     * @return  array
     */
    public function getItemRender($type)
    {
        return $this->getItemRendererInfo($type);
    }

    /**
     * Get renderer information by product type code
     *
     * @param   string $type
     * @return  array
     */
    public function getItemRendererInfo($type)
    {
        if (isset($this->_itemRenders[$type])) {
            return $this->_itemRenders[$type];
        }
        return $this->_itemRenders['default'];
    }

    /**
     * Get renderer block instance by product type code
     *
     * @param   string $type
     * @return  array
     */
    public function getItemRenderer($type)
    {
        if (!isset($this->_itemRenders[$type])) {
            $type = 'default';
        }
        if (is_null($this->_itemRenders[$type]['blockInstance'])) {
            $this->_itemRenders[$type]['blockInstance'] = $this->getLayout()
                ->createBlock($this->_itemRenders[$type]['block'])
                ->setParentBlock($this)
                ->setPostData($this->getPostData())
                ->setTemplate($this->_itemRenders[$type]['template'])
                ->setRenderedBlock($this);
        }

        return $this->_itemRenders[$type]['blockInstance'];
    }

    /**
     * Returns the html of a rendered item
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    public function getItemHtml(Mage_Catalog_Model_Product $product)
    {
        $renderer = $this->getItemRenderer($product->getTypeId())->setProduct($product);
        return $renderer->toHtml();
    }

    /**
     * @return Mage_Catalog_Model_Product|Mage_Core_Model_Abstract
     */
    public function getSelectedProduct()
    {
        $product = $this->getProduct();
        $postData = new Varien_Object($this->getPostData());

        if ($postData->getSuperGroup()) {
            $product = Mage::getModel('catalog/product')->load($postData->getProduct());
            $product->processBuyRequest($postData);
        }

        return $product;
    }
}
