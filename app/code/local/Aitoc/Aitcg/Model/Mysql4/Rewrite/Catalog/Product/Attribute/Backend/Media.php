<?php
class Aitoc_Aitcg_Model_Mysql4_Rewrite_Catalog_Product_Attribute_Backend_Media extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Backend_Media
{
    /**
     * Load gallery images for product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Product_Attribute_Backend_Media $object
     * @return array
     */
    public function loadGallery($product, $object)
    {
        if(version_compare(Mage::getVersion(), '1.9.1.0', '>='))
        {
            return parent::loadGallery($product, $object);
        }
        // Select gallery images for product
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('main'=>$this->getMainTable()),
                array('value_id', 'value AS file')
            )
            ->joinLeft(
                array('value'=>$this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id=value.value_id AND value.store_id='.(int)$product->getStoreId(),
                array('label','position','disabled','cgimage')
            )
            ->joinLeft( // Joining default values
                array('default_value'=>$this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id=default_value.value_id AND default_value.store_id=0',
                array(
                    'label_default' => 'label',
                    'position_default' => 'position',
                    'disabled_default' => 'disabled'
                )
            )
            ->where('main.attribute_id = ?', $object->getAttribute()->getId())
            ->where('main.entity_id = ?', $product->getId())
            ->order('IF(value.position IS NULL, default_value.position, value.position) ASC');

        $result = $this->_getReadAdapter()->fetchAll($select);
        $this->_removeDuplicates($result);
        return $result;
    }


    /**
     * Get select to retrieve media gallery images
     * for given product IDs.
     *
     * @param array $productIds
     * @param $storeId
     * @param int $attributeId
     * @return Varien_Db_Select
     */
    protected function _getLoadGallerySelect(array $productIds, $storeId, $attributeId) {
        $adapter = $this->_getReadAdapter();

        $positionCheckSql = $adapter->getCheckSql('value.position IS NULL', 'default_value.position', 'value.position');

        // Select gallery images for product
        $select = $adapter->select()
            ->from(
            array('main'=>$this->getMainTable()),
            array('value_id', 'value AS file', 'product_id' => 'entity_id')
        )
            ->joinLeft(
            array('value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
            $adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int)$storeId),
            array('label','position','disabled','cgimage')
        )
            ->joinLeft( // Joining default values
            array('default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
            'main.value_id = default_value.value_id AND default_value.store_id = 0',
            array(
                'label_default' => 'label',
                'position_default' => 'position',
                'disabled_default' => 'disabled'
            )
        )
            ->where('main.attribute_id = ?', $attributeId)
            ->where('main.entity_id in (?)', $productIds)
            ->order($positionCheckSql . ' ' . Varien_Db_Select::SQL_ASC);

        return $select;
    }
}
