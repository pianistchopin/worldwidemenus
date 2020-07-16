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
 * Cart sidebar container
 */
class Ophirah_Qquoteadv_Model_Container_Miniquote extends Enterprise_PageCache_Model_Container_Advanced_Quote
{
    /**
     * Overwrite for saveCache
     *
     * @param $blockContent
     * @param array $tags
     * @return $this
     */
    public function saveCache($blockContent, $tags = array())
    {
        return $this;
    }

    /**
     * Returns the html of the rendered block miniquote_head
     *
     * @return mixed
     */
    protected function _renderBlock()
    {
        $layout = $this->_getLayout('default');
        $block = $layout->getBlock('miniquote_head');

        return $block->toHtml();
    }
}
