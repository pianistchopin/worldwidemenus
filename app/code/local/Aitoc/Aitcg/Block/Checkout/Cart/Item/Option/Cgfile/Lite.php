<?php
class Aitoc_Aitcg_Block_Checkout_Cart_Item_Option_Cgfile_Lite extends Aitoc_Aitcg_Block_Checkout_Cart_Item_Option_Cgfile 
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('aitcg/checkout/cart/item/option/cgfile_lite.phtml');
    }
}
