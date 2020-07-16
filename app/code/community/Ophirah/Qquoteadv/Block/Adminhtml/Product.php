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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Product
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Template
{
    /**
     * @var array
     */
    protected $_itemRenders = array();

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        //$this->view->baseUrl = $this->_request->getBaseUrl();
        $this->setTemplate('qquoteadv/productlist.phtml');
    }

    /**
     * Adds 'Add Selected Product(s) to Advanced Quote' button in the backend
     *
     * @return mixed
     */
    protected function _prepareLayout()
    {
        $this->setChild('add_new_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('qquoteadv')->__('Add Selected Product(s) to Advanced Quote'),
                    'onclick' => "transfer_items()",
                    'class' => 'add'
                ))
        );
        /**
         * Display store switcher if system has more one store
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->setChild('store_switcher',
                $this->getLayout()->createBlock('adminhtml/store_switcher')
                    ->setUseConfirm(false)
                    ->setSwitchUrl($this->getUrl('*/*/*', array('store' => null)))
            );
        }
        $this->setChild('grid', $this->getLayout()->createBlock('qquoteadv/adminhtml_product_grid', 'product.grid'));
        return parent::_prepareLayout();
    }

    /**
     * Returns the add new button
     *
     * @return mixed
     */
    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_new_button');
    }

    /**
     * Returns the grid html
     *
     * @return mixed
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Returns the store switcher html
     *
     * @return mixed
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

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
                ->setTemplate($this->_itemRenders[$type]['template'])
                ->setRenderedBlock($this);
        }

        return $this->_itemRenders[$type]['blockInstance'];
    }

    /**
     * Returns the html of a rendered item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return mixed
     */
    public function getItemHtml(Mage_Sales_Model_Quote_Item $item)
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

}
