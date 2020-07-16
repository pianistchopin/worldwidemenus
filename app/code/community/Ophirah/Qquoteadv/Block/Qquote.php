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
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Block_Qquote
 */
class Ophirah_Qquoteadv_Block_Qquote extends Ophirah_Qquoteadv_Block_Qquote_Abstract
{
    /**
     * Ophirah_Qquoteadv_Block_Qquote constructor.
     */
    public function __construct()
    {
        parent::__construct();

        //only run if SCP is enabled
        if (Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')) {
            $this->scp = true;
        }
    }

    /**
     * Function will return the product URL of the current quote item
     *
     * @param $item
     * @return string
     */
    public function getItemUrl($item)
    {
        $itemAttributes = unserialize($item->getAttribute());
        $belongsToGroup = array_key_exists('super_group', $itemAttributes);

        if($belongsToGroup){
            $product = $this->getProduct($itemAttributes['product']);
        } else {
            $product = $this->getProduct($item->getProductId());
        }

        return $product->getProductUrl();
    }

    /**
     * Get Product information from qquote_product table
     * @return quote object
     */
    public function getQuote()
    {
        $quoteId = $this->getCustomerSession()->getQuoteadvId();
        $collection = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);
        return $collection;
    }

    /**
     * Get attribute options array
     * @param object $product
     * @param string $attribute
     * @return array
     */
    public function getOption($product, $attribute)
    {
        $superAttribute = array();

        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
            $superAttribute = Mage::helper('qquoteadv')->getSimpleOptions($product, @unserialize($attribute));
        }
        return $superAttribute;
    }

    /**
     * Returns the continue shopping url
     * Usually the url where a user came from but in some cases the main shop url is returned
     *
     * @return mixed
     */
    public function getContinueShoppingUrl()
    {
        $this->setLastUrlInSession();
        $continueShoppingUrl = $this->getLastUrl();
        return $continueShoppingUrl;
    }

    /**
     * Retrieve disable order references config.
     */
    public function getShowOrderReferences()
    {
        return (bool)(!Mage::getStoreConfig('qquoteadv_quote_frontend/shoppingcart_quotelist/layout_disable_all_order_references', $this->getStoreId()));
    }

    /**
     * Retrieve disable trier option.
     */
    public function getShowTrierOption()
    {
        return (bool)(!Mage::getStoreConfig('qquoteadv_quote_frontend/shoppingcart_quotelist/layout_disable_trier_option', $this->getStoreId()));
    }

    /**
     * Function that returns all non salable product of an collection
     *
     * @param $collection
     * @return array
     */
    public function getNotSalableProductNames($collection)
    {
        $productNames = array();
        foreach ($collection as $_item) {
            $product = $this->getProduct($_item->getProductId());
            try {
                if (!$product->isSaleable()) {
                    $productNames[] = $product->getName();
                }
            } catch (Exception $e) {
                Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
        return $productNames;
    }

    /**
     * Overwrite for getOptionList to get getCustomOptions
     *
     * @param $product
     * @param $item_attributes
     * @return array
     */
    public function getOptionList($product, $item_attributes)
    {
        //return $this->getCustomOptions($product, $item_attributes);
        return $this->getCustomOptions($product);
    }

    /**
     * Function that cuts down a value to a max length of 55 chars
     *
     * @param $optionValue
     * @return mixed
     */
    public function getFormatedOptionValue($optionValue)
    {
        /* @var $helper Mage_Catalog_Helper_Product_Configuration */
        $helper = Mage::helper('catalog/product_configuration');
        $params = array(
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots" onclick="return false">...</a>'
        );
        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * Retrieves product configuration options
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return array
     */
    public function getCustomOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $options = array();
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            $product = $item->getProduct();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    $itemOption = $item->getOptionByCode('option_' . $option->getId());
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItem($item)
                        ->setConfigurationItemOption($itemOption);

                    if ('file' == $option->getType()) {
                        $downloadParams = $item->getFileDownloadParams();
                        if ($downloadParams) {
                            $url = $downloadParams->getUrl();
                            if ($url) {
                                $group->setCustomOptionDownloadUrl($url);
                            }
                            $urlParams = $downloadParams->getUrlParams();
                            if ($urlParams) {
                                $group->setCustomOptionUrlParams($urlParams);
                            }
                        }
                    }

                    $options[] = array(
                        'label'       => $option->getTitle(),
                        'value'       => $group->getFormattedOptionValue($itemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($itemOption->getValue()),
                        'option_id'   => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $group->isCustomizedView()
                    );
                }
            }
        }

        $addOptions = $item->getOptionByCode('additional_options');
        if ($addOptions) {
            $options = array_merge($options, unserialize($addOptions->getValue()));
        }

        return $options;
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getAdminUser()
    {
        if (!$this->hasData('expected_admin')) {
            /** @var $helper Ophirah_Qquoteadv_Helper_Data */
            $helper = Mage::helper('qquoteadv');
            $quoteId = $this->getCustomerSession()->getQuoteadvId();
            /* @var $quote Ophirah_Qquoteadv_Model_Qqadvcustomer */
            $quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($quoteId);
            $admin = $helper->getExpectedQuoteAdmin($quote);
            $this->setData('expected_admin', $admin);
        }
        return $this->getData('expected_admin');
    }

    /**
     * @return boolean
     */
    public function displayAssignedTo()
    {
        if (!(bool)Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/auto_assign_login')) {
            return false;
        }

        if ((bool)Mage::getStoreConfig('qquoteadv_quote_frontend/shoppingcart_quotelist/show_admin_login')) {
            return true;
        }

        return $this->getAdminUser() !== null;
    }

    /**
     * @return string
     */
    public function getAdminLoginUrl()
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/index/login/");
    }

    /**
     * Check for the setting auto assign to previous salesrep
     *
     * @return bool
     */
    public function isAssignPreviousEnabled()
    {
        return (bool)Mage::getStoreConfig('qquoteadv_sales_representatives/quote_assignment/auto_assign_previous');
    }

    /**
     * Function that gets the remark of a given quote product
     *
     * @param null $quoteProduct
     * @param bool|true $response
     * @return null|string
     */
    public function getRemarks($quoteProduct = null, $response = true){
        if($quoteProduct instanceof Ophirah_Qquoteadv_Model_Qqadvproduct){
            if(is_string($quoteProduct->getData('client_request'))){
                return $quoteProduct->getData('client_request');
            }
        }

        if($response){
            return Mage::helper('qquoteadv')->__('Enter your comments at any time. Click Update Quote to save your changes.');
        } else {
            return null;
        }
    }

    /**
     * Save customer last URL in session
     * @return mixed
     */
    protected function setLastUrlInSession()
    {
        $lastUrl = Mage::helper('core/http')->getHttpReferer();
        $quoteadvUrl = Mage::getUrl('qquoteadv/index');
        if ($lastUrl != $quoteadvUrl) {
            Mage::getSingleton('core/session')->setQuoteadvLastUrl($lastUrl);
        } else {
            Mage::getSingleton('core/session')->setQuoteadvLastUrl(Mage::getUrl());
        }

        return $this;
    }

    /**
     * Get customer last URL from session
     * @return mixed
     */
    protected function getLastUrl()
    {

        $lastUrlFromSession = Mage::getSingleton('core/session')->getQuoteadvLastUrl();
        if (!empty ($lastUrlFromSession)) {
            $continueShoppingUrl = $lastUrlFromSession;
        }else{
            $continueShoppingUrl = Mage::getUrl();
        }
        return $continueShoppingUrl;
    }

    /**
     * Function that returns the product object to get the image from
     *
     * @param $product
     * @param null $childProduct
     * @return $product Mage_Catalog_Model_Product |$childProduct Mage_Catalog_Model_Product
     */
    public function getImageProduct($product, $childProduct = null)
    {
        return Mage::helper('qquoteadv/catalog_product_data')->getImageProduct($product, $childProduct);
    }

    /**
     * Add extra item data to a product
     *
     * Support for OrganicInternet_SimpleConfigurableProducts
     *
     * @param $product
     * @param $item
     * @return \Mage_Catalog_Model_Product
     */
    public function addItemDataToProduct($product, $item)
    {
        /** @var \Mage_Catalog_Model_Product $product */
        if ($item->getAttribute()) {
            try {
                $attribute = unserialize($item->getAttribute());
                if (is_array($attribute) && isset($attribute['cpid'])) {
                    $product->setCpid($attribute['cpid']);
                    $product->addCustomOption('info_buyRequest', $item->getAttribute());
                }
            } catch (Exception $exception) {
                //do nothing
            }
        }

        return $product;
    }

    /**
     * Get the SCP item renderer for a quote items
     *
     * @param $item
     * @return mixed
     */
    public function getItemRendererScp($item)
    {
        $product = $this->getProduct($item->getProductId());
        $quoteByProduct = Mage::helper('qquoteadv')->getQuoteItem($product, $item->getAttribute(), null, $item);
        $quoteItems = $quoteByProduct->getAllItems();
        foreach ($quoteItems as $_item) {
            $_item->setId($item->getId());
            if ($_item->getProductId() == $product->getId()) {
                $renderer = $this->getLayout()
                    ->createBlock('qquoteadv/checkout_miniquote_item_miniquoterenderer')
                    ->setTemplate('qquoteadv/checkout/quote/miniquoteitem.phtml')
                    ->setRenderedBlock($this);

                $renderer->setItem($_item);

                return $renderer;
            }
        }

        return false;
    }

    /**
     * Render optionlist from options array
     *
     * @param $options
     * @return string
     */
    public function getOptionHtml($options)
    {
        $html = '<dl class="item-options">';
        foreach ($options as $option) {
            $html .= '<dt>' . $option['label'] . '</dt>';
            $html .= '<dd>' . $option['value'] . '</dd>';
        }
        $html .= '</dl>';

        return $html;
    }
}
