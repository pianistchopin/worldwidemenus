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
 * @version     1.0.5
 */

/**
 * @since 1.0.5
 * Class Ophirah_CustomProducts_Model_Customproduct
 */
final class Ophirah_CustomProducts_Model_Customproduct extends Ophirah_CustomProducts_Model_Customproduct_Abstract
{
    /**
     * Default product settings
     */
    protected $productName = 'Custom Product';
    protected $productSku = 'quote-product-custom';
    protected $productDescription = 'This is Quote product that is customisable, please don\'t remove this product.';
    protected $productDescriptionShort = 'Quote product that is customisable';

    /**
     * Option types
     */
    protected $name = 'Name';
    protected $sku = 'SKU';
    protected $description = 'Description';
    protected $image = 'Product Image';

    /**
     * Retrieves the custom product
     * @return false|Mage_Catalog_Model_Product
     * @since 1.0.5
     */
    public function getCustomProduct()
    {
        /** @var \Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');

        if (!$product->getIdBySku($this->productSku)) {
            $currentStore = Mage::app()->getStore()->getStoreId();
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $product = $this->createProduct($product);
            if (!is_string($product)) {
                $product = $this->addCustomOptionsToProduct($product);
            }
            Mage::app()->setCurrentStore($currentStore);
        } else {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $this->productSku);

            //fallback if product is not available for this store or disabled.
            if (!$product) {
                $productId = $product->getIdBySku($this->productSku);

                if ($productId) {
                    $product = Mage::getModel('catalog/product')->load($productId);

                    //try setting
                    try {
                        $product->setWebsiteIds(array_keys(Mage::app()->getWebsites()));
                        $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                        $product->save();
                    } catch (Exception $e) {
                        $message = "Could not re-save custom product";
                        Mage::log('Exception: ' . $message, null, 'c2q_fp_exception.log', true);
                    }
                }
            }
        }

        return $product;
    }

    /**
     * Creates an object by product options with the basic product attributes.
     *
     * @param $buyRequest
     * @return Varien_Object
     *          Product
     * @method getName
     * @method getSku
     * @method getType
     *
     *          Image
     * @method getTitle
     * @method getImageUrl - Recommend for HTML
     * @method getQuotePath
     * @method getOrderPath
     * @method getFullPath - Recommend for PDF
     * @method getSize
     * @method getWidth
     * @method getSize
     * @method getSecretKey
     *
     * @since 1.0.5
     */
    public function getProductOptionValues($buyRequest)
    {
        $options = new Varien_Object();
        $attributes = unserialize($buyRequest);

        $product = $this->getCustomProduct();
        $buyRequest = new Varien_Object($attributes);
        $product->setCustomOptions($product->processBuyRequest($buyRequest)->getOptions());

        foreach ($product->getProductOptionsCollection() as $option) {
            $customOption = $product->getCustomOption($option->getOptionId());
            if (is_string($customOption)) {
                $customOption = array($this->formatOptionTitle($option->getTitle()) => $customOption);
            }
            if (is_array($customOption)) {
                $options->addData($customOption);
            }
        }
        $options = $this->prepareProductImageUrl($product, $options);
        return $options;
    }

    /**
     * @param $option
     * @param $key
     * @return string
     * @since 1.0.5
     */
    protected function getOptionLabel($option, $key)
    {
        if (is_array($option) && array_key_exists('label', $option)) {
            $label = $option['label'];
        } else {
            $label = $key;
        }
        return $label;
    }

    /**
     * Get default option settings
     * @return array
     * @since 1.0.5
     */
    protected function _getDefaultOptionSettings()
    {
        return array(
            array(
                'title' => $this->name,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD,
                'is_require' => 1,
                'sort_order' => 1
            ),
            array(
                'title' => $this->sku,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD,
                'is_require' => 0,
                'sort_order' => 2
            ),
            array(
                'title' => $this->description,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA,
                'is_require' => 0,
                'sort_order' => 3
            ),
            array(
                'title' => $this->image,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE,
                'file_extension' => 'jpg, jpeg, png',
                'is_require' => 0,
                'sort_order' => 4
            )
        );
    }
}

