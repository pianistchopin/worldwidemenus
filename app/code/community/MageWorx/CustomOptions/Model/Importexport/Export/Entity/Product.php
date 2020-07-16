<?php

/**
 * MageWorx
 * CustomOptions Extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

if (version_compare(Mage::helper('mageworx_customoptions')->getMagetoVersion(), '1.8.0', '<')) {
    class MageWorx_CustomOptions_Model_Importexport_Export_Entity_Product extends MageWorx_CustomOptions_Model_Importexport_Export_Entity_Product_M1700 {}
} else if (version_compare(Mage::helper('mageworx_customoptions')->getMagetoVersion(), '1.9.2.4', '<=')){
    class MageWorx_CustomOptions_Model_Importexport_Export_Entity_Product extends MageWorx_CustomOptions_Model_Importexport_Export_Entity_Product_M1800 {}
} else {
    class MageWorx_CustomOptions_Model_Importexport_Export_Entity_Product extends MageWorx_CustomOptions_Model_Importexport_Export_Entity_Product_M1930 {}
}
