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
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Renderers_Bundle
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Renderers_Bundle extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit_Items_Renderers_Abstract
{
    /**
     * Check if the product has bundle options
     *
     * @return bool
     */
    public function hasBundleOptions()
    {
        if ($this->getBundleOptions() && count($this->getBundleOptions())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the bundle options
     *
     * @return array
     */
    public function getBundleOptions()
    {
        if ($this->productValues instanceof Varien_Object) {
            return $this->productValues->getBundleOptions();
        } else {
            return array();
        }
    }

    /**
     * Get the product price
     *
     * @return string
     */
    public function getPrice()
    {
        $block = $this->getLayout()->createBlock('bundle/catalog_product_price')->setProduct($this->getProduct());
        $block->setTemplate('bundle/catalog/product/view/price.phtml');

        return $block->toHtml();
    }


}