<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_Catalog_Product_Option_Type_Select extends Mage_Catalog_Model_Product_Option_Type_Select
{

    public function getOptionPrice($valueIds, $basePrice, $qty = 1)
    {
        $option = $this->getOption();
        $product = $option->getProduct();
        if (is_null($product)) {
            return parent::getOptionPrice($valueIds, $basePrice);
        }
        $helper = Mage::helper('mageworx_customoptions');
        $result = 0;

        $optionQtyArr = $this->_getOptionQty();

        if (!$this->_isSingleSelection()) {
            $valueIds = explode(',', $valueIds);
            foreach ($valueIds as $valueId) {
                if ($value = $option->getValueById($valueId)) {
                    $optionQty = (!is_array($optionQtyArr) ? $optionQtyArr : $optionQtyArr[$valueId]);
                    if ($option->getCustomoptionsIsOnetime()) {
                        $optionTotalQty = $optionQty;
                    } else {
                        $optionTotalQty = $optionQty * $qty;
                    }
                    // calculate option price
                    $price = $helper->getOptionPriceByQty($value, $optionTotalQty);
                    if ($price != 0) {
                        $price = $price / $qty;
                    }
                    $result += $price;
                } else {
                    if ($this->getListener()) {
                        $this->getListener()
                            ->setHasError(true)
                            ->setMessage(
                                Mage::helper('catalog')
                                    ->__('Some of the products below do not have all the required options. Please remove them and add again with all the required options.')
                            );
                        break;
                    }
                }
            }
        } elseif ($this->_isSingleSelection()) {
            $optionQty = $optionQtyArr;
            if ($value = $option->getValueById($valueIds)) {
                if ($option->getCustomoptionsIsOnetime()) {
                    $optionTotalQty = $optionQty;
                } else {
                    $optionTotalQty = $optionQty * $qty;
                }
                // calculate option price
                $price = $helper->getOptionPriceByQty($value, $optionTotalQty);
                if ($price != 0) {
                    $price = $price / $qty;
                }
                $result += $price;
            } else {
                if ($this->getListener()) {
                    $this->getListener()
                        ->setHasError(true)
                        ->setMessage(
                            Mage::helper('catalog')
                                ->__('Some of the products below do not have all the required options. Please remove them and add again with all the required options.')
                        );
                }
            }
        }

        return $result;
    }

    protected function _isSingleSelection()
    {
        $_single = array(
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN,
            Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO,
            MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH
        );

        return in_array($this->getOption()->getType(), $_single);
    }

    protected function _getOptionQty()
    {
        $option = $this->getOption();
        $product = $option->getProduct();
        $helper = Mage::helper('mageworx_customoptions');
        $post = $helper->getInfoBuyRequest($product);
        $optionQtyArr = 1;

        switch ($option->getType()) {
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE:
            case MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_MULTISWATCH:
            case MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_HIDDEN:
                if (isset($post['options'][$option->getId()])) {
                    $optionQtyArr = array();
                    foreach ($option->getValues() as $key=>$itemV) {
                        $optionQty = $helper->getPostCustomoptionQty($product, $option, $itemV, $post);
                        $optionQtyArr[$itemV->getOptionTypeId()] = $optionQty;
                    }
                }
                break;
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN:
            case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
            case MageWorx_CustomOptions_Model_Catalog_Product_Option::OPTION_TYPE_SWATCH:
                $optionQtyArr = $helper->getPostCustomoptionQty($product, $option, null, $post);
                break;
        }

        return $optionQtyArr;
    }

    /**
     * Return SKU for selected option
     *
     * @param string $optionValue Prepared for cart option value
     * @param string $skuDelimiter Delimiter for Sku parts
     * @return string
     */
    public function getOptionSku($optionValue, $skuDelimiter)
    {
        $option = $this->getOption();
        if (!$this->_isSingleSelection()) {
            $skus = array();
            foreach(explode(',', $optionValue) as $value) {
                if ($optionSku = $option->getValueById($value)) {
                    if ($optionSku->getSku()) {
                        $skus[] = $optionSku->getSku();
                    }
                } else {
                    if ($this->getListener()) {
                        $this->_setWrongConfigurationError();
                        break;
                    }
                }
            }
            $result = implode($skuDelimiter, $skus);
        } elseif ($this->_isSingleSelection()) {
            if ($result = $option->getValueById($optionValue)) {
                return $result->getSku();
            } else {
                if ($this->getListener()) {
                    $this->_setWrongConfigurationError();
                }
                return '';
            }
        } else {
            $result = parent::getOptionSku($optionValue, $skuDelimiter);
        }

        return $result;
    }

    /**
     * Set wrong configuration error for listener
     */
    protected function _setWrongConfigurationError()
    {
        $this->getListener()
            ->setHasError(true)
            ->setMessage(
                $this->_getWrongConfigurationMessage()
            );
    }
}