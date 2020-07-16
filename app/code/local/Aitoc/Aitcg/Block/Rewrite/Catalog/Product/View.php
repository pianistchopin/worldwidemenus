<?php
class Aitoc_Aitcg_Block_Rewrite_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    protected function _toHtml() {
        $result = parent::_toHtml();
        $result = preg_replace('|\<form action="([^"]*)" method="post" id="product_addtocart_form"\>|Uis','<form action="$1" method="post" id="product_addtocart_form" enctype="multipart/form-data">',$result);
        return $result;
    }
}
