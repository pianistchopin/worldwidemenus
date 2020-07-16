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

$sql = "
SELECT country_id, customer_id  
FROM `{$installer->getTable('qquoteadv/qqadvcustomer')}`
WHERE  is_quote=1 and  customer_id IN(
	SELECT customer_id  
	FROM `{$installer->getTable('qquoteadv/qqadvcustomer')}`
	WHERE is_quote=1 group by customer_id having count(customer_id)>1
) 
ORDER BY quote_id asc";

$result = $installer->getConnection()->fetchAll($sql);
$cacheSql = array();
foreach ($result as $item) {
    $countryId = $item['country_id'];
    $customerId = $item['customer_id'];

    $update = "UPDATE `{$installer->getTable('qquoteadv/qqadvcustomer')}` SET country_id='" . $countryId . "'
    			WHERE customer_id=" . $customerId . " and country_id=''";
    $installer->run($update);
}
$installer->endSetup();
