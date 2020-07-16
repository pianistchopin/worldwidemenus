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

$tableName = $installer->getTable('exitoffer/popup');

$resource = Mage::getSingleton('core/resource');
$db = $resource->getConnection('core_write');


$db->addColumn($tableName, 'use_captcha',  array(
    'TYPE'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'LENGTH' =>  '1',
    'COMMENT'   => 'Show captcha on popup'
));

$installer->endSetup();