<?php
class Magecomp_AbsolutePricing_Block_Edit_Tab_Options_Option extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option
{
    public function getPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'fixed') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'absolute') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'absoluteonce') {
            return number_format($value, 2, null, '');
        }
    }
}
