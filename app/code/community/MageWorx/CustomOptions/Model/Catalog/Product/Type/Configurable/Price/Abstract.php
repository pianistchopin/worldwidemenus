<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

if ((string)Mage::getConfig()->getModuleConfig('DerModPro_BCP')->active == 'true'){
    class MageWorx_CustomOptions_Model_Catalog_Product_Type_Configurable_Price_Abstract extends DerModPro_BCP_Model_Catalog_Product_Type_Configurable_Price {}
} elseif ((string)Mage::getConfig()->getModuleConfig('Ayasoftware_SimpleProductPricing')->active == 'true'){
    class MageWorx_CustomOptions_Model_Catalog_Product_Type_Configurable_Price_Abstract extends Ayasoftware_SimpleProductPricing_Catalog_Model_Product_Type_Configurable_Price {}
} elseif ((string)Mage::getConfig()->getModuleConfig('Bluejalappeno_Tieredproducts')->active == 'true'){
    class MageWorx_CustomOptions_Model_Catalog_Product_Type_Configurable_Price_Abstract extends Bluejalappeno_Tieredproducts_Model_Product_Type_Configurable_Price {}
} else {
    class MageWorx_CustomOptions_Model_Catalog_Product_Type_Configurable_Price_Abstract extends Mage_Catalog_Model_Product_Type_Configurable_Price {}
}