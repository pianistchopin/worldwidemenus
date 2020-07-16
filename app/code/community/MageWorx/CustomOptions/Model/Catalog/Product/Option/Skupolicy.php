<?php
/**
 * MageWorx
 * CustomOptions Extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2016 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_Catalog_Product_Option_Skupolicy extends Mage_Core_Model_Abstract
{
    // sku policy
    const NONE = 0;
    const STANDARD = 1;
    const INDEPENDENT = 2;
    const GROUPED = 3;
    const REPLACEMENT = 4;

    public function applySkuPolicyToCart($items){

        $helper = Mage::helper('mageworx_customoptions');
        $configSkuPolicy = $helper->getOptionSkuPolicyDefault();

        foreach ($items as $item) {
            $skuItem = array();
            //add sku product in array $skuItem
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $skuItem[] = $product->getSku();

            $itemChangesFlag = false;
            $itemRemoveFlag = false;

            $product = $item->getProduct();

            // check data
            if (is_null($product->getHasOptions())) $product->load($product->getId());
            if (!$product->getHasOptions()) continue;

            $productSkuPolicy = $helper->getProductSkuPolicy($product);
            if ($productSkuPolicy == self::NONE) $productSkuPolicy = $configSkuPolicy;
            $post = $helper->getInfoBuyRequest($product);
            if (isset($post['options'])) $options = $post['options']; else $options = false;

            if ($options) {
                foreach ($options as $optionId => $value) {
                    $optionModel = $product->getOptionById($optionId);
                    if (!$optionModel) continue;
                    $optionModel->setProduct($product);

                    $customoptionsIsOnetime = $optionModel->getCustomoptionsIsOnetime();
                    $skuPolicy = $optionModel->getSkuPolicy();
                    if ($skuPolicy == self::NONE || $productSkuPolicy == self::GROUPED) $skuPolicy = $productSkuPolicy;

                    switch ($optionModel->getType()) {
                        case 'drop_down':
                        case 'radio':
                        case 'checkbox':
                        case 'multiple':
                        case 'swatch':
                        case 'multiswatch':
                        case 'hidden':
                            if (is_array($value)) {
                                $optionTypeIds = $value;
                            } else {
                                $optionTypeIds = explode(',', $value);
                            }

                            foreach ($optionTypeIds as $index => $optionTypeId) {
                                if (!$optionTypeId) continue;
                                $valueModel = $optionModel->getValueById($optionTypeId);
                                $sku = $valueModel->getSku();
                                if (!$sku) continue;
                                $productIdBySku = $helper->getProductIdBySku($sku);

                                if ($skuPolicy == self::STANDARD){
                                    $skuItem[] = $sku;
                                } else if ($skuPolicy == self::REPLACEMENT) {
                                    $skuItem[0] = $sku;
                                } else { //INDEPENDENT or GROUPED
                                    if (!$productIdBySku) continue;

                                    // add new product by $productIdBySku
                                    $optionQty = $this->getOptionQty($post, $optionId, $optionTypeId);
                                    $optionTotalQty = ($customoptionsIsOnetime ? $optionQty : $optionQty * $item->getQty());
                                    $request = $this->getRequest($optionTotalQty, $item, $optionModel, $optionTypeId);

                                    if ($helper->isWeightEnabled()) $request->setSkuPolicyWeight($valueModel->getWeight());
                                    if ($helper->isCostEnabled()) $request->setSkuPolicyCost($valueModel->getCost());

                                    $result = $item->getQuote()->addProduct(Mage::getModel('catalog/product')->setStoreId($item->getStoreId())->load($productIdBySku), $request);
                                    if (!is_object($result)) continue;

                                    $this->removeOptionAndOptionValueFromItem($value, $post, $item, $optionId, $index);

                                    // end remove option from item
                                    $itemChangesFlag = true;
                                    if ($skuPolicy == self::GROUPED) $itemRemoveFlag = true;
                                }
                            }
                            break;
                        default:
                            $sku = $optionModel->getSku();
                            $productIdBySku = $helper->getProductIdBySku($sku);
                            if (!$value || !$sku) continue;
                            //if (!$productIdBySku) continue;

                            if ($skuPolicy == self::STANDARD){
                                $skuItem[] = $sku;
                            } elseif ($skuPolicy == self::REPLACEMENT) {
                                $skuItem[0] = $sku;
                            } else {
                                if (!$productIdBySku) continue;

                                // add new product by $productIdBySku
                                $optionTotalQty = ($customoptionsIsOnetime ? 1 : $item->getQty());
                                $request = $this->getRequest($optionTotalQty, $item, $optionModel, $optionTypeId);
                                $result = $item->getQuote()->addProduct(Mage::getModel('catalog/product')->setStoreId($item->getStoreId())->load($productIdBySku));
                                if (!is_object($result)) continue;

                                $this->removeOptionFromItem($post,$optionId,$item);

                                // end remove option from item
                                $itemChangesFlag = true;
                                if ($skuPolicy == self::GROUPED) $itemRemoveFlag = true;
                            }
                            break;
                    }
                }
                $infoBuyRequest = $item->getOptionByCode('info_buyRequest');
                $newSku = implode('-', $skuItem); // toString
                $post['sku_policy_sku'] = $newSku;
                $infoBuyRequest->setValue(serialize($post));
                $item->addOption($infoBuyRequest);
            }
            if ($itemRemoveFlag) {
                $itemsCollection = $item->getQuote()->getItemsCollection();
                foreach ($itemsCollection as $key => $itm) {
                    if ($itm === $item) $itemsCollection->removeItemByKey($key);
                }
            } elseif ($itemChangesFlag) {
                // update item
                $quote = $item->getQuote();
                $itemsCollection = $quote->getItemsCollection();
                $itemRemoveFlag = false;
                foreach ($itemsCollection as $key => $itm) {
                    if ($itm->getProductId() == $item->getProductId() && $itm !== $item) {

                        // get current $item - $options
                        if (isset($post['options'])) $options = $post['options']; else $options = false;

                        // get other $itm - $optns
                        $prdct = $itm->getProduct();
                        // if bad magento))
                        if (is_null($prdct->getHasOptions())) $prdct->load($prdct->getId());
                        $optns = false;
                        if ($prdct->getHasOptions()) {
                            $post = $helper->getInfoBuyRequest($prdct);
                            if (isset($post['options'])) $optns = $post['options'];
                        }

                        // compare options
                        if ($optns === $options) {
                            $itm->setQty($itm->getQty() + $item->getQty());
                            $itemRemoveFlag = true;
                        }

                    }
                    if ($itemRemoveFlag && $itm === $item) $itemsCollection->removeItemByKey($key);
                }
            }
        }
    }


    public function applySkuPolicyToOrder($order, $orderItems, $tablePrefix, $connection){
        $helper = Mage::helper('mageworx_customoptions');
        $configSkuPolicy = $helper->getOptionSkuPolicyDefault();

        $orderTotalQtyOrdered = $order->getTotalQtyOrdered();
        $orderChangesFlag = false;
        $invoiceChangesFlag = false;

        foreach ($orderItems as $orderItem) {
            $skuOrderItem = array();
            $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
            $skuItem[] = $product->getSku();

            $orderItemChangesFlag = false;
            $orderItemRemoveFlag = false;
            $productOptions = $orderItem->getProductOptions();
            if (!isset($productOptions['options'])) continue;
            $product = Mage::getModel('catalog/product')->setStoreId($orderItem->getStoreId())->load($orderItem->getProductId());

            $productSkuPolicy = $helper->getProductSkuPolicy($product);
            if ($productSkuPolicy == self::NONE) $productSkuPolicy = $configSkuPolicy;

            $store = Mage::app()->getStore($orderItem->getStoreId());
            $updateProductOptions = $productOptions;

            $reduce = array(
                'weight' => 0,
                'price' => 0,
                'base_price' => 0,
                'total_price' => 0,
                'base_total_price' => 0,
                'tax_amount' => 0,
                'base_tax_amount' => 0,
                'cost' => 0,
            );

            $isInvoiced = false;
            foreach ($productOptions['options'] as $index => $option) {
                $optionId = $option['option_id'];
                if (!$optionId) continue;
                $optionModel = $product->getOptionById($optionId);
                if (!$optionModel) continue;
                $optionModel->setProduct($product);
                $customoptionsIsOnetime = $optionModel->getCustomoptionsIsOnetime();
                $skuPolicy = $optionModel->getSkuPolicy();

                if ($skuPolicy == self::NONE || $productSkuPolicy == self::GROUPED) $skuPolicy = $productSkuPolicy;

                if ($optionModel->getGroupByType($option['option_type']) == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                    $optionTypeIds = explode(',', $option['option_value']);
                    foreach ($optionTypeIds as $optionTypeId) {
                        if (!$optionTypeId) continue;
                        $valueModel = $optionModel->getValueById($optionTypeId);
                        $sku = $valueModel->getSku();

                        if ($skuPolicy == self::STANDARD){
                            $skuOrderItem[] = $sku;
                        } else if ($skuPolicy == self::REPLACEMENT) {
                            $skuOrderItem[0] = $sku;
                            $orderChangesFlag = true;
                        } else { // Independent, Grouped

                            list($reduce, $orderTotalQtyOrdered, $isInvoiced) = $this->insertNewOrderItem($sku, $option['option_type'], $orderItem, $valueModel, $productOptions, $store, $optionId, $optionTypeId, $connection, $tablePrefix, $reduce, $orderTotalQtyOrdered, $customoptionsIsOnetime, $product, !$orderItemChangesFlag && $skuPolicy == self::GROUPED);
                            if (isset($updateProductOptions['options'][$index])) unset($updateProductOptions['options'][$index]);
                            if (isset($updateProductOptions['info_buyRequest']['options'][$optionId])) unset($updateProductOptions['info_buyRequest']['options'][$optionId]);
                            if ($skuPolicy == self::GROUPED) $orderItemRemoveFlag = true;
                            $orderChangesFlag = true;
                        }
                        $orderItemChangesFlag = true;
                    }
                } else {
                    // text, area, file, date
                    $sku = $optionModel->getSku();
                    if ($sku) {
                        if ($skuPolicy == self::STANDARD){
                            $skuOrderItem[] = $sku;
                        } else if ($skuPolicy == self::REPLACEMENT) {
                            $skuOrderItem[0] = $sku;
                            $orderChangesFlag = true;
                        } else { // Independent, Grouped
                            list($reduce, $orderTotalQtyOrdered, $isInvoiced) = $this->insertNewOrderItem($sku, $option['option_type'], $orderItem, $optionModel, $productOptions, $store, $optionId, 0, $connection, $tablePrefix, $reduce, $orderTotalQtyOrdered, $customoptionsIsOnetime, $product, !$orderItemChangesFlag && $skuPolicy == self::GROUPED);
                            if (isset($updateProductOptions['options'][$index])) unset($updateProductOptions['options'][$index]);
                            if (isset($updateProductOptions['info_buyRequest']['options'][$optionId])) unset($updateProductOptions['info_buyRequest']['options'][$optionId]);
                            if ($skuPolicy == self::GROUPED) $orderItemRemoveFlag = true;
                            $orderChangesFlag = true;
                        }
                        $orderItemChangesFlag = true;
                    }
                }
            }

            if ($isInvoiced) $invoiceChangesFlag = true;

            if ($orderItemRemoveFlag) {
                // remove order_item
                $connection->delete($tablePrefix . 'sales_flat_order_item', 'item_id = ' . $orderItem->getId());
                if ($isInvoiced) $connection->delete($tablePrefix . 'sales_flat_invoice_item', 'order_item_id = ' . $orderItem->getId());

                $orderTotalQtyOrdered -= $orderItem->getQtyOrdered();
            } else {
                // update original order_item
                if ($orderItemChangesFlag) {
                    $updateItemData = array();

                    // get simple $productSku
                    $productSku = $product->getSku();
                    // get correct configurable sku
                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                        $childrenItems = $orderItem->getChildrenItems();
                        if ($childrenItems) {
                            foreach ($childrenItems as $childrenItem) {
                                $productSku = $childrenItem->getSku();
                            }
                        }
                    }
                    // add to $productSku - options sku
                    if (isset($updateProductOptions['info_buyRequest'])) {
                        $_buyRequest = new Varien_Object($updateProductOptions['info_buyRequest']);
                        $productInstance = $product->getTypeInstance(true);
                        if (method_exists($productInstance, 'processConfiguration')) {
                            $productInstance->processConfiguration($_buyRequest, $product);
                            $productSku = $productInstance->getOptionSku($product, $productSku);
                        } else {
                            $productInstance->prepareForCart($_buyRequest, $product);
                            $productSku = $productInstance->getSku($product);
                        }
                    }

                    $skuReplacement = implode('-', $skuOrderItem);

                    $updateItemData['sku'] = ($skuReplacement ? $skuReplacement : $productSku);

                    if (isset($productOptions['info_buyRequest'])) $updateProductOptions['originalInfoBuyRequest'] = $productOptions['info_buyRequest'];

                    $updateItemData['product_options'] = serialize($updateProductOptions);

                    if ($reduce['weight'] > 0) $updateItemData['weight'] = $orderItem->getWeight() - $reduce['weight'];
                    if ($reduce['cost'] > 0) $updateItemData['base_cost'] = $orderItem->getBaseCost() - $reduce['cost'];

                    if ($reduce['price'] > 0) $updateItemData['price'] = $orderItem->getPrice() - ($reduce['price'] / $orderItem->getQtyOrdered());
                    if ($reduce['base_price'] > 0) $updateItemData['base_price'] = $orderItem->getBasePrice() - ($reduce['base_price'] / $orderItem->getQtyOrdered());

                    if ($reduce['price'] > 0) $updateItemData['original_price'] = $orderItem->getOriginalPrice() - ($reduce['price'] / $orderItem->getQtyOrdered());
                    if ($reduce['base_price'] > 0) $updateItemData['base_original_price'] = $orderItem->getBaseOriginalPrice() - ($reduce['base_price'] / $orderItem->getQtyOrdered());

                    if ($reduce['total_price'] > 0) $updateItemData['row_total'] = $orderItem->getRowTotal() - $reduce['total_price'];
                    if ($reduce['base_total_price'] > 0) $updateItemData['base_row_total'] = $orderItem->getBaseRowTotal() - $reduce['base_total_price'];

                    if ($reduce['price'] > 0) $updateItemData['price_incl_tax'] = $orderItem->getPriceInclTax() - ($reduce['price'] / $orderItem->getQtyOrdered()) - ($reduce['tax_amount'] > 0 ? $reduce['tax_amount'] / $orderItem->getQtyOrdered() : 0);
                    if ($reduce['base_price'] > 0) $updateItemData['base_price_incl_tax'] = $orderItem->getBasePriceInclTax() - ($reduce['base_price'] / $orderItem->getQtyOrdered()) - ($reduce['base_tax_amount'] > 0 ? $reduce['base_tax_amount'] / $orderItem->getQtyOrdered() : 0);

                    if ($reduce['total_price'] > 0) $updateItemData['row_total_incl_tax'] = $orderItem->getRowTotalInclTax() - $reduce['total_price'] - $reduce['tax_amount'];
                    if ($reduce['base_total_price'] > 0) $updateItemData['base_row_total_incl_tax'] = $orderItem->getBaseRowTotalInclTax() - $reduce['base_total_price'] - $reduce['base_tax_amount'];

                    if ($reduce['tax_amount'] > 0) $updateItemData['tax_amount'] = $orderItem->getTaxAmount() - $reduce['tax_amount'];
                    if ($reduce['base_tax_amount'] > 0) $updateItemData['base_tax_amount'] = $orderItem->getBaseTaxAmount() - $reduce['base_tax_amount'];

                    if ($isInvoiced) {
                        if ($reduce['total_price'] > 0) $updateItemData['row_invoiced'] = $orderItem->getRowInvoiced() - $reduce['total_price'];
                        if ($reduce['base_total_price'] > 0) $updateItemData['base_row_invoiced'] = $orderItem->getBaseRowInvoiced() - $reduce['base_total_price'];
                        if ($reduce['tax_amount'] > 0) $updateItemData['tax_invoiced'] = $orderItem->getTaxInvoiced() - $reduce['tax_amount'];
                        if ($reduce['base_tax_amount'] > 0) $updateItemData['base_tax_invoiced'] = $orderItem->getBaseTaxInvoiced() - $reduce['base_tax_amount'];
                    }

                    $connection->update($tablePrefix . 'sales_flat_order_item', $updateItemData, 'item_id = ' . $orderItem->getId());

                    if ($isInvoiced) {
                        $updateItemData = array();
                        $updateItemData['sku'] = ($skuReplacement ? $skuReplacement : $productSku);
                        if ($reduce['price'] > 0) $updateItemData['price'] = $orderItem->getPrice() - ($reduce['price'] / $orderItem->getQtyOrdered());
                        if ($reduce['base_price'] > 0) $updateItemData['base_price'] = $orderItem->getBasePrice() - ($reduce['base_price'] / $orderItem->getQtyOrdered());
                        if ($reduce['total_price'] > 0) $updateItemData['row_total'] = $orderItem->getRowTotal() - $reduce['total_price'];
                        if ($reduce['base_total_price'] > 0) $updateItemData['base_row_total'] = $orderItem->getBaseRowTotal() - $reduce['base_total_price'];
                        if ($reduce['price'] > 0) $updateItemData['price_incl_tax'] = $orderItem->getPriceInclTax() - ($reduce['price'] / $orderItem->getQtyOrdered()) - ($reduce['tax_amount'] > 0 ? $reduce['tax_amount'] / $orderItem->getQtyOrdered() : 0);
                        if ($reduce['base_price'] > 0) $updateItemData['base_price_incl_tax'] = $orderItem->getBasePriceInclTax() - ($reduce['base_price'] / $orderItem->getQtyOrdered()) - ($reduce['base_tax_amount'] > 0 ? $reduce['base_tax_amount'] / $orderItem->getQtyOrdered() : 0);
                        if ($reduce['total_price'] > 0) $updateItemData['row_total_incl_tax'] = $orderItem->getRowTotalInclTax() - $reduce['total_price'] - $reduce['tax_amount'];
                        if ($reduce['base_total_price'] > 0) $updateItemData['base_row_total_incl_tax'] = $orderItem->getBaseRowTotalInclTax() - $reduce['base_total_price'] - $reduce['base_tax_amount'];
                        $connection->update($tablePrefix . 'sales_flat_invoice_item', $updateItemData, 'order_item_id = ' . $orderItem->getId());
                    }
                }
            }
        }
        //update  sales_flat_order total_qty_ordered
        $orderId = $order->getId();
        if ($invoiceChangesFlag) $connection->update($tablePrefix . 'sales_flat_invoice', array('total_qty' => $orderTotalQtyOrdered), 'order_id = ' . $orderId);
        if ($orderChangesFlag) {
            $connection->update($tablePrefix . 'sales_flat_order', array('total_qty_ordered' => $orderTotalQtyOrdered), 'entity_id = ' . $orderId);
            // reload order to correct e-mail send
            $order->unsetData()->load($orderId);
        }
    }

    protected function getOptionQty($post, $optionId, $optionTypeId){
        if (isset($post['options_' . $optionId . '_qty'])) {
            $optionQty = intval($post['options_' . $optionId . '_qty']);
        } elseif (isset($post['options_' . $optionId . '_' . $optionTypeId . '_qty'])) {
            $optionQty = intval($post['options_' . $optionId . '_' . $optionTypeId . '_qty']);
        } else {
            $optionQty = 1;
        }

        return $optionQty;
    }

    protected function getRequest($optionTotalQty, $item, $optionModel, $optionTypeId){
        $request = new Varien_Object();
        $request->setQty($optionTotalQty);

        $optionResourceModel = $optionModel->getResource();
        $request->setSkuPolicyName($optionResourceModel->getValueTitle($optionTypeId, $item->getStoreId()));

        return $request;
    }

    protected function removeOptionAndOptionValueFromItem($value,$post,$item,$optionId,$index){
        if (is_array($value)) {
            unset($value[$index]);
        } else {
            $value = '';
        }
        if ($value) {
            // if remove optionValue
            $post['options'][$optionId] = $value;
            $itemOption = $item->getOptionByCode('option_' . $optionId);
            $itemOption->setValue((is_array($value) ? implode(',', $value) : $value));
            $item->addOption($itemOption);
        } else {
            $this->removeOptionFromItem($post,$optionId,$item);
        }
    }

    protected function removeOptionFromItem($post,$optionId,$item){
        unset($post['options'][$optionId]);
        $item->removeOption('option_' . $optionId);

        $itemOptionIds = $item->getOptionByCode('option_ids');
        $optionIds = $itemOptionIds->getValue();
        if ($optionIds) {
            $optionIds = explode(',', $optionIds);
            $i = array_search($optionId, $optionIds);
            if ($i !== false) unset($optionIds[$i]);
            if ($optionIds) {
                $optionIds = implode(',', $optionIds);
            }
        }
        if ($optionIds) {
            $itemOptionIds->setValue($optionIds);
            $item->addOption($itemOptionIds);
        } else {
            $item->removeOption('option_ids');
        }
    }
}
