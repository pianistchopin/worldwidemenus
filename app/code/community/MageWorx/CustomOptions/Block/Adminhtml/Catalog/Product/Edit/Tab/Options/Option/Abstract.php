<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

if ((string)Mage::getConfig()->getModuleConfig('Aitoc_Aitoptionstemplate')->active == 'true'){
    class MageWorx_CustomOptions_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Option_Abstract extends Aitoc_Aitoptionstemplate_Block_Rewrite_AdminhtmlCatalogProductEditTabOptionsOption {}
} else {
    class MageWorx_CustomOptions_Block_Adminhtml_Catalog_Product_Edit_Tab_Options_Option_Abstract extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option {}
}