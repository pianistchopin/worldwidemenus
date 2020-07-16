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
 * @package     CustomProducts
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */
class Ophirah_CustomProducts_Model_Observer extends Mage_Core_Helper_Abstract
{
    const CONVERT_PRODUCT_SKU = 'qpc';

    /**
     * Function that checks if allowed to add custom product to order
     * @param Varien_Event_Observer $observer
     */
    public function licensecheckCustomProduct(Varien_Event_Observer $observer)
    {
        $request = $observer->getRequestModel();
        if ($request->has('item') && !$request->getPost('update_items') && !($request->getActionName() == 'save')) {
            $items = $request->getPost('item');

            //look if a custom product is being added
            foreach ($items as $itemId => $itemOptions) {
                $product = Mage::getModel("catalog/product")->load($itemId);
                if ($product) {
                    if ($product->getSku() == Ophirah_CustomProducts_Helper_Data::PRODUCT_SKU) {

                        if (!Mage::helper('qquoteadv/licensechecks')->isAllowedCustomProduct()) {
                            $message = Mage::helper('qquoteadv')
                                ->__("Custom product is only available in the Cart2Quote enterprise edition.
                                <a href='https://www.cart2quote.com/cart2quote-update-upgrade.html'>Upgrade</a>");
                            Mage::getSingleton('core/session')->addError($message);
                            unset($items[$itemId]);
                        }
                    }
                }
            }
            $request->setPost('item', $items);
        }
    }

    /**
     * Function that checks the post data of the order creation page in the backend for custom products
     *
     * @param Varien_Event_Observer $observer
     */
    public function createCustomProductObserver(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('qquoteadv/licensechecks')->isAllowedCustomProduct()) {
            return;
        }

        //convert custom products on creation (create order screen)
        if (Mage::getStoreConfig('custom_products/general/convert') == 2) {
            $request = $observer->getRequestModel();
            $this->createCustomProduct($request);
        }
    }

    /**
     * Function that check if the custom products on a quote need to be converted to a real product
     * This function only needs a quote object to be available in the $observer object
     *
     * @param Varien_Event_Observer $observer
     */
    public function convertCustomProductOnCreationObserver(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('qquoteadv/licensechecks')->isAllowedCustomProduct()) {
            return;
        }

