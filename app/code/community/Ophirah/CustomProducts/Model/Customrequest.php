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
 * Class Ophirah_CustomProducts_Model_CustomRequest
 */
final class Ophirah_CustomProducts_Model_Customrequest extends Ophirah_CustomProducts_Model_Customproduct_Abstract
{
    protected $numOfDefaultOptions = 9;

    /**
     * Default product settings
     */
    protected $productName = 'Custom Request';
    protected $productSku = 'custom-request';
    protected $productDescription = 'Please describe what you are looking for';
    protected $productDescriptionShort = 'Looking for something extra special? Let us know!';
    protected $budget = 'Budget';

    /**
     * Option types
     */
    protected $description = 'Description';
    protected $image = 'Product Image';
    protected $subject = 'Subject';
    protected $dateOfDelivery = 'Desired date of Delivery';
    protected $colorOfProduct = 'Color(s)';
    protected $fileUpload = 'Upload additional file(s)';


    /**
     * Retrieves the custom product
     * @return false|Mage_Catalog_Model_Product
     * @since 1.0.5
     */
    public function getCustomRequest()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');

        if (!$product->getIdBySku($this->productSku)) {
            $currentStore = Mage::app()->getStore()->getStoreId();
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $product = $this->createProduct($product);
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG);
            $product->set;
            if (!is_string($product)) {
                $product = $this->addCustomOptionsToProduct($product);
            }
            Mage::app()->setCurrentStore($currentStore);
        } else {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $this->productSku);
        }

        return $product;
    }

    /**
     * Getter for the product name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
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
     * @method getFullpath - Recommend for PDF
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

        $product = $this->getCustomRequest();
        $buyRequest = new Varien_Object($attributes);
        $product->setCustomOptions($product->processBuyRequest($buyRequest)->getOptions());

        foreach ($product->getProductOptionsCollection() as $option) {
            switch ($option->getTitle()) {
                case $this->name:
                case $this->description:
                case $this->image:
                case $this->aim:
                case $this->dateOfDelivery:
                case $this->colorOfProduct:
                    $customOption = $product->getCustomOption($option->getOptionId());
                    if (is_string($customOption)) {
                        $customOption = array($this->formatOptionTitle($option->getTitle()) => $customOption);
                    }
                    if (is_array($customOption)) {
                        $options->addData($customOption);
                    }
                default; // Do nothing
            }
        }
        $options = $this->prepareProductImageUrl($product, $options);
        return $options;
    }


    /**
     * Checks if product ID is same as custom-request product ID
     * @param $productId
     * @return bool
     * @since 1.0.5
     */
    public function isCustomRequest($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        if (isset($product)) {
            if ($product->getSku() == $this->productSku) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get default option settings
     * @return array
     * @since 1.0.5
     */
    public function _getDefaultOptionSettings()
    {
        return array(
            array(
                'title' => $this->subject,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD,
                'is_require' => 1,
                'sort_order' => 0,
            ),
            array(
                'title' => $this->productDescription,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_AREA,
                'is_require' => 1,
                'sort_order' => 1
            ),
            array(
                'title' => $this->image,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE,
                'file_extension' => 'jpg, jpeg, png',
                'is_require' => 0,
                'sort_order' => 2
            ),
            array(
                'title' => $this->budget,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD,
                'is_require' => 1,
                'max_characters' => 10,
                'sort_order' => 3
            ),
            array(
                'title' => $this->colorOfProduct,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FIELD,
                'is_require' => 0,
                'sort_order' => 4
            ),
            array(
                'title' => $this->dateOfDelivery,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE,
                'is_require' => 0,
                'sort_order' => 5
            ),
            array(
                'title' => $this->fileUpload,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE,
                'is_require' => 0,
                'sort_order' => 6
            ),
            array(
                'title' => $this->fileUpload,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE,
                'is_require' => 0,
                'sort_order' => 7
            ),
            array(
                'title' => $this->fileUpload,
                'type' => Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE,
                'is_require' => 0,
                'sort_order' => 8
            )
        );
    }
}
