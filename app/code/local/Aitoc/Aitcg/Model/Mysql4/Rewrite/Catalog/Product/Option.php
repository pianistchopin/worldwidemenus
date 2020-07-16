<?php

class Aitoc_Aitcg_Model_Mysql4_Rewrite_Catalog_Product_Option
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Option
{

    protected function _saveForZeroStore($object)
    {

        $priceTable = $this->getTable('catalog/product_option_price');
        if (!$object->getData('scope', 'price')) {
            if ($this->_ifStoreAndOption($object->getId(), 0, $priceTable)) {
                if ($object->getStoreId() == '0') {
                    $this->_getWriteAdapter()->update(
                        $priceTable,
                        array(
                            'price'      => $object->getPrice(),
                            'price_type' => $object->getPriceType()
                        ),
                        $this->_getWriteAdapter()->quoteInto('option_id = ' . $object->getId() . ' AND store_id = ?', 0)
                    );

                    return true;
                }
            } else {
                $this->_getWriteAdapter()->insert(
                    $priceTable,
                    array(
                        'option_id'  => $object->getId(),
                        'store_id'   => 0,
                        'price'      => $object->getPrice(),
                        'price_type' => $object->getPriceType()
                    )
                );

                return true;
            }
        }

        return false;

    }


    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        //better to check param 'price' and 'price_type' for saving. If there is not price scip saving price
        if (Mage::helper('aitcg/options')->checkAitOption($object)) {
            $priceTable    = $this->getTable('catalog/product_option_price');
            $aitImageTable = $this->getTable('catalog/product_option_aitimage');

            //save for store_id = 0
            $this->_saveForZeroStore($object);

            $scope = (int)Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);

            if ($object->getStoreId() != '0' && $scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE
                && !$object->getData('scope', 'price')
            ) {

                $baseCurrency = Mage::app()->getBaseCurrencyCode();

                $storeIds = Mage::app()->getStore($object->getStoreId())->getWebsite()->getStoreIds();
                if (is_array($storeIds)) {
                    foreach ($storeIds as $storeId) {
                        if ($object->getPriceType() == 'fixed') {
                            $storeCurrency = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
                            $rate          = Mage::getModel('directory/currency')
                                ->load($baseCurrency)
                                ->getRate($storeCurrency);
                            if (!$rate) {
                                $rate = 1;
                            }
                            $newPrice = $object->getPrice() * $rate;
                        } else {
                            $newPrice = $object->getPrice();
                        }


                        if ($this->_ifStoreAndOption($object->getId(), $storeId, $priceTable)) {
                            $this->_getWriteAdapter()->update(
                                $priceTable,
                                array(
                                    'price'      => $newPrice,
                                    'price_type' => $object->getPriceType()
                                ),
                                $this->_getWriteAdapter()->quoteInto(
                                    'option_id = ' . $object->getId() . ' AND store_id = ?', $storeId
                                )
                            );
                        } else {
                            $this->_getWriteAdapter()->insert(
                                $priceTable,
                                array(
                                    'option_id'  => $object->getId(),
                                    'store_id'   => $storeId,
                                    'price'      => $newPrice,
                                    'price_type' => $object->getPriceType()
                                )
                            );
                        }
                    }// end foreach()
                }
            } elseif ($scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE && $object->getData('scope', 'price')) {
                $this->_getWriteAdapter()->delete(
                    $priceTable,
                    $this->_getWriteAdapter()->quoteInto(
                        'option_id = ' . $object->getId() . ' AND store_id = ?', $object->getStoreId()
                    )
                );
            }

            /* <<< AITOC_FIX */
            //aitcg image
            if (!$object->getData('scope', 'image_template_id')) {

                if ($this->_ifStoreAndOption($object->getId(), 0, $aitImageTable)) {
                    if ($object->getStoreId() == '0') {
                        $this->_getWriteAdapter()->update(
                            $aitImageTable, $this->_getArrayOption($object),
                            $this->_getWriteAdapter()->quoteInto('option_id=' . $object->getId() . ' AND store_id=?', 0)
                        );
                    }
                } else {
                    $this->_getWriteAdapter()->insert(
                        $aitImageTable,
                        array(
                            'option_id' => $object->getId(),
                            'store_id'  => 0
                        ) + $this->_getArrayOption($object)
                    );
                }
            }

            if ($object->getStoreId() != '0' && !$object->getData('scope', 'image_template_id')
                && $scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE
            ) {

                if ($this->_ifStoreAndOption($object->getId(), $object->getStoreId(), $aitImageTable)) {
                    $this->_getWriteAdapter()->update(
                        $aitImageTable, $this->_getArrayOption($object),
                        $this->_getWriteAdapter()
                            ->quoteInto('option_id=' . $object->getId() . ' AND store_id=?', $object->getStoreId())
                    );
                } else {
                    $insertArray              = $this->_getArrayOption($object);
                    $insertArray['option_id'] = $object->getId();
                    $insertArray['store_id']  = $object->getStoreId();
                    $this->_getWriteAdapter()->insert($aitImageTable, $insertArray);
                }
            } elseif ($object->getData('scope', 'image_template_id')
                && $scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE
            ) {
                $this->_getWriteAdapter()->delete(
                    $aitImageTable,
                    $this->_getWriteAdapter()->quoteInto(
                        'option_id = ' . $object->getId() . ' AND store_id = ?', $object->getStoreId()
                    )
                );
            }
            /* >>> AITOC_FIX */
        }

        return parent::_afterSave($object);
    }

    public function deletePrices($option_id)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('option_id=?', $option_id);

        $this->_getWriteAdapter()->delete(
            $this->getTable('catalog/product_option_aitimage'),
            $condition
        );

        return parent::deletePrices($option_id);;
    }

    protected function _ifStoreAndOption($option, $store, $Table)
    {

        $statement = $this->_getReadAdapter()->select()
            ->from($Table)
            ->where('option_id = ' . $option . ' AND store_id = ?', $store);
        if ($this->_getReadAdapter()->fetchOne($statement)) {
            return true;
        } else {
            return false;
        }
    }

    protected function _getArrayOption($object)
    {
        $option = array(
            'image_template_id'       => $object->getImageTemplateId(),
            'area_size_x'             => $object->getAreaSizeX(),
            'area_size_y'             => $object->getAreaSizeY(),
            'area_offset_x'           => $object->getAreaOffsetX(),
            'area_offset_y'           => $object->getAreaOffsetY(),
            'use_text'                => $object->getUseText(),
            'use_user_image'          => $object->getUseUserImage(),
            'use_digital_image'       => $object->getUseDigitalImage(),
            'spread_type'             => $object->getSpreadType(),
            'use_predefined_image'    => $object->getUsePredefinedImage(),
            'masks_cat_id'            => $object->getMasksCatId(),
            'mask_location'           => $object->getMaskLocation(),
            'use_masks'               => $object->getUseMasks(),
            'use_instagram'           => $object->getUseInstagram(),
            'use_pinterest'           => $object->getUsePinterest(),
            'use_black_white'         => $object->getUseBlackWhite(),
            'predefined_cats'         => $object->getPredefinedCats(),
            'allow_colorpick'         => $object->getAllowColorpick(),
            'text_length'             => $object->getTextLength(),
            'allow_text_distortion'   => $object->getAllowTextDistortion() == '' ? null
                : $object->getAllowTextDistortion(),
            'input_box_type'          => $object->getInputBoxType() == '' ? null : $object->getInputBoxType(),
            'curve_text'              => $object->getCurveText() == '' ? null : $object->getCurveText(),
            'allow_predefined_colors' => $object->getAllowPredefinedColors() == '' ? null
                : $object->getAllowPredefinedColors(),
            'color_set_id'            => $object->getColorSetId(),
            'def_img_behind_text'     => $object->getDefImgBehindText(),
            'def_img_behind_image'    => $object->getDefImgBehindImage(),
            'def_img_behind_clip'     => $object->getDefImgBehindClip(),
            'allow_save_graphics'     => $object->getAllowSaveGraphics(),
            'scale_image'             => $object->getScaleImage()
        );

        return $option;
    }

    /**
     * Duplicate custom options for product
     *
     * @param Mage_Catalog_Model_Product_Option $object
     * @param int                               $oldProductId
     * @param int                               $newProductId
     *
     * @return Mage_Catalog_Model_Product_Option
     */
    public function duplicate(Mage_Catalog_Model_Product_Option $object, $oldProductId, $newProductId)
    {

        $optionsCond = $this->_getOptionDataCond($object, $oldProductId, $newProductId);
        // copy options prefs
        foreach ($optionsCond as $oldOptionId => $newOptionId) {
            // title
            $this->_replaceInIable(
                'catalog/product_option_title', $newOptionId . ', `store_id`, `title`', $oldOptionId
            );

            $this->_replaceInIable(
                'catalog/product_option_price', $newOptionId . ', `store_id`, `price`, `price_type`', $oldOptionId
            );

            $sOptions = '`store_id`, `image_template_id`, `area_size_x`, `area_size_y`, `area_offset_x`, `area_offset_y`, `use_text`, `use_user_image`,`spread_type`, `use_digital_image`, `use_predefined_image`, `predefined_cats`,`use_masks`, `masks_cat_id`, `mask_location`, `allow_colorpick`,`text_length`,`allow_text_distortion`,`allow_predefined_colors`,`color_set_id`,  `def_img_behind_text`, `def_img_behind_image`, `def_img_behind_clip`, `allow_save_graphics`, `scale_image`, `use_instagram`, `use_pinterest`, `input_box_type`, `curve_text`, `use_black_white`';
            $this->_replaceInIable('catalog/product_option_aitimage', $newOptionId . ', ' . $sOptions, $oldOptionId);


            $object->getValueInstance()->duplicate($oldOptionId, $newOptionId);
        }

        return $object;
    }

    protected function _replaceInIable($table, $newOptionId, $oldOptionId)
    {
        $table = $this->getTable($table);
        $sql   = 'REPLACE INTO `' . $table . '` '
            . 'SELECT NULL, ' . $newOptionId
            . 'FROM `' . $table . '` WHERE `option_id`=' . $oldOptionId;
        $this->_getWriteAdapter()->query($sql);

    }

    protected function _getOptionDataCond(Mage_Catalog_Model_Product_Option $object, $oldProductId, $newProductId)
    {

        $write = $this->_getWriteAdapter();
        $read  = $this->_getReadAdapter();

        $optionsCond = array();
        $optionsData = array();

        // read and prepare original product options
        $select = $read->select()
            ->from($this->getTable('catalog/product_option'))
            ->where('product_id=?', $oldProductId);
        $query  = $read->query($select);
        while ($row = $query->fetch()) {
            $optionsData[$row['option_id']]               = $row;
            $optionsData[$row['option_id']]['product_id'] = $newProductId;
            unset($optionsData[$row['option_id']]['option_id']);
        }

        // insert options to duplicated product
        foreach ($optionsData as $oId => $data) {
            $write->insert($this->getMainTable(), $data);
            $optionsCond[$oId] = $write->lastInsertId();
        }

        return $optionsCond;

    }
}