        //convert custom products on creation (edit quote screen)
        if (Mage::getStoreConfig('custom_products/general/convert') == 2) {
            $quoteId = $observer->getQuoteId();
            if (isset($quoteId)) {
                $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);
                if (isset($quote) && !empty($quote)) {
                    $this->convertCustomProduct($quote);
                }
            }
        }
    }

    /**
     * Function that check if the custom products on a quote need to be converted to a real product
     * This function only needs a quote object to be available in the $observer object
     *
     * @param Varien_Event_Observer $observer
     */
    public function convertCustomProductObserver(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('qquoteadv/licensechecks')->isAllowedCustomProduct()) {
            return;
        }

        //convert custom products on quote to order conversion (backend)
        if (Mage::getStoreConfig('custom_products/general/convert') == 1) {
            $quote = $observer->getQuote();
            if (isset($quote) && !empty($quote)) {
                $this->convertCustomProduct($quote);
            }
        }
    }

    /**
     * Function that check if the custom products on a quote need to be converted to a real product
     * This function only needs a quote object to be available in the $observer object
     *
     * @param Varien_Event_Observer $observer
     */
    public function convertCustomProductFrontendObserver(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('qquoteadv/licensechecks')->isAllowedCustomProduct()) {
            return;
        }

        //convert custom products on quote to order conversion (frontend)
        if (Mage::getStoreConfig('custom_products/general/convert') == 1) {
            $observerData = $observer->getData();
            $quoteId = $observerData[0];
            if (isset($quoteId)) {
                $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load((int)$quoteId);
                if (isset($quote) && !empty($quote)) {
                    //emulate the backend store to be able to create products
                    $appEmulation = Mage::getSingleton('core/app_emulation');
                    $initialEnvironmentInfo = $appEmulation
                        ->startEnvironmentEmulation(Mage_Core_Model_App::ADMIN_STORE_ID);

                    $this->convertCustomProduct($quote);

                    //stop admin store emulation process
                    $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                }
            }
        }
    }

    /**
     * Replace the options on the custom product with hard values
     *
     * @param $qqadvproduct
     * @param $newProduct
     * @return array
     */
    private function _convertCustomProduct($qqadvproduct, $newProduct)
    {
        $qqadvproductOptions = array();
        $qqadvproductAttribute = unserialize($qqadvproduct->getAttribute());
        if (isset($qqadvproductAttribute['options'])) {
            $qqadvproductOptions = $qqadvproductAttribute['options'];
        }

        //move the options to the product data
        $qqadvproductOptions = $this->_replaceProductOptions($newProduct, $qqadvproductOptions);

        //set quoteadv product with updated options
        if (is_array($qqadvproductOptions)) {
            if (count($qqadvproductOptions) > 0) {
                $qqadvproductAttribute['options'] = $qqadvproductOptions;
                $newProductOptions = serialize($qqadvproductAttribute['options']);
                $qqadvproduct->setOptions($newProductOptions);
            } else {
                unset($qqadvproductAttribute['options']);
                $qqadvproduct->setOptions(null);
                $qqadvproduct->getHasOptions(false);
            }

            $qqadvproductAttribute = serialize($qqadvproductAttribute);
            $qqadvproduct->setAttribute($qqadvproductAttribute);
            $qqadvproduct->save();
        }

        //save
        $newProduct->setShortDescription(null); //unset default custom product short description
        $newProduct->save();
    }

    /**
     * Function that converts the old option array to the same array with new option id's
     *
     * @param array $oldOptions
     * @param $oldProduct
     * @param $newProduct
     * @return array
     */
    private function _convertProductOptions($oldOptions = array(), $oldProduct, $newProduct)
    {
        $newOptions = array();

        foreach ($oldOptions as $optionId => $optionValue) {
            $oldProductOptions = $oldProduct->getOptions();
            if (isset($oldProductOptions[$optionId])) {
                $oldProductOption = $oldProductOptions[$optionId];
                $optionName = $oldProductOption->getTitle();
                $optionType = $oldProductOption->getType();

                $newProductOptions = $newProduct->getOptions();
                foreach ($newProductOptions as $newProductOptionId => $newProductOption) {
                    if ($newProductOption->getTitle() == $optionName && $newProductOption->getType() == $optionType) {
                        $newOptions[$newProductOptionId] = $optionValue;
                    }
                }
            }
        }

        return $newOptions;
    }

    /**
     * Function that generates an sku
     *
     * @param null $someId
     * @return string
     */
    private function _getConvertedSku($someId = null)
    {
        if (!isset($someId)) {
            $date = new DateTime();
            $someId = $date->getTimestamp();
        }

        $productWithSameSKUExists = true;
        while ($productWithSameSKUExists) {
            $convertedSKU = Ophirah_CustomProducts_Model_Observer::CONVERT_PRODUCT_SKU . '-' . $someId . rand(100, 999);
            $product = Mage::getModel("catalog/product")->getIdBySku($convertedSKU);

            if (!$product) {
                $productWithSameSKUExists = false;
            }
        }

        return $convertedSKU;
    }

    /**
     * Function that duplicates a product
     *
     * @param $product
     * @param $newSkuInput
     * @return Mage_Core_Model_Abstract
     * @throws Exception
     */
    private function _duplicateProduct($product, $newSkuInput)
    {
        //duplicate the custom product
        $newProduct = $product->duplicate();
        $newProduct->save();

        //reload to avoid duplcate inventory
        $newProduct = Mage::getModel("catalog/product")->load($newProduct->getId());

        //overwrite some data that isn't valid after duplication
        $newProduct->setStockData(
            array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 0,
                'is_in_stock' => 1,
                'qty' => 9999
            )
        );
        $newProduct->setSku($this->_getConvertedSku($newSkuInput));
        $newProduct->setStatus($product->getStatus());
        $newProduct->save();
        return $newProduct;
    }

    /**
     * @param $qqadvproduct
     * @param $newProduct
     * @param $product
     */
    private function _convertProductAttributes($qqadvproduct, $newProduct, $product)
    {
        $qqadvproductAttribute = unserialize($qqadvproduct->getAttribute());

        if (isset($qqadvproductAttribute['product'])) {
            $qqadvproductAttribute['product'] = $newProduct->getId();
        }

        if (isset($qqadvproductAttribute['options'])) {
            $qqadvproductAttribute['options'] = $this->_convertProductOptions(
                $qqadvproductAttribute['options'],
                $product,
                $newProduct
            );

            $newProductOptions = serialize($qqadvproductAttribute['options']);
            $qqadvproduct->setOptions($newProductOptions);
        }

        $qqadvproductAttribute = serialize($qqadvproductAttribute);
        $qqadvproduct->setAttribute($qqadvproductAttribute);
    }

    /**
     * Replace the product values with the options that are set on the original custom product
     *
     * @param $newProduct
     * @param $productOptions
     * @return array
     */
    private function _replaceValuesWithOptions($newProduct, $productOptions)
    {
        $removedOptions = array();
        $newProductOptions = $newProduct->getOptions();
        $attributes = Mage::getModel('eav/config')->getEntityAttributeCodes(
            Mage_Catalog_Model_Product::ENTITY,
            $newProduct
        );

        //loop trough all options
        foreach ($newProductOptions as $newProductOptionId => $newProductOption) {
            //name option
            if ($newProductOption->getTitle() == Ophirah_CustomProducts_Helper_Data::NAME) {
                if (isset($productOptions[$newProductOptionId])) {
                    $newProduct->setName($productOptions[$newProductOptionId]);
                }

                $removedOptions[] = $newProductOptionId;
                continue;
            }

            //sku option
            if ($newProductOption->getTitle() == Ophirah_CustomProducts_Helper_Data::SKU) {
                if (isset($productOptions[$newProductOptionId])) {
                    $optionSku = $productOptions[$newProductOptionId];
                    $product = Mage::getModel("catalog/product")->getIdBySku($optionSku);
                    if ($product) {
                        //generate Sku in case it already exists
                        $optionSku = $this->_getConvertedSku($optionSku);
                    }

                    $newProduct->setSku($optionSku);
                }

                $removedOptions[] = $newProductOptionId;
                continue;
            }

            //description option
            if ($newProductOption->getTitle() == Ophirah_CustomProducts_Helper_Data::DESCRIPTION) {
                if (isset($productOptions[$newProductOptionId])) {
                    $newProduct->setDescription($productOptions[$newProductOptionId]);
                }

                $removedOptions[] = $newProductOptionId;
                continue;
            }

            //image option
            if ($newProductOption->getTitle() == Ophirah_CustomProducts_Helper_Data::IMAGE) {
                if (isset($productOptions[$newProductOptionId])
                    && isset($productOptions[$newProductOptionId]['fullpath'])
                ) {
                    $this->_addImageToProduct($newProduct, $productOptions[$newProductOptionId]['fullpath']);
                }

                $removedOptions[] = $newProductOptionId;
                continue;
            }

            //any other option that is set on the custom product and is available as an attribute on the new product
            $newAttributeCode = strtolower(trim($newProductOption->getTitle()));
            $newAttributeCode = str_replace(' ', '_', $newAttributeCode);

            if (in_array($newAttributeCode, $attributes)) {
                $newProduct->setData($newAttributeCode, $productOptions[$newProductOptionId]);
                $removedOptions[] = $newProductOptionId;
            }
        }

        return $removedOptions;
    }

    /**
     * Get the image from the upload form and add it to the new product
     *
     * @param $newProduct
     * @param $requestOptions
     * @param $oldProduct
     * @return mixed
     */
    private function _replaceImageUploadOptions($newProduct, $requestOptions, $oldProduct)
    {
        $newProductOptions = $newProduct->getOptions();
        $oldProductOptions = $oldProduct->getOptions();

        foreach ($oldProductOptions as $oldProductOptionId => $oldProductOption) {
            if ($oldProductOption->getTitle() == Ophirah_CustomProducts_Helper_Data::IMAGE) {
                $oldProductImageOptionId = $oldProductOptionId;
                break;
            }
        }

        if (isset($oldProductImageOptionId)) {
            $fileKey = 'options_' . $oldProductImageOptionId . '_file_action';
            foreach ($newProductOptions as $newProductOptionId => $newProductOption) {
                if ($newProductOption->getTitle() == Ophirah_CustomProducts_Helper_Data::IMAGE) {
                    if (isset($requestOptions[$fileKey])) {
                        //some upload and set image
                        $uploadKey = 'item_' . $oldProduct->getId() . '_options_' . $oldProductImageOptionId . '_file';

                        try {
                            //upload to image
                            $filePath = $this->_uploadPostImages($uploadKey);

                            //set the image on the product
                            $this->_addImageToProduct($newProduct, $filePath);

                            //remove the image option
                            Mage::getModel('catalog/product_option')->load($newProductOptionId)->delete();

                            //remove the request option
                            unset($requestOptions[$fileKey]);

                            //remove the upload item
                            unset($_FILES[$uploadKey]);
                        } catch (Exception $e) {
                            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_fp_exception.log', true);
                        }

                        continue;
                    }
                }
            }
        }

        return $requestOptions;
    }

    /**
     * Function that uploads the images in the _FILES postdata
     *
     * @param $uploadKey
     * @return string
     */
    private function _uploadPostImages($uploadKey)
    {
        $filePath = '';

        try {
            $uploader = new Mage_Core_Model_File_Uploader($uploadKey);
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->addValidateCallback('catalog_product_image',
                Mage::helper('catalog/image'), 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                Mage::getSingleton('catalog/product_media_config')->getBaseTmpMediaPath()
            );

            if (is_array($result) && isset($result['path']) && isset($result['file'])) {
                $filePath = $result['path'] . $result['file'];
            }
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_fp_exception.log', true);
        }

        return $filePath;
    }

    /**
     * Replace the product values with the options that are set on the original custom product
     * and remove these options on the current product
     *
     * @param $newProduct
     * @param $productOptions
     */
    private function _replaceProductOptions($newProduct, $productOptions)
    {
        //remove the default custom product options and set it as data values
        $removedOptions = $this->_replaceValuesWithOptions(
            $newProduct,
            $productOptions
        );

        //remove to removed options
        foreach ($removedOptions as $removedOption) {
            try {
                Mage::getModel('catalog/product_option')->load($removedOption)->delete();
                unset($productOptions[$removedOption]);
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_fp_exception.log', true);
            }
        }

        return $productOptions;
    }

    /**
     * Set a new default image on the new product
     *
     * @param $newProduct
     * @param $imagePath
     */
    private function _addImageToProduct($newProduct, $imagePath)
    {
        //get the product media gallery attribute
        $catalogAttribute = Mage::getModel('catalog/resource_eav_attribute')
            ->loadByCode(
                $newProduct->getEntityTypeId(),
                'media_gallery'
            );

        //remove all the current images
        $galleryImages = $newProduct->getMediaGalleryImages();
        foreach ($galleryImages as $image) {
            $catalogAttribute->getBackend()->removeImage($newProduct, $image->getFile());
        }

        //list om image settings for the new image
        $imageTypes = array(
            'thumbnail',
            'small_image',
            'image'
        );

        //add the image to the new product as the default image for the given $imageTypes
        $newProduct->addImageToMediaGallery(
            $imagePath,
            $imageTypes,
            false,
            false
        );
    }

    /**
     * Create a real product from the just created custom product
     *
     * @param $request
     * @throws Exception
     */
    public function createCustomProduct($request)
    {
        if (!Mage::helper('qquoteadv/licensechecks')->isAllowedCustomProduct()) {
            return;
        }

        //check if this request is to add items to a new order
        if ($request->has('item') && !$request->getPost('update_items') && !($request->getActionName() == 'save')) {
            $items = $request->getPost('item');

            //look if a custom product is being added
            foreach ($items as $itemId => $itemOptions) {
                $product = Mage::getModel("catalog/product")->load($itemId);
                if ($product) {
                    if ($product->getSku() == Ophirah_CustomProducts_Helper_Data::PRODUCT_SKU) {
                        //custom product is in post data
                        $customerId = $request->getPost('customer_id');
                        if (!$customerId) {
                            $customerId = null;
                        }

                        //make a new product
                        $newProduct = $this->_duplicateProduct($product, $customerId);
                        if (isset($itemOptions['options'])) {
                            $itemOptions['options'] = $this->_convertProductOptions(
                                $itemOptions['options'],
                                $product,
                                $newProduct
                            );
                        }

                        //move the options to the product data
                        $itemOptions['options'] = $this->_replaceProductOptions(
                            $newProduct,
                            $itemOptions['options']
                        );

                        //move the options to the product data - image upload field
                        $itemOptions = $this->_replaceImageUploadOptions(
                            $newProduct,
                            $itemOptions,
                            $product
                        );

                        //save
                        $newProduct->setShortDescription(null); //unset default custom product short description
                        $newProduct->save();

                        //replace post data with the new item id
                        $items[$newProduct->getId()] = $itemOptions;
                        unset($items[$itemId]);
                    }
                }
            }

            //overwrite post data
            $request->setPost('item', $items);
        }
    }

    /**
     * Convert all the custom products on a given quote to real products
     *
     * @param $quote
     */
    public function convertCustomProduct($quote)
    {
        //get quote products
        if (Mage::helper('qquoteadv/licensechecks')->isAllowedCustomProduct()) {
            $qqadvproductData = Mage::getModel('qquoteadv/qqadvproduct')
                ->getCollection()
                ->addFieldToFilter('quote_id',
                    array(
                        "eq" => $quote->getId()
                    )
                );

            foreach ($qqadvproductData as $qqadvproduct) {
                // Load quote Items
                $quoteItems = Mage::getModel('qquoteadv/requestitem')->getCollection()
                    ->addFieldToFilter('quote_id', array("eq" => $qqadvproduct->getQuoteId()))
                    ->addFieldToFilter('request_qty', array("eq" => $qqadvproduct->getQty()))
                    ->addFieldToFilter('quoteadv_product_id', array("eq" => $qqadvproduct->getId()))
                    ->load();

                foreach ($quoteItems as $quoteItem) {
                    $product = Mage::getModel("catalog/product")->load($quoteItem->getProductId());
                    if ($product->getSku() == Ophirah_CustomProducts_Helper_Data::PRODUCT_SKU) {
                        //make a new product
                        $newProduct = $this->_duplicateProduct($product, $quote->getId());

                        //replace product in quote
                        $quoteItem->setProductId($newProduct->getId());
                        $quoteItem->save();

                        //convert attributes
                        $this->_convertProductAttributes($qqadvproduct, $newProduct, $product);

                        //move product options to product data for custom product
                        $this->_convertCustomProduct($qqadvproduct, $newProduct);

                        //replace product in quote product
                        $qqadvproduct->setProductId($newProduct->getId());
                        $qqadvproduct->save();

                        //update quoteItem in case that new prices are set
                        $price = $newProduct->getPrice();
                        $cost = $newProduct->getCost();

                        if (!empty($price) || !empty($cost)) {
                            $quoteadvProductId = $qqadvproduct->getId();
                            $qty = $quoteItem->getRequestQty();
                            $currencyCode = $quote->getCurrency();

                            //get new prices
                            $ownerPrice = Mage::helper('qquoteadv')->_applyPrice($quoteadvProductId, $qty);
                            $originalPrice = Mage::helper('qquoteadv')->_applyPrice($quoteadvProductId, $qty);
                            $ownerCurPrice = Mage::helper('qquoteadv')
                                ->_applyPrice($quoteadvProductId, $qty, $currencyCode);
                            $originalCurPrice = Mage::helper('qquoteadv')
                                ->_applyPrice($quoteadvProductId, $qty, $currencyCode);

                            //set new prices
                            $quoteItem->setOwnerBasePrice($ownerPrice);
                            $quoteItem->setOriginalPrice($originalPrice);
                            $quoteItem->setOwnerCurPrice($ownerCurPrice);
                            $quoteItem->setOriginalCurPrice($originalCurPrice);
                            $quoteItem->setCostPrice((int)$cost);

                            //save
                            $quoteItem->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    public function addCustomRequestProduct()
    {
        /**
         * @var Ophirah_CustomProducts_Model_Customrequest $customrequest
         * @var Ophirah_CustomProducts_Model_Customproduct $customproduct
         */
        try {
            $customrequest = Mage::getModel('customproducts/customrequest');
            $customrequest->getCustomRequest();
            $customproduct = Mage::getModel('customproducts/customproduct');
            $customproduct->getCustomProduct();
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_fp_exception.log', true);
            return $e->getMessage();
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addToTopmenu(Varien_Event_Observer $observer)
    {
        $menu = $observer->getMenu();
        $tree = $menu->getTree();

        if (Mage::getStoreConfig('custom_products/custom-request/topmenu')) {
            $node = new Varien_Data_Tree_Node(array(
                'name' => 'Custom Request',
                'id' => 'custom-request',
                'url' => Mage::getUrl('custom-request.html'),
            ), 'id', $tree, $menu);

            $menu->addChild($node);
        }
    }
}
