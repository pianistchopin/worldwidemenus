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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/** @var Ophirah_Crmaddon_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

$messageBody = "&lt;p&gt;&lt;strong&gt;Hello {{var CRMcustomername}},&lt;/strong&gt;&lt;br /&gt;&lt;br /&gt;This is the default template for CRM messages to send to the customer. Edit or update this CRM template and save it as a new template. This way you can add as many CRM templates as you want.&lt;br /&gt;&lt;br /&gt;With the WYSYWIG editor it&#039;s even possible to use default markup in the CRM templates and messages!&lt;br /&gt;You can use:&lt;br /&gt;&lt;br /&gt;&lt;strong&gt;Bold&lt;/strong&gt;&lt;br /&gt;&lt;em&gt;Italic&lt;/em&gt;&lt;br /&gt;&lt;span style=&quot;text-decoration: underline;&quot;&gt;Underlined&lt;/span&gt;&lt;br /&gt;&lt;br /&gt;If you to use custom variables as the customer name and the sender name, you can use: - &lt;em&gt;between double curly braces {{ &amp;hellip; }}&amp;nbsp; &lt;/em&gt;:&lt;br /&gt;&lt;br /&gt;var CRMcustomername&lt;br /&gt;var CRMsendername&lt;br /&gt;&lt;br /&gt;It&#039;s all here in the CRM addon module!!&lt;br /&gt;&lt;br /&gt;with Kind Regards,&lt;br /&gt;{{var CRMsendername}}&lt;/p&gt;";

$sql = "INSERT INTO `{$installer->getTable('quoteadv_crmaddon_templates')}` (`name`,`subject`,`template`,`default`, `status`) VALUES ('Default Template', 'Default Template Subject', '{$messageBody}', 1, 1)";
$connection = $installer->getConnection();
$connection->query($sql);

$installer->endSetup();
