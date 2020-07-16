<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

if ((string)Mage::getConfig()->getModuleConfig('Bluejalappeno_Tieredproducts')->active == 'true'){
    class MageWorx_CustomOptions_Model_Catalog_Product_Type_Downloadable_Price_Abstract extends Bluejalappeno_Tieredproducts_Model_Product_Type_Downloadable_Price {}
} else {
    class MageWorx_CustomOptions_Model_Catalog_Product_Type_Downloadable_Price_Abstract extends Mage_Downloadable_Model_Product_Price {}
}