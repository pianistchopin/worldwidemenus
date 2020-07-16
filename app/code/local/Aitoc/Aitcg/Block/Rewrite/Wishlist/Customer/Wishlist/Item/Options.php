<?php
class Aitoc_Aitcg_Block_Rewrite_Wishlist_Customer_Wishlist_Item_Options extends Mage_Wishlist_Block_Customer_Wishlist_Item_Options
{
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $html = Mage::helper('aitcg')->removeSocialWidgetsFromHtml($html);

        return $html;
    }
}
