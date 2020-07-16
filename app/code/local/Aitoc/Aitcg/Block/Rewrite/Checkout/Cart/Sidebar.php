<?php
class Aitoc_Aitcg_Block_Rewrite_Checkout_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
    /*
     * overwrite parent
     * purpose - to delete from the cart sidebar block social widgets code (
     * this code not work for sidebar )
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $html = Mage::helper('aitcg')->removeSocialWidgetsFromHtml($html);

        return $html;
    }
}
