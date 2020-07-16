<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

if ((string)Mage::getConfig()->getModuleConfig('GoMage_Procart')->active == 'true'){
    class MageWorx_CustomOptions_Helper_Product_Configuration_Abstract extends GoMage_Procart_Helper_Product_Configuration {}
} else {
    class MageWorx_CustomOptions_Helper_Product_Configuration_Abstract extends Mage_Catalog_Helper_Product_Configuration {}
}