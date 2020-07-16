<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

/* add salesrep dropdown to customer admin */
$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$setup->addAttribute('customer', 'assigned_sales_rep', array(
    'type'              => 'int',
    'input'             => 'select',
    'label'             => 'Assigned sales representative',
    'global'            => 1,
    'visible'           => 1,
    'required'          => 0,
    'user_defined'      => 1,
    'default'           => '',
    'visible_on_front'  => 0,
    'position'          => 100,
    'source'            => 'qquoteadv/entity_assignsalesrep',
    'note'              => 'This overwrites the automatically assigned sales rep.'
));

$customer = Mage::getModel('customer/customer');
$attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
$setup->addAttributeToSet('customer', $attrSetId, 'General', 'assigned_sales_rep');

//add to the backend customer form
Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'assigned_sales_rep')
    ->setData('assigned_sales_rep', array('adminhtml_customer'))
    ->save();

Mage::getModel('customer/attribute')->loadByCode('customer', 'assigned_sales_rep')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();


/* fix edit increment id for updating users */
$read = $installer->getConnection();
$dbname = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');

// Add edit_increment < already happened in 4.3.4-4.3.5 but not for some users
$checkIfColumnExistResults = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$installer->getTable('quoteadv_customer')}' AND COLUMN_NAME = 'edit_increment' and TABLE_SCHEMA = '$dbname' ";
$rows = $read->fetchAll($checkIfColumnExistResults);

if(!count($rows)) {
    $installer->run("
        ALTER TABLE `{$installer->getTable('quoteadv_customer')}` ADD `edit_increment` INT(11) DEFAULT NULL AFTER `increment_id`;
    ");
}

//end sql setup
$installer->endSetup();
