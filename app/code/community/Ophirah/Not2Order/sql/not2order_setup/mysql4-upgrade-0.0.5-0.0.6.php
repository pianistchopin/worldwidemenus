<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$ordermodeId = $installer->getAttributeId('catalog_product', 'hideprice_for_ordermode');
$defaultValue = 0; //NO
$entityTypeId = $installer->getEntityTypeId('catalog_product');  //eav_entity_type

$groupedResult = $installer->getConnection()->fetchAll(
    "SELECT entity_id, store_id from {$installer->getTable('catalog_product_entity_int')} group by entity_id,store_id"
);

if (!$ordermodeId) {
    foreach ($groupedResult as $row) {
        $entityId = $row['entity_id'];
        $storeId = $row['store_id'];

        $checkIfExists = "SELECT *
                          FROM {$installer->getTable( 'catalog_product_entity_int' )}
                          WHERE entity_type_id = {$entityTypeId}
                                AND attribute_id = {$ordermodeId}
                                AND store_id = {$storeId}
                                AND entity_id = {$entityId}";
        $checkIfExistsSql = $installer->getConnection()->fetchAll($checkIfExists);
        if (is_null($checkIfExistsSql)) {
            $sql = "INSERT INTO {$installer->getTable( 'catalog_product_entity_int' )}
                     (`value_id`,`entity_type_id`,`attribute_id`,`store_id`,`entity_id`,`value`)
                     VALUES (NULL,'{$entityTypeId}','{$ordermodeId}','{$storeId}','{$entityId}','{$defaultValue}')";

            $installer->run($sql);
        }
    }
}

$installer->endSetup();