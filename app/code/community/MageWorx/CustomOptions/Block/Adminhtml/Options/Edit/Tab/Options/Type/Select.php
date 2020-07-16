<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Block_Adminhtml_Options_Edit_Tab_Options_Type_Select extends
    Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Type_Select
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mageworx/customoptions/catalog-product-edit-options-type-select.phtml');
    }
}