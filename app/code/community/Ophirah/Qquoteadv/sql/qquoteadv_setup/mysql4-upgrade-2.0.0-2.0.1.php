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

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'prefix',
    'varchar(40) NOT NULL default "" after customer_id'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'suffix',
    'varchar(40) NOT NULL default "" after lastname'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'region_id',
    'int(10) default NULL after region'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_prefix',
    'varchar(40) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_firstname',
    'varchar(255) default NULL'
);


$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_middlename',
    'varchar(40) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_lastname',
    'varchar(255) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_suffix',
    'varchar(40) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_company',
    'varchar(255) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_country_id',
    'varchar(255) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_region',
    'varchar(255) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_region_id',
    'int(10) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_city',
    'varchar(255) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_address',
    'varchar(255) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_postcode',
    'varchar(255) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_telephone',
    'varchar(255) default NULL'
);

$installer->getConnection()->addColumn(
    $installer->getTable('quoteadv_customer'),
    'shipping_fax',
    'varchar(255) default NULL'
);

//# copy project old billing address to shipping
$select = $installer->getConnection()->select();
$select->from(array('result' => $installer->getTable('quoteadv_customer')));

foreach ($installer->getConnection()->fetchAll($select) as $key => $result) {
    $update = "UPDATE {$installer->getTable('quoteadv_customer')}
        SET
            shipping_prefix = '" . $result['prefix'] . "',
            shipping_firstname = '" . $result['firstname'] . "',
            shipping_middlename = '" . $result['middlename'] . "',
            shipping_lastname = '" . $result['lastname'] . "',
            shipping_suffix = '" . $result['suffix'] . "',
            shipping_company = '" . $result['company'] . "',
            shipping_country_id = '" . $result['country_id'] . "',
            shipping_region = '" . $result['region'] . "',
            shipping_region_id = '" . $result['region_id'] . "',
            shipping_city = '" . $result['city'] . "',
            shipping_address = '" . $result['address'] . "',
            shipping_postcode = '" . $result['postcode'] . "',
            shipping_telephone = '" . $result['telephone'] . "',
            shipping_fax = '" . $result['fax'] . "'

        WHERE
            (quote_id='" . $result['quote_id'] . "')
    ";
    $installer->run($update);
}

$installer->endSetup();
