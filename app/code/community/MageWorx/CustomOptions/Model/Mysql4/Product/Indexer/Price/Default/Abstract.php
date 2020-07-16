<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

if ((string)Mage::getConfig()->getModuleConfig('Innoexts_AdvancedPricing')->active == 'true'){
    class MageWorx_CustomOptions_Model_Mysql4_Product_Indexer_Price_Default_Abstract extends Innoexts_AdvancedPricing_Model_Mysql4_Catalog_Product_Indexer_Price_Default {}
} elseif ((string)Mage::getConfig()->getModuleConfig('Innoexts_StorePricing')->active == 'true') { 
    class MageWorx_CustomOptions_Model_Mysql4_Product_Indexer_Price_Default_Abstract extends Innoexts_StorePricing_Model_Mysql4_Catalog_Product_Indexer_Price_Default {}
} else {
    class MageWorx_CustomOptions_Model_Mysql4_Product_Indexer_Price_Default_Abstract extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default {}
}