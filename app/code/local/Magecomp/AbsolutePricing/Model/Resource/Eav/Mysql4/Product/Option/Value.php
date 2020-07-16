<?php
class Magecomp_AbsolutePricing_Model_Resource_Eav_Mysql4_Product_Option_Value extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Option_Value
{
    protected function  _construct()
    {
		parent::_construct();
    }
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $priceTable = $this->getTable('catalog/product_option_type_price');
        $titleTable = $this->getTable('catalog/product_option_type_title');

        if (!$object->getData('scope', 'price')) {
            $statement = $this->_getReadAdapter()->select()
                ->from($priceTable)
                ->where('option_type_id = '.$object->getId().' AND store_id = ?', 0);
            if ($this->_getReadAdapter()->fetchOne($statement)) {
                if ($object->getStoreId() == '0') {
                    $this->_getWriteAdapter()->update(
                        $priceTable,
                        array(
                            'price' => $object->getPrice(),
                            'price_type' => $object->getPriceType()
                        ),
                        $this->_getWriteAdapter()->quoteInto('option_type_id = '.$object->getId().' AND store_id = ?', 0)
                    );
                }
            } else {
                $this->_getWriteAdapter()->insert(
                    $priceTable,
                    array(
                        'option_type_id' => $object->getId(),
                        'store_id' => 0,
                        'price' => $object->getPrice(),
                        'price_type' => $object->getPriceType()
                    )
                );
            }
        }
        $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
        if ($object->getStoreId() != '0' && $scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE
            && !$object->getData('scope', 'price')) {
            $baseCurrency = Mage::app()->getBaseCurrencyCode();
            $storeIds = $object->getProduct()->getStoreIds();
            if (is_array($storeIds)) {
                foreach ($storeIds as $storeId) {
                    if ($object->getPriceType() == 'fixed' || $object->getPriceType() == 'absolute') {
                        $storeCurrency = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
                        $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($storeCurrency);
                        if (!$rate) {
                            $rate = 1;
                        }
                        $newPrice = $object->getPrice() * $rate;
                    } else {
                        $newPrice = $object->getPrice();
                    }
                    $statement = $this->_getReadAdapter()->select()
                        ->from($priceTable)
                        ->where('option_type_id = '.$object->getId().' AND store_id = ?', $storeId);

                    if ($this->_getReadAdapter()->fetchOne($statement)) {
                        $this->_getWriteAdapter()->update(
                            $priceTable,
                            array(
                                'price' => $newPrice,
                                'price_type' => $object->getPriceType()
                            ),
                            $this->_getWriteAdapter()->quoteInto('option_type_id = '.$object->getId().' AND store_id = ?', $storeId)
                        );
                    } else {
                        $this->_getWriteAdapter()->insert(
                            $priceTable,
                            array(
                                'option_type_id' => $object->getId(),
                                'store_id' => $storeId,
                                'price' => $newPrice,
                                'price_type' => $object->getPriceType()
                            )
                        );
                    }
                }
            }
        } elseif ($scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE && $object->getData('scope', 'price')) {
            $this->_getWriteAdapter()->delete(
                $priceTable,
                $this->_getWriteAdapter()->quoteInto('option_type_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
            );
        }
        if (!$object->getData('scope', 'title')) {
            $statement = $this->_getReadAdapter()->select()
                ->from($titleTable)
                ->where('option_type_id = '.$object->getId().' AND store_id = ?', 0);

            if ($this->_getReadAdapter()->fetchOne($statement)) {
                if ($object->getStoreId() == '0') {
                    $this->_getWriteAdapter()->update(
                        $titleTable,
                            array('title' => $object->getTitle()),
                            $this->_getWriteAdapter()->quoteInto('option_type_id='.$object->getId().' AND store_id=?', 0)
                    );
                }
            } else {
                $this->_getWriteAdapter()->insert(
                    $titleTable,
                        array(
                            'option_type_id' => $object->getId(),
                            'store_id' => 0,
                            'title' => $object->getTitle()
                ));
            }
        }

        if ($object->getStoreId() != '0' && !$object->getData('scope', 'title')) {
            $statement = $this->_getReadAdapter()->select()
                ->from($titleTable)
                ->where('option_type_id = '.$object->getId().' AND store_id = ?', $object->getStoreId());

            if ($this->_getReadAdapter()->fetchOne($statement)) {
                $this->_getWriteAdapter()->update(
                    $titleTable,
                        array('title' => $object->getTitle()),
                        $this->_getWriteAdapter()
                            ->quoteInto('option_type_id='.$object->getId().' AND store_id=?', $object->getStoreId()));
            } else {
                $this->_getWriteAdapter()->insert(
                    $titleTable,
                        array(
                            'option_type_id' => $object->getId(),
                            'store_id' => $object->getStoreId(),
                            'title' => $object->getTitle()
                ));
            }
        } elseif ($object->getData('scope', 'title')) {
            $this->_getWriteAdapter()->delete(
                $titleTable,
                $this->_getWriteAdapter()->quoteInto('option_type_id = '.$object->getId().' AND store_id = ?', $object->getStoreId())
            );
        }
		return $object;
    }
}