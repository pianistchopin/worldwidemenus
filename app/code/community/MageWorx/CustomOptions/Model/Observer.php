<?php

/**
 * MageWorx
 * CustomOptions Extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Model_Observer
{

    // replace main product image in checkout/cart
    public function toHtmlBlockFrontBefore($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isEnabled() || !$helper->isImageModeEnabled()) return $this;
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Checkout_Block_Cart_Item_Renderer) {
            $product = $block->getProduct();
            if ($product) {
                if (is_null($product->getHasOptions())) $product->load($product->getId());
                if (($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual' || $product->getTypeId() == 'downloadable') && $product->getHasOptions()) {
                    $post = $helper->getInfoBuyRequest($product);
                    if (isset($post['options'])) $options = $post['options']; else $options = array();
                    if ($options) {
                        foreach ($options as $optionId => $value) {
                            $optionModel = $product->getOptionById($optionId);
                            if (!$optionModel) continue;
                            $optionModel->setProduct($product);
                            // if replace image mode setting
                            if ($optionModel->getImageMode() == 2) {
                                switch ($optionModel->getType()) {
                                    case 'drop_down':
                                    case 'radio':
                                    case 'checkbox':
                                    case 'multiple':
                                    case 'swatch':
                                    case 'multiswatch':
                                        if (is_array($value)) {
                                            $optionTypeIds = $value;
                                        } else {
                                            $optionTypeIds = explode(',', $value);
                                        }
                                        foreach ($optionTypeIds as $optionTypeId) {
                                            if (!$optionTypeId) continue;
                                            $images = $optionModel->getOptionValueImages($optionTypeId);
                                            if ($images) {
                                                foreach ($images as $index => $image) {
                                                    // file
                                                    if ($image['source'] == 1 && (!$optionModel->getExcludeFirstImage() || ($optionModel->getExcludeFirstImage() && $index > 0))) {
                                                        // replace main image
                                                        $block->overrideProductThumbnail(Mage::getModel('mageworx_customoptions/catalog_product_option_image')->init($image['image_file']));

                                                        return $this;
                                                    }
                                                }
                                            }
                                        }
                                }
                            }
                        }
                    }
                }
            }
            $block->overrideProductThumbnail(null);
        }

        return $this;
    }

    // add "Starting at" Price Prefix (front)
    public function toHtmlBlockFrontAfter($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isEnabled() || !$helper->isPricePrefixEnabled()) return $this;
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Catalog_Block_Product_Price) {
            $transport = $observer->getEvent()->getTransport();
            if (Mage::app()->getRequest()->getControllerName() != 'product' && Mage::app()->getRequest()->getActionName() != 'configure') {
                $product = $block->getProduct();
                if ($product && $transport) {
                    if (is_null($product->getHasOptions())) $product->load($product->getId());
                    if (($product->getHasOptions() && $product->getFinalPrice() != $product->getMaxPrice()) || ($product->getRequiredOptions() && $product->getAbsolutePrice())) {
                        $html = trim($transport->getHtml());
                        $htmlArr = explode('</div>', $html, 2);
                        if (count($htmlArr) == 2) {
                            list($html1, $html2) = $htmlArr;
                            $htmlArr1 = explode('<', $html1);
                            if (count($htmlArr1) == 6) {
                                $htmlArr1[2] .= '<span class="price-label">' . $helper->__('Starting at') . '</span> ';
                                $html1 = implode('<', $htmlArr1);
                                $transport->setHtml($html1 . '</div>' . $html2);
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    // show admin only options
    public function toHtmlBlockAdminBefore($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isEnabled()) return $this;
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Items_Column_Default) {
            $item = $block->getItem();
            if (!$item) return $this;
            $options = $item->getProductOptions();
            if (!isset($options['admin_options'])) return $this;

            if (isset($options['options']) && is_array($options['options'])) {
                $options['options'] = array_merge($options['options'], $options['admin_options']);
            } else {
                $options['options'] = $options['admin_options'];
            }
            unset($options['admin_options']);
            $item->setProductOptions($options);
        }

        return $this;
    }

    // ckeckout/cart
    public function checkQuoteItemQtyAndCustomerGroup($observer)
    {
        if (!Mage::helper('mageworx_customoptions')->isEnabled()) return $this;
        $quoteItem = $observer->getEvent()->getItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        //|| $quoteItem->getQuote()->getIsSuperMode()
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote()) {
            return $this;
        }

        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isInventoryEnabled() && !$helper->isCustomerGroupsEnabled()) return $this;

        // product Qty
        $qty = 0;
        // if update cart -> cart[182][qty]
        $quoteItemId = $quoteItem->getId();
        if ($quoteItemId > 0) {
            $cartPost = Mage::app()->getRequest()->getParam('cart', false);
            if ($cartPost && isset($cartPost[$quoteItemId]['qty'])) $qty = $cartPost[$quoteItemId]['qty'];
        }

        // standart add to cart
        if (!$qty) $qty = $quoteItem->getQty();

        if (!$qty) $qty = Mage::app()->getRequest()->getParam('qty', false);

        // get correctly options
        $options = false;
        $post = Mage::app()->getRequest()->getParams();

        if (isset($post['id'])) {
            // if update quote item 
            if ($post['id'] == $quoteItemId) {
                // if quote item edited:
                if (isset($post['options'])) $options = $post['options'];
                $qty = Mage::app()->getRequest()->getParam('qty', false);
            } else {
                return $this;
            }
        } else {
            $product = $quoteItem->getProduct();
            if (is_null($product->getHasOptions())) $product->load($product->getId());
            if (!$product->getHasOptions()) return $this;
            $post = $helper->getInfoBuyRequest($product);
            if (isset($post['options'])) $options = $post['options'];
        }

        if (!$options) return $this;
        $customerGroupId = $helper->getCustomerGroupId();
        $outOfStockOptions = $helper->getOutOfStockOptions();

        $quote = $quoteItem->getQuote();

        $arrayExtraValues = array(); // check if total qty values with extra 'i' are less then parent qty value
        foreach ($options as $optionId => $option) {
            $productOption = Mage::getModel('catalog/product_option')->load($optionId);

            // check Options Customer Group
            if ($helper->isCustomerGroupsEnabled()) {
                $groups = $productOption->getCustomerGroups();
                if ($groups !== '' && !in_array($customerGroupId, explode(',', $groups))) {
                    $fullMessage = $helper->__('Some options are not available for your customer group. Please, edit product "%s"', $quoteItem->getProduct()->getName());
                    $message = $helper->__('Some options are not available for your customer group');

                    $quoteItem->setHasError(true)->setMessage($message);
                    if ($quoteItem->getParentItem()) {
                        $quoteItem->getParentItem()->setMessage($message);
                    }
                    $quote->setHasError(true)->addMessage($fullMessage, 'options');

                    return $this;
                    break;
                }
            }

            // check Options Inventory and if not Backorders
            if ($helper->isInventoryEnabled()) {

                $optionType = $productOption->getType();
                if ($productOption->getGroupByType($optionType) != Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) continue;
                if (!is_array($option)) $option = array($option);

                foreach ($option as $optionTypeId) {
                    if (!$optionTypeId) continue;
                    $row = $productOption->getOptionValue($optionTypeId);
                    if (!$row) continue;
                    $value = new Varien_Object($row);

                    $extra = $row['extra'];

                    list($customoptionsQty, $backorders) = $helper->getCustomoptionsQty(isset($row['customoptions_qty']) ? $row['customoptions_qty'] : '', isset($row['sku']) ? $row['sku'] : '', $quoteItem->getProductId(), isset($row['extra']) ? $row['extra'] : '', $value, $quoteItem->getId(), $quote, true, true);

                    if ($backorders === false && $outOfStockOptions > 1) {
                        $backorders = $outOfStockOptions - 1;
                    }
                    // Allow Backorders (by SKU or APO Settings)
                    if ($backorders == 1) {
                        continue;
                    }
                    if ($customoptionsQty === '') {
                        continue;
                    }

                    $optionQty = $helper->getPostCustomoptionQty($quoteItem->getProduct(), $productOption, $value, $post);
                    $optionTotalQty = ($productOption->getCustomoptionsIsOnetime() ? $optionQty : $optionQty * $qty);

                    // is null if add new product (edit) (admin) -> correction inventory
                    if (is_null($quoteItem->getId()) && Mage::app()->getStore()->isAdmin()) {
                        $customoptionsQty += $optionTotalQty;
                    }

                    if ($customoptionsQty < $optionTotalQty) {
                        $productOptionResource = $productOption->getResource();
                        $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', trim($quoteItem->getProduct()->getName() . ' / '
                            . $productOptionResource->getTitle($optionId, $quoteItem->getStoreId()) . ' - '
                            . $productOptionResource->getValueTitle($optionTypeId, $quoteItem->getStoreId())));

                        if ($backorders == 2) {
                            if ($customoptionsQty < 0) $customoptionsQty = 0;
                            $message .= ' ' . $helper->__('%s of the items will be backordered.', $optionTotalQty - $customoptionsQty);
                        } else {
                            $quoteItem->setHasError(true);
                        }
                        $quoteItem->setMessage($message);
                        if ($quoteItem->getParentItem()) {
                            $quoteItem->getParentItem()->setMessage($message);
                        }

                        if ($backorders != 2) {
                            $quote->setHasError(true)->addMessage($message, 'qty');
                        }

                    }

                    // check if total qty values with extra 'i' are less then parent qty value
                    if (substr($extra, 0, 1) == 'i') {
                        $productOptionResource = $productOption->getResource();
                        $optionName = $productOptionResource->getTitle($optionId, $quoteItem->getStoreId()) . ' - ' . $productOptionResource->getValueTitle($optionTypeId, $quoteItem->getStoreId());
                        $arrayExtraValues[$extra] = array(
                            'names' => array_unique(array_merge(isset($arrayExtraValues[$extra]['names']) ? $arrayExtraValues[$extra]['names'] : array(), array($optionName))),
                            'total_qty' => ($arrayExtraValues[$extra]['total_qty']) ? $arrayExtraValues[$extra]['total_qty'] + $optionTotalQty : $optionTotalQty,
                            'parent_id' => substr($extra, 1),
                            'option_id' => $optionId,
                        );
                    }

                }

            }
        }

        // check if total qty values with extra 'i' are less then parent qty value
        foreach ($arrayExtraValues as $extra => $value) {
            $parentRow = $helper->getRowValueByIGI($value['parent_id'], $quoteItem->getProduct()->getId());
            $parentPostQtyValue = $post['options_' . $parentRow['option_id'] . '_' . $parentRow['option_type_id'] . '_qty'];
            $parentPostQtyOption = $post['options_' . $parentRow['option_id'] . '_qty'];
            $parentPostQty = ($parentPostQtyValue) ? $parentPostQtyValue : $parentPostQtyOption;

            $totalQty = $value['total_qty'];
            $optionsNames = $value['names'];

            if ($parentPostQty && $value['option_id'] != $parentRow['option_id']) {
                $totalQty += $parentPostQty;
                $optionsNames = array_unique(array_merge($value['names'], array($parentRow['option_title'] . ' - ' . $parentRow['value_title'])));
            }

            if ($totalQty > $parentRow['customoptions_qty']) {
                $quoteItem->setHasError(true);
                foreach ($optionsNames as $name) {
                    $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', trim($quoteItem->getProduct()->getName() . ' / ' . $name));
                    $quoteItem->setMessage($message);
                }
            }
        }

        return $this;
    }

    // before create order -> setCustomOptionsDetails
    public function convertQuoteItemToOrderItem($observer)
    {
        if (!Mage::helper('mageworx_customoptions')->isEnabled()) return $this;
        $orderItem = $observer->getEvent()->getOrderItem();
        $item = $observer->getEvent()->getItem();
        $product = $item->getProduct();
        // if bad magento))
        if (is_null($product->getHasOptions())) $product->load($product->getId());
        if (!$product->getHasOptions()) return $this;

        // multiplier - to order: 3 x Red
        Mage::helper('mageworx_customoptions/product_configuration')->setCustomOptionsDetails($item);
        $quoteOptions = $product->getTypeInstance(true)->getOrderOptions($product);
        $orderOptions = $orderItem->getProductOptions();
        if (!is_array($orderOptions)) return $this;


        // htmlspecialchars_decode titles
        if (isset($quoteOptions['options']) && is_array($quoteOptions['options'])) {
            foreach ($quoteOptions['options'] as $key => $op) {
                if (isset($op['label'])) $quoteOptions['options'][$key]['label'] = htmlspecialchars_decode($op['label']);
                if (isset($op['value'])) $quoteOptions['options'][$key]['value'] = htmlspecialchars_decode($op['value']);
                if (isset($op['print_value'])) unset($quoteOptions['options'][$key]['print_value']);
            }
            $orderOptions['options'] = $quoteOptions['options'];
        }
        $orderItem->setProductOptions($orderOptions);

        return $this;
    }

    public function orderSaveAfter($observer)
    {
        if (Mage::app()->getRequest()->getControllerName() == 'multishipping') $this->quoteSubmitSuccess($observer);
    }

    // after create order - subtract inventory + sku policy + admin only options
    public function quoteSubmitSuccess($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isEnabled()) return $this;

        $order = $observer->getEvent()->getOrder();
        $orderItems = $order->getAllItems();

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();

        foreach ($orderItems as $orderItem) {
            // options sku -> reduce product inventory or options inventory
            $productOptions = $orderItem->getProductOptions();
            if (!isset($productOptions['options'])) continue;

            $orderItemChangesFlag = false;

            $qty = $orderItem->getQtyOrdered();
            if ($qty == $orderItem->getQtyInvoiced()) $isInvoiced = true; else $isInvoiced = false;

            foreach ($productOptions['options'] as $index => $option) {
                $optionId = $option['option_id'];
                if (!$optionId) continue;

                $product = Mage::getModel('catalog/product')->setStoreId($orderItem->getStoreId())->load($orderItem->getProductId());

                $optionModel = $product->getOptionById($optionId);
                if (!$optionModel) continue;
                $optionModel->setProduct($product);

                // subtract inventory 
                if ($helper->isInventoryEnabled() && $optionModel->getGroupByType($option['option_type']) == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                    $customoptionsIsOnetime = $optionModel->getCustomoptionsIsOnetime();
                    $optionTypeIds = explode(',', $option['option_value']);

                    foreach ($optionTypeIds as $optionTypeId) {
                        $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                        $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                        $sku = $productOptionValueModel->getSku();

                        if ($customoptionsQty === '' && $sku == '') continue;

                        $optionQty = $helper->getPostCustomoptionQty($product, $optionModel, $productOptionValueModel, $productOptions['info_buyRequest']);
                        $optionTotalQty = ($customoptionsIsOnetime ? $optionQty : $optionQty * $qty);

                        // check link inventory to other option by IGI
                        $extra = $productOptionValueModel->getExtra();
                        if ($extra != '' && strlen($extra) >= 2) {
                            if (substr($extra, 0, 1) == 'i') {
                                $IGI = substr($extra, 1);
                                $row = $helper->getRowValueByIGI($IGI, $orderItem->getProductId());

                                if ($row) {

                                    $optionTypeId = $row['option_type_id'];
                                    $sku = $row['sku'];
                                    $customoptionsQty = $row['customoptions_qty'];
                                }
                            }
                        }

                        if ($customoptionsQty !== null && $customoptionsQty !== '') {
                            $customoptionsQty = $customoptionsQty - $optionTotalQty;
                            // model 'catalog/product_option_value' - do not use!
                            $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty'=>$customoptionsQty), 'option_type_id = '.$optionTypeId);
                        }

                        if ($sku !== '') {
                            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                            if (isset($product) && $product && $product->getId() > 0) {
                                $item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                                if ($item->getUseConfigManageStock()) {
                                    $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_ITEM . 'manage_stock');
                                } else {
                                    $manageStock = $item->getManageStock();
                                }
                                if ($manageStock) {
                                    $item->subtractQty($optionTotalQty);
                                    $item->save();
                                }
                            }
                        }

                    }
                }

                // set admin only options
                if ($optionModel->getViewMode() == 4) {
                    $productOptions['admin_options'][] = $option;
                    unset($productOptions['options'][$index]);
                    $orderItemChangesFlag = true;
                }
            }

            // save order item
            if ($orderItemChangesFlag) {
                $orderItem->setProductOptions($productOptions)->save();

                // save invoice item
                if ($isInvoiced) {
                    $invoices = $order->getInvoiceCollection();
                    if ($invoices) {
                        foreach ($invoices as $invoice) {
                            foreach ($invoice->getItemsCollection() as $invoiceItem) {
                                if ($invoiceItem->getOrderItemId() == $orderItem->getId()) {
                                    $invoiceItem->setData('product_options', serialize($productOptions))->save();
                                    break;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        // OptionSkuPolicy
        if ($helper->isOptionSkuPolicyEnabled()) {
            Mage::getModel('mageworx_customoptions/catalog_product_option_skupolicy')->applySkuPolicyToOrder($order, $orderItems, $tablePrefix, $connection);
        }

        return $this;
    }

    public function insertNewOrderItem($sku, $optionType, $orderItem, $optionModel, $productOptions, $store, $optionId, $optionTypeId, $connection, $tablePrefix, $reduce, $orderTotalQtyOrdered, $customoptionsIsOnetime, $product, $isFirstGroupedInsert)
    {
        $finalProductPrice = $product->getFinalPrice();

        $helper = Mage::helper('mageworx_customoptions');

        $isInvoiced = false;
        $productBySku = Mage::getModel('catalog/product')->setStoreId($store->getId())->loadByAttribute('sku', $sku);
        // insert new order item
        $itemData = array();
        if ($productBySku && $productBySku->getId() > 0) {
            $productId = $productBySku->getId();
            $productName = $productBySku->getName();
        } else {
            $productId = 0; //$orderItem->getProductId();
            $productName = $optionModel->getTitle();
        }
        //$productName .= ' ' . $orderItem->getName();

        $itemData['product_id'] = $productId;
        $itemData['name'] = $productName;

        $itemData['order_id'] = $orderItem->getOrderId();
        $itemData['quote_item_id'] = $orderItem->getQuoteItemId();
        $itemData['store_id'] = $orderItem->getStoreId();
        $itemData['created_at'] = $orderItem->getCreatedAt();
        $itemData['updated_at'] = $orderItem->getUpdatedAt();

        $itemData['product_type'] = 'simple';
        $itemData['product_options'] = '';

        $itemData['weight'] = $optionModel->getWeight();
        $reduce['weight'] += floatval($itemData['weight']);

        $itemData['base_cost'] = floatval($optionModel->getCost());
        $reduce['cost'] += $itemData['base_cost'];


        $itemData['sku'] = $sku;

        if (($optionType == 'field' || $optionType == 'area') && isset($productOptions['info_buyRequest']['options'][$optionId])) {
            $itemData['description'] = $productOptions['info_buyRequest']['options'][$optionId];
        } else {
            $itemData['description'] = $optionModel->getDescription();
        }

        $qty = $orderItem->getQtyOrdered();
        if ($qty == $orderItem->getQtyInvoiced()) $isInvoiced = true;

        $optionQty = $helper->getCustomOptionQuantity($product, $optionId, $optionModel, $productOptions['info_buyRequest']);

        $optionTotalQty = ($customoptionsIsOnetime ? $optionQty : $optionQty * $qty);
        $orderTotalQtyOrdered += $optionTotalQty;
        $itemData['qty_ordered'] = $optionTotalQty;
        if ($isInvoiced) $itemData['qty_invoiced'] = $optionTotalQty;

        // get price data
        $basePrice = $helper->getOptionPriceByQty($optionModel, $optionTotalQty);
        if ($basePrice != 0) $basePrice = $basePrice / $optionTotalQty;


        // option taxClassId
        $taxClassId = ($optionModel->getTaxClassId() ? $optionModel->getTaxClassId() : $product->getTaxClassId());

        // calculate tax
        if ($basePrice != 0) {
            //$quote = Mage::getSingleton('checkout/cart')->getQuote();
            $quote = Mage::getSingleton('checkout/session')->getQuote();

            if (Mage::helper('tax')->priceIncludesTax($store)) {
                // Exclude Default Tax
                $basePrice = $helper->getPriceExcludeTax($basePrice, $quote, $taxClassId);
            }
            $basePriceInclTax = $basePrice + $helper->getTaxPrice($basePrice, $quote, $taxClassId);
        } else {
            $basePriceInclTax = 0;
        }

        // convert basePrice - to price
        $price = $store->convertPrice($basePrice, false, false);
        $priceInclTax = $store->convertPrice($basePriceInclTax, false, false);

        $itemData['base_price'] = $basePrice;
        $reduce['base_price'] += floatval($itemData['base_price']) * $optionTotalQty;


        $itemData['price'] = $price;
        $reduce['price'] += floatval($itemData['price']) * $optionTotalQty;

        $itemData['original_price'] = $itemData['price'];
        $itemData['base_original_price'] = $itemData['base_price'];

        $itemData['row_total'] = $itemData['price'] * $optionTotalQty;
        $reduce['total_price'] += floatval($itemData['row_total']);
        $itemData['base_row_total'] = $itemData['base_price'] * $optionTotalQty;
        $reduce['base_total_price'] += floatval($itemData['base_row_total']);

        $itemData['price_incl_tax'] = $priceInclTax;
        $itemData['base_price_incl_tax'] = $basePriceInclTax;

        $itemData['row_total_incl_tax'] = $itemData['price_incl_tax'] * $optionTotalQty;
        $itemData['base_row_total_incl_tax'] = $itemData['base_price_incl_tax'] * $optionTotalQty;

        $itemData['tax_percent'] = $helper->getTaxRate($quote, $taxClassId);

        $itemData['tax_amount'] = ($priceInclTax - $price) * $optionTotalQty;
        $reduce['tax_amount'] += floatval($itemData['tax_amount']);

        $itemData['base_tax_amount'] = ($basePriceInclTax - $basePrice) * $optionTotalQty;
        $reduce['base_tax_amount'] += floatval($itemData['base_tax_amount']);


        // item options
        $insertProductOptions = array('isSkuPolicyItem' => 1);
        if ($isFirstGroupedInsert && isset($productOptions['info_buyRequest'])) {
            $insertProductOptions['originalProductId'] = $product->getId();
            $insertProductOptions['originalInfoBuyRequest'] = $productOptions['info_buyRequest'];
        }
        $itemData['product_options'] = serialize($insertProductOptions);

        if ($isInvoiced) {
            $itemData['qty_invoiced'] = $itemData['qty_ordered'];

            $itemData['row_invoiced'] = $itemData['row_total'];
            $itemData['base_row_invoiced'] = $itemData['base_row_total'];

            $itemData['tax_invoiced'] = $itemData['tax_amount'];
            $itemData['base_tax_invoiced'] = $itemData['base_tax_amount'];
        }

        //print_r($itemData); exit;
        $connection->insert($tablePrefix . 'sales_flat_order_item', $itemData);
        $orderItemId = $connection->lastInsertId($tablePrefix . 'sales_flat_order_item');

        // insert invoice item
        if ($isInvoiced && $orderItemId) {
            $invoice = $orderItem->getOrder()->getInvoiceCollection()->getFirstItem();
            if ($invoice && $invoice->getId()) {
                $itemInvoiceData = array();
                $itemInvoiceData['parent_id'] = $invoice->getId();

                $itemInvoiceData['price'] = $itemData['price'];
                $itemInvoiceData['base_price'] = $itemData['base_price'];

                $itemInvoiceData['row_total'] = $itemData['row_total'];
                $itemInvoiceData['base_row_total'] = $itemData['base_row_total'];

                $itemInvoiceData['tax_amount'] = $itemData['tax_amount'];
                $itemInvoiceData['base_tax_amount'] = $itemData['base_tax_amount'];

                $itemInvoiceData['price_incl_tax'] = $itemData['price_incl_tax'];
                $itemInvoiceData['base_price_incl_tax'] = $itemData['base_price_incl_tax'];

                $itemInvoiceData['row_total_incl_tax'] = $itemData['row_total_incl_tax'];
                $itemInvoiceData['base_row_total_incl_tax'] = $itemData['base_row_total_incl_tax'];

                $itemInvoiceData['qty'] = $optionTotalQty;
                $itemInvoiceData['product_id'] = $productId;
                $itemInvoiceData['order_item_id'] = $orderItemId;
                $itemInvoiceData['sku'] = $sku;
                $itemInvoiceData['name'] = $productName;
                $connection->insert($tablePrefix . 'sales_flat_invoice_item', $itemInvoiceData);
            }
        }

        return array($reduce, $orderTotalQtyOrdered, $isInvoiced);
    }

    public function heidelpayPaymentCancel($observer)
    {
        $lastRealOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        if (!$lastRealOrderId) return $this;
        $order = Mage::getModel('sales/order')->loadByIncrementId($lastRealOrderId);
        if (!$order->getId()) return $this;
        $orderItems = $order->getItemsCollection();
        foreach ($orderItems as $orderItem) {
            $this->cancelOrderItem($orderItem);
        }
    }


    public function cancelOrderItem($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isInventoryEnabled()) return $this;

        if ($observer instanceof Mage_Sales_Model_Order_Item) {
            $orderItem = $observer;
        } else {
            $orderItem = $observer->getEvent()->getItem();
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();

        // qty cancel now
        $qty = intval($orderItem->getQtyToCancel());

        // options sku -> increase product inventory or options inventory
        $productOptions = $orderItem->getProductOptions();
        if (!isset($productOptions['options'])) return $this;


        foreach ($productOptions['options'] as $option) {
            switch ($option['option_type']) {
                case 'drop_down':
                case 'radio':
                case 'checkbox':
                case 'multiple':
                case 'swatch':
                case 'multiswatch':
                case 'hidden':
                    $optionId = $option['option_id'];
                    $customoptionsIsOnetime = Mage::getModel('catalog/product_option')->load($optionId)->getCustomoptionsIsOnetime();
                    $optionTypeIds = explode(',', $option['option_value']);
                    foreach ($optionTypeIds as $optionTypeId) {
                        $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                        $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                        $sku = $productOptionValueModel->getSku();
                        if ($customoptionsQty === '' && $sku == '') continue;

                        if (isset($productOptions['info_buyRequest']['options_' . $optionId . '_qty'])) {
                            $optionQty = $helper->typifyQty($orderItem, $productOptions['info_buyRequest']['options_' . $optionId . '_qty']);
                        } elseif (isset($productOptions['info_buyRequest']['options_' . $optionId . '_' . $optionTypeId . '_qty'])) {
                            $optionQty = $helper->typifyQty($orderItem, $productOptions['info_buyRequest']['options_' . $optionId . '_' . $optionTypeId . '_qty']);
                        } else {
                            $optionQty = 1;
                        }
                        $optionTotalQty = ($customoptionsIsOnetime ? $optionQty : $optionQty * $qty);

                        // check link inventory to other option by IGI
                        $extra = $productOptionValueModel->getExtra();
                        if ($extra != '' && strlen($extra) >= 2) {
                            if (substr($extra, 0, 1) == 'i') {
                                $IGI = substr($extra, 1);
                                $row = $helper->getRowValueByIGI($IGI, $orderItem->getProductId());
                                if ($row) {
                                    $optionTypeId = $row['option_type_id'];
                                    $sku = $row['sku'];
                                    $customoptionsQty = $row['customoptions_qty'];
                                }
                            }
                        }

                        $productId = $helper->getProductIdBySku($sku);
                        if ($productId > 0) {
                            Mage::getSingleton('cataloginventory/stock')->backItemQty($productId, $optionTotalQty);
                        } elseif ($customoptionsQty !== null && $customoptionsQty !== '') {
                            $customoptionsQty = $customoptionsQty + $optionTotalQty;
                            // model 'catalog/product_option_value' - do not use!
                            $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty' => $customoptionsQty), 'option_type_id = ' . $optionTypeId);
                        }
                    }
            }

        }

        return $this;

    }

    public function creditMemoRefund($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isInventoryEnabled()) return $this;

        $orderItems = $observer->getEvent()->getCreditmemo()->getOrder()->getItemsCollection();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();
        $creditmemoData = Mage::app()->getRequest()->getParam('creditmemo');

        foreach ($orderItems as $orderItem) {
            // if not ckecked "Return to Stock"
            if (!isset($creditmemoData['items'][$orderItem->getId()]['back_to_stock'])) continue;

            // qty refund now            
            $qty = intval($orderItem->getQtyRefunded()) - intval($orderItem->getOrigData('qty_refunded'));

            // options sku -> increase product inventory and options inventory
            $productOptions = $orderItem->getProductOptions();
            if (!isset($productOptions['options'])) continue;

            foreach ($productOptions['options'] as $option) {
                switch ($option['option_type']) {
                    case 'drop_down':
                    case 'radio':
                    case 'checkbox':
                    case 'multiple':
                    case 'swatch':
                    case 'multiswatch':
                    case 'hidden':
                        $optionId = $option['option_id'];
                        $customoptionsIsOnetime = Mage::getModel('catalog/product_option')->load($optionId)->getCustomoptionsIsOnetime();
                        $optionTypeIds = explode(',', $option['option_value']);
                        foreach ($optionTypeIds as $optionTypeId) {
                            $productOptionValueModel = Mage::getModel('catalog/product_option_value')->load($optionTypeId);
                            $customoptionsQty = $productOptionValueModel->getCustomoptionsQty();
                            $sku = $productOptionValueModel->getSku();
                            if ($customoptionsQty === '' && $sku == '') continue;

                            if (isset($productOptions['info_buyRequest']['options_' . $optionId . '_qty'])) {
                                $optionQty = intval($productOptions['info_buyRequest']['options_' . $optionId . '_qty']);
                            } elseif (isset($productOptions['info_buyRequest']['options_' . $optionId . '_' . $optionTypeId . '_qty'])) {
                                $optionQty = intval($productOptions['info_buyRequest']['options_' . $optionId . '_' . $optionTypeId . '_qty']);
                            } else {
                                $optionQty = 1;
                            }
                            $optionTotalQty = ($customoptionsIsOnetime ? $optionQty : $optionQty * $qty);

                            // check link inventory to other option by IGI
                            $extra = $productOptionValueModel->getExtra();
                            if ($extra != '' && strlen($extra) >= 2) {
                                if (substr($extra, 0, 1) == 'i') {
                                    $IGI = substr($extra, 1);
                                    $row = $helper->getRowValueByIGI($IGI, $orderItem->getProductId());
                                    if ($row) {
                                        $optionTypeId = $row['option_type_id'];
                                        $sku = $row['sku'];
                                        $customoptionsQty = $row['customoptions_qty'];
                                    }
                                }
                            }

                            $productId = $helper->getProductIdBySku($sku);
                            if ($productId > 0) {
                                Mage::getSingleton('cataloginventory/stock')->backItemQty($productId, $optionTotalQty);
                            } elseif ($customoptionsQty !== null && $customoptionsQty !== '') {
                                $customoptionsQty = $customoptionsQty + $optionTotalQty;
                                // model 'catalog/product_option_value' - do not use!
                                $connection->update($tablePrefix . 'catalog_product_option_type_value', array('customoptions_qty' => $customoptionsQty), 'option_type_id = ' . $optionTypeId);
                            }
                        }
                }

            }
        }

        return $this;
    }

    // set weight, cost and sku_police apply to cart
    public function quoteItemSetProduct($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isEnabled() || (!$helper->isWeightEnabled() && !$helper->isOptionSkuPolicyEnabled() && !$helper->isOptionSkuPolicyApplyToCart() && !$helper->isCostEnabled())) return $this;

        $quoteItem = $observer->getEvent()->getQuoteItem();
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote()) return $this;

        $product = $quoteItem->getProduct();

        // prepare post data
        $post = $helper->getInfoBuyRequest($product);

        if ($helper->isOptionSkuPolicyApplyToCart()) {
            //if (isset($post['sku_policy_name'])) $quoteItem->setName($post['sku_policy_name']);
            if (isset($post['sku_policy_weight'])) $quoteItem->setWeight($post['sku_policy_weight']);
            if (isset($post['sku_policy_sku'])) $quoteItem->setSku($post['sku_policy_sku']);
        }

        if (!$helper->isWeightEnabled() && !$helper->isOptionSkuPolicyEnabled() && !$helper->isCostEnabled()) return $this;

        if (isset($post['options'])) $options = $post['options']; else $options = false;

        if ($options) {

            // if ProductSkuPolicy = Grouped -> disable inventory
            if ($helper->getProductSkuPolicy($product) == 3) {
                $stockItem = $product->getStockItem();
                $stockItem->setUseConfigManageStock(0);
                $stockItem->setManageStock(0);
            }

            if (!$helper->isWeightEnabled() && !$helper->isCostEnabled()) return $this;

            $customerGroupId = $helper->getCustomerGroupId();

            $optionsWeight = 0;
            $optionsCost = 0;

            foreach ($options as $optionId => $option) {
                $optionModel = $product->getOptionById($optionId);
                if (!$optionModel) continue;
                $optionModel->setProduct($product);

                // check Options Customer Group
                if ($helper->isCustomerGroupsEnabled() && $optionModel->getCustomerGroups() !== '' && !in_array($customerGroupId, explode(',', $optionModel->getCustomerGroups()))) continue;

                // set options weight and cost
                $optionType = $optionModel->getType();
                if ($optionModel->getGroupByType($optionType) != Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) continue;
                if (!is_array($option)) $option = array($option);
                //product Qty
                $qty = intval($quoteItem->getQty());


                foreach ($option as $optionTypeId) {
                    if (!$optionTypeId) continue;

                    $value = $optionModel->getValueById($optionTypeId);

                    $optionQty = $helper->getPostCustomoptionQty($quoteItem->getProduct(), $optionModel, $value, $post);

                    // get option weight
                    if ($value) {
                        if ($value->getWeight() > 0) {
                            $weight = floatval($value->getWeight());
                            if ($optionModel->getCustomoptionsIsOnetime()) $weight = $weight / $qty;
                            $optionsWeight += $weight * $optionQty;
                        }

                        // get option cost
                        if ($value->getCost() > 0) {
                            $cost = floatval($value->getCost());
                            if ($optionModel->getCustomoptionsIsOnetime()) $cost = $cost / $qty;
                            $optionsCost += $cost * $optionQty;
                        }
                    }
                }
            }

            if ($helper->isWeightEnabled() && $optionsWeight > 0) {
                // check absolute weight
                if (!$helper->getProductAbsoluteWeight($product)) $optionsWeight += $quoteItem->getWeight();
                // set weight for qty=1
                $quoteItem->setWeight($optionsWeight);
            }

            if ($helper->isCostEnabled() && $optionsCost > 0) {
                // check absolute price
                if (!$helper->getProductAbsolutePrice($product)) $optionsCost += $quoteItem->getBaseCost();
                // set baseCost for qty=1
                $quoteItem->setBaseCost($optionsCost);
            }

        }

        return $this;
    }


    // isOptionSkuPolicyApplyToCart
    public function quoteProductAddAfter($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isEnabled() || !$helper->isOptionSkuPolicyEnabled() || !$helper->isOptionSkuPolicyApplyToCart()) return $this;
        $items = $observer->getEvent()->getItems();

        Mage::getModel('mageworx_customoptions/catalog_product_option_skupolicy')->applySkuPolicyToCart($items);

    }

    public function catalogProductCollectionLoadBefore($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        $query = new Zend_Db_Expr("IF((
            SELECT vm.view_mode AS required_options 
            FROM " . Mage::getSingleton('core/resource')->getTableName('catalog_product_option') . " AS cpo 
            LEFT JOIN " . Mage::getSingleton('core/resource')->getTableName('mageworx_custom_options_option_view_mode') . " AS vm ON cpo.option_id=vm.option_id AND (vm.store_id='" . intval(Mage::app()->getStore()->getId()) . "' OR vm.store_id=0)
            WHERE e.entity_id=cpo.product_id  AND cpo.is_require=1 ORDER BY vm.store_id DESC LIMIT 0,1)='1','1','0')");

        $inject = $collection->getSelect()->getPart('columns');
        $inject[] = array('e', $query, 'required_options');
        $collection->getSelect()->setPart('columns', $inject);
    }


    public function catalogProductCollectionLoadAfter($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if ($helper->isEnabled() && $helper->isSetProductOutOfStock()) {
            $collection = $observer->getEvent()->getCollection();
            if (count($collection) > 0) {
                $isShowOutOfStock = Mage::helper('cataloginventory')->isShowOutOfStock();
                foreach ($collection as $key => $product) {
                    if ($product->getRequiredOptions()) {
                        $helper->doNotCheckQuote(1);
                        $product->getProductOptionsCollection();
                        $helper->doNotCheckQuote(-1);
                        if (!$isShowOutOfStock && !$product->isSalable()) $collection->removeItemByKey($key);
                    }
                }
            }
        }
    }


    public function orderItemsLoadBefore($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if ($helper->isEnabled() && $helper->isOptionSkuPolicyEnabled()) {
            $observer->getEvent()->getOrderItemCollection()->setOrder('quote_item_id', 'ASC')->setOrder('item_id', 'ASC');
        }
    }

    public function orderItemsLoadAfter($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');

        if ($helper->isEnabled() && $helper->isOptionSkuPolicyEnabled() && (
                Mage::app()->getRequest()->getActionName() == 'reorder' ||
                Mage::app()->getRequest()->getActionName() == 'start'
            )
        ) {
            $collection = $observer->getEvent()->getOrderItemCollection();
            foreach ($collection as $key => $item) {
                $productOptions = $item->getProductOptions();
                if (isset($productOptions['originalProductId']) && $productOptions['originalProductId']) {
                    $item->setProductId($productOptions['originalProductId']);
                } elseif (isset($productOptions['isSkuPolicyItem']) && $productOptions['isSkuPolicyItem']) {
                    // remove inserted item
                    $collection->removeItemByKey($key);
                    continue;
                }

                // restore info_buyRequest
                if (isset($productOptions['originalInfoBuyRequest']) && $productOptions['originalInfoBuyRequest']) {
                    $productOptions['info_buyRequest'] = $productOptions['originalInfoBuyRequest'];
                    $item->setProductOptions($productOptions);
                }
            }

        }
    }

    /**
     * Update linked option value SKU if product SKU is modified
     *
     * Event: catalog_product_save_before
     *
     * @param $observer
     */
    public function updateLinkedOptionValues($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeId = Mage::app()->getStore()->getStoreId();

        if(!$product->getId()) {
            return $this;
        }

        if ($product->hasDataChanges() &&
            $product->getData('sku') &&
            $product->getData('sku') !== $product->getOrigData('sku') &&
            $product->getOrigData('sku') !== null
        ) {

            $collection = Mage::getResourceModel('catalog/product_option_value_collection')
                ->addTitlesToResult($storeId)
                ->addFieldToFilter('sku', $product->getOrigData('sku'));
            foreach ($collection as $item) {
                $item->setSku($product->getData('sku'));
                $item->save();
            }

            $collection = Mage::getResourceModel('mageworx_customoptions/group_collection');
            foreach ($collection as $item) {
                $modifiedHashOptions = array();
                $hashOptions = unserialize($item->getHashOptions());
                foreach ($hashOptions as $key => $data) {
                    $modifiedValues = array();
                    foreach ($data['values'] as $valuesKey => $valuesData) {
                        if ($valuesData['sku'] == $product->getOrigData('sku')) {
                            $valuesData['sku'] = $product->getData('sku');
                        }
                        $modifiedValues[$valuesKey] = $valuesData;
                    }
                    $data['values'] = $modifiedValues;
                    $modifiedHashOptions[$key] = $data;
                }
                $item->setHashOptions(serialize($modifiedHashOptions));
                $item->save();
            }
        }
    }

    /**
     * Update buy request option quantities for the same product with the same options
     *
     * Event: catalog_product_type_prepare_full_options
     *
     * @param $observer
     * @return $this
     */
    public function updateInfoBuyRequest($observer)
    {
        $helper = Mage::helper('mageworx_customoptions');
        if (!$helper->isEnabled() || !$helper->isExtrasEnabled()) {
            return $this;
        }

        $cart = Mage::getModel('checkout/cart');
        if (!is_object($cart)) {
            return $this;
        }

        $cartQuote = $cart->getQuote();
        if (!is_object($cartQuote)) {
            return $this;
        }

        $infoBuyRequest = $observer->getBuyRequest();

        foreach ($cartQuote->getAllItems() as $item) {
            $itemProduct = $item->getProduct();

            if (!is_object($itemProduct) || $infoBuyRequest->getProduct() != $itemProduct->getId()) {
                continue;
            }

            $infoBuyRequestData = $infoBuyRequest->getData();
            $oldInfoBuyRequestData = $helper->getInfoBuyRequest($itemProduct);

            if ($infoBuyRequestData['options'] == $oldInfoBuyRequestData['options']) {
                foreach ($infoBuyRequestData as $key => $value) {
                    if (preg_match('/options_/', $key) &&
                        isset($infoBuyRequestData[$key]) &&
                        isset($oldInfoBuyRequestData[$key])
                    ) {
                        $infoBuyRequestData[$key] += $oldInfoBuyRequestData[$key];
                    }
                }
            }

            foreach ($infoBuyRequestData as $dataKey => $dataValue) {
                $observer->getBuyRequest()->setData($dataKey, $dataValue);
            }
        }

        return $this;
    }
}
