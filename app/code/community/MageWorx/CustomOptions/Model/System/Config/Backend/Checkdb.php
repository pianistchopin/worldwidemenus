<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_System_Config_Backend_Checkdb extends Mage_Core_Model_Config_Data
{
    protected function _afterSave() {        
        try {                
            // check db setup
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_write');
            if (!$connection->tableColumnExists($resource->getTableName('mageworx_customoptions/option_type_special_price'), 'date_to')) {
                $connection->delete($resource->getTableName('core/resource'), "code =  'customoptions_setup'");
            }
        } catch (Exception $e) {}        
    }
}
