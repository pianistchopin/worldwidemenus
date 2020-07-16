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
$tableName = $installer->getTable('exitoffer/popup');

$resource = Mage::getSingleton('core/resource');
$db = $resource->getConnection('core_write');

$db->addColumn($tableName, 'email_template',  array(
    'TYPE'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'LENGTH' =>  5,
    'COMMENT'   => 'Contact Email Template'
));

$installer->endSetup();