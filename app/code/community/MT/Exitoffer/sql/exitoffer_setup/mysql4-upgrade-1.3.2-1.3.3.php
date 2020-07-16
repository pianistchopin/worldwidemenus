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

$installer = $this;
$installer->startSetup();
$tableName = $installer->getTable('exitoffer/campaign');

$resource = Mage::getSingleton('core/resource');
$db = $resource->getConnection('core_write');


$db->addColumn($tableName, 'show_on_load',  array(
    'TYPE'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'LENGTH' =>  20,
    'COMMENT'   => 'Show on load active'
));

$db->addColumn($tableName, 'show_on_load_delay',  array(
    'TYPE'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'DEFAULT' =>  0,
    'COMMENT'   => 'Show on load delay'
));

$installer->endSetup();