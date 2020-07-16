<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$ordermodeId = $installer->getAttributeId('catalog_product', 'allowed_to_ordermode');
$defaultValue = 1; //YES
$entityTypeId = $installer->getEntityTypeId('catalog_product');  //eav_entity_type

$groupedResult = $installer->getConnection()->fetchAll(
    "SELECT entity_id, store_id from {$installer->getTable('catalog_product_entity_int')} group by entity_id,store_id"
);

if ($ordermodeId)
    foreach ($groupedResult as $row) {
        $entityId = $row['entity_id'];
        $storeId = $row['store_id'];

        $sql = "INSERT IGNORE INTO {$installer->getTable('catalog_product_entity_int')}
     (`value_id`,`entity_type_id`,`attribute_id`,`store_id`,`entity_id`,`value`)
     VALUES (NULL,'{$entityTypeId}','{$ordermodeId}','{$storeId}','{$entityId}','{$defaultValue}')";

        $installer->run($sql);
    }

$installer->endSetup();