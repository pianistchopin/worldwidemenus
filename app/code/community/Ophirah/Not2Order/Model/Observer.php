<?php

class Ophirah_Not2Order_Model_Observer
{

    public function setSaleableState($observer)
    {
        $enabled = Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/enabled');

        $product = $this->getProduct($observer);


        if ($enabled == '1') {
            if ($product->getAllowedToOrdermode() == 0) {
                $parentProduct = $this->getParentProduct($observer);
                if (!($this->excludeConfigurable($parentProduct) || $this->excludeBundle($parentProduct))) {
                    $observer->getEvent()->getData('salable')->setData('is_salable', false);
                }
            }
        }
    }

    public function removeFromCart($observer)
    {
        $product = $observer->getProduct();
        $quoteItem = $observer->getQuoteItem();
        if (!$product->isSaleable()) {
            //remove item from quote
            if ($quoteItem->getParentItem() == NULL) {
                $quoteItem->getQuote()->removeItem($quoteItem->getId());
            }
            Mage::throwException(Mage::helper('not2order')->__('You can not add %s to your cart', $product->getName()));
        }
    }

    /**
     * This function set the default 'allowed_to_ordermode' attribute value
     * It listens to admin_system_config_changed_section_qquoteadv observer
     */
    public function defaultNot2OrderAttribute()
    {
        $defaultNot2OrderAttributeValue = (int)Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/default_not2order_attribute_value');

        $defaultAllowedToOrdermode = (int)Mage::getResourceModel('eav/entity_attribute_collection')
            ->setCodeFilter('allowed_to_ordermode')
            ->getFirstItem()
            ->getDefaultValue();

        if ($defaultNot2OrderAttributeValue !== $defaultAllowedToOrdermode) {
            if ($defaultNot2OrderAttributeValue == 0) {
                $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
                $setup->updateAttribute(
                    'catalog_product', 'allowed_to_ordermode', array(
                        'default_value' => '0',
                    )
                );
            }
            if ($defaultNot2OrderAttributeValue == 1) {
                $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
                $setup->updateAttribute(
                    'catalog_product', 'allowed_to_ordermode', array(
                        'default_value' => '1',
                    )
                );
            }
        }
    }

    /**
     * Exclude the bundle child products from the 'allow to order' check.
     * @param Mage_Catalog_Model_Product $parentProduct
     * @return bool
     */
    protected function excludeConfigurable(Mage_Catalog_Model_Product $parentProduct)
    {
        $exclude = false;
        $enabled = Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/exclude_configurable');
        if ($enabled) {
            $exclude = $parentProduct->isConfigurable();
        }
        return $exclude;
    }

    /**
     * Exclude the bundle child products from the 'allow to order' check.
     * @param Mage_Catalog_Model_Product $parentProduct
     * @return bool
     */
    protected function excludeBundle(Mage_Catalog_Model_Product $parentProduct)
    {
        $exclude = false;
        $enabled = Mage::getStoreConfig('qquoteadv_general/orderability_and_prices/exclude_bundle');
        if ($enabled) {
            $exclude = $parentProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE;
        }
        return $exclude;
    }

    /**
     * Retrieves the parent product. If there is no parent product it will return a false.
     * @param $observer
     * @return Mage_Core_Model_Abstract
     */
    protected function getParentProduct($observer)
    {
        $parentId = $observer->getSalable()->getProduct()->getParentId(); // in case of configurable
        if (!isset($parentId)) {
            $parentId = $observer->getSalable()->getProduct()->getParentProductId(); // in case of bundle
        }

        $parentProduct = Mage::getModel('catalog/product')->load($parentId);
        return $parentProduct;
    }

    /**
     * Retrieves the product from the observer.
     * @param $observer
     * @return Mage_Core_Model_Abstract
     */
    protected function getProduct($observer)
    {
        $productId = $observer->getEvent()->getProduct()->getId();
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product;
    }
}