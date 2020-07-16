<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

$tableName = $installer->getTable('exitoffer/field');

$resource = Mage::getSingleton('core/resource');
$db = $resource->getConnection('core_write');


$db->addColumn($tableName, 'default_value',  array(
    'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'COMMENT'   => 'Default field value'
));

$db->addColumn($tableName, 'admin_title',  array(
    'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'COMMENT'   => 'Admin Title'
));

$db->addColumn($tableName, 'error_message_is_required',  array(
    'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'COMMENT'   => 'Error message is required'
));

$tableName = $installer->getTable('newsletter/subscriber');
$db->addColumn(
    $tableName,
    'created_at',
    array(
        'TYPE' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'DEFAULT' =>  Varien_Db_Ddl_Table::TIMESTAMP_INIT,
        'COMMENT'   => 'Created At'
    )
);

$installer->endSetup();