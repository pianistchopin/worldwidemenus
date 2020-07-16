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
 * Class Ophirah_Qquoteadv_Model_Qqadvproductdownloadable
 */
class Ophirah_Qquoteadv_Model_Qqadvproductdownloadable extends Mage_Core_Model_Abstract
{
    /**
     * Construct function
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/qqadvproductdownloadable');
    }

    /**
     * Sets the available links from the qqadvproduct to this object.
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $qquoteadvProduct
     * @return $this
     */
    public function loadProduct(Ophirah_Qquoteadv_Model_Qqadvproduct $qquoteadvProduct)
    {
        $links = false;
        $this->setQquoteadvProduct($qquoteadvProduct);
        $product = Mage::getModel('catalog/product')->load($qquoteadvProduct->getProductId());

        if ($this->isDownloadable($product)) {
            $this->setProduct($product);
            $availibleLinksOnProduct = $product->getTypeInstance(true)->getLinks($product);

            $collection = Mage::getModel('qquoteadv/qqadvproductdownloadable')->getCollection()
                ->addFieldToFilter('product_id', $qquoteadvProduct->getId());

            foreach ($collection as $item) {
                $link = Mage::getModel('downloadable/link')->load($item->getLinkId());
                if ($link instanceof Mage_Downloadable_Model_Link && $link->getLinkId()) {
                    foreach ($availibleLinksOnProduct as $linkOnProduct) {
                        if ($linkOnProduct->getLinkId() == $link->getLinkId()) {
                            $links[] = $linkOnProduct;
                            break;
                        }
                    }

                }
            }
            $this->setData('links', $links);
        }

        return $this;
    }

    /**
     * When multiple links are given for a product then this function will filter the links and add them to the qquoteadv_product_downloadable table.
     * @param $qquoteadvProductId
     * @param $links
     */
    public function setLinksForProduct($qquoteadvProductId, $links){
        if(is_array($links)){
            foreach($links as $linkId){
                $this->setLinkForProduct($qquoteadvProductId, $linkId);
            }
        }
    }

    /**
     * Sets the link_id and product_id in the quoteadv_product_downloadable table.
     * This is used to determine what link(s) is/are attached to a downloadable product.
     * @param $qquoteadvProductId
     * @param $linkId
     * @throws Exception
     */
    public function setLinkForProduct($qquoteadvProductId, $linkId){
        $qquoteadvProduct = Mage::getModel('qquoteadv/qqadvproduct')->load($qquoteadvProductId);

        if($qquoteadvProduct instanceof Ophirah_Qquoteadv_Model_Qqadvproduct && $qquoteadvProduct->getId()){
            $downloadableProduct = Mage::getModel('catalog/product')->load($qquoteadvProduct->getProductId());

            if($this->isDownloadable($downloadableProduct)){
                $link = $this->_getLink($linkId);

                if($link instanceof Mage_Downloadable_Model_Link && $link->getLinkId()){
                    if(!$this->containsLink($qquoteadvProduct, $link)){
                        try{
                            Mage::getModel('qquoteadv/qqadvproductdownloadable')
                                ->setProductId($qquoteadvProduct->getId())
                                ->setLinkId($link->getLinkId())
                                ->save();
                        }catch(Exception $e){
                            Mage::log('Exception: ' .$e->getMessage(), null, 'c2q_exception.log', true);
                        }
                    }
                }
            }
        }
    }

    /**
     * Checks if a qqadvproduct contains a link
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $qquoteadvProduct
     * @param Mage_Downloadable_Model_Link $compareLink
     * @return bool
     */
    public function containsLink(Ophirah_Qquoteadv_Model_Qqadvproduct $qquoteadvProduct, Mage_Downloadable_Model_Link $compareLink){
        if(!$this->getLinks()){
            $this->loadProduct($qquoteadvProduct);
        }

        $containsLink = false;
        if($this->getLinks()){
            foreach($this->getLinks() as $link){
                if($link->getId() == $compareLink->getLinkId()){
                    $containsLink = true;
                }
            }
        }
        return $containsLink;
    }

    /**
     * Checks if a downloadable product (Ophirah_Qquoteadv_Model_Qqadvproduct) exists with the same links.
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $qquoteadvProduct
     * @param $links
     * @return bool
     */
    public function exists(Ophirah_Qquoteadv_Model_Qqadvproduct $qquoteadvProduct, $links){
        if(!$this->getLinks()){
            $this->loadProduct($qquoteadvProduct);
        }

        $exists = false;
        if(is_array($links)){
            if($this->getCountLinks() == count($links)){
                $haveTheSameLinks = true;
                foreach($links as $linkId){
                    $link = $this->_getLink($linkId);
                    if(!($this->containsLink($qquoteadvProduct, $link))){
                        $haveTheSameLinks = false;
                    }
                }
                if($haveTheSameLinks){
                    $exists = true;
                }
            }
        }
        return $exists;
    }


    /**
     * Return title of links section
     *
     * @return string
     */
    public function getLinksTitle()
    {
        return Mage::helper('downloadable/catalog_product_configuration')->getLinksTitle($this->getProduct());
    }

    /**
     * Checks if a Mage_Catalog_Model_Product is a downloadable Type
     * @param $product
     * @return bool
     */
    public function isDownloadable($product){
        if ($product instanceof Mage_Catalog_Model_Product &&
            $product->getId() &&
            $product->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
        ) {
            return true;
        } elseif ($product instanceof Mage_Sales_Model_Quote_Item &&
            $product->getItemId() &&
            $product->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Counts the available links after load
     * @return int
     */
    public function getCountLinks(){
        $count = 0;
        if(is_array($this->getLinks())){
            $count = count($this->getLinks());
        }
        return $count;
    }

    /**
     * Gets the links from the params submitted in a Magento form.
     * @param $params
     * @return array|false
     */
    public function getLinksFromParams($params){
        $links = false;
        if(is_array($params) && array_key_exists('links', $params)){
            $links = $params['links'];
        }
        return $links;
    }

    /**
     * Function that sets to downloadable links on the downloadable product
     *
     * @param Ophirah_Qquoteadv_Model_Qqadvproduct $qquoteadvProduct
     * @return mixed
     */
    public function prepareDownloadableProduct(Ophirah_Qquoteadv_Model_Qqadvproduct $qquoteadvProduct){
        if(!$this->getLinks()){
            $this->loadProduct($qquoteadvProduct);
        }

        $product = Mage::getModel('catalog/product')->load($qquoteadvProduct->getProductId());

        if($product->getId() && $this->getLinks()) {
            $links = '';
            foreach ($this->getLinks() as $link) {
                $links[] .= $link->getLinkId();
            }
            $product->addCustomOption('downloadable_link_ids', implode(',', $links));
        }
        return $product;
    }

    /**
     * Function that sets to downloadable links on the downloadable product from the buyrequest
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return Mage_Sales_Model_Quote_Item
     */
    public function prepareDownloadableProductFromBuyRequest(Mage_Sales_Model_Quote_Item $item){
        $buyRequest = $item->getBuyRequest();
        if($buyRequest){
            $links = $buyRequest->getLinks();
            if($links){
                $item->addOption(new Varien_Object(
                    array(
                        'product' => $item->getProduct(),
                        'code' => 'downloadable_link_ids',
                        'value' => implode(',', $links)
                    )
                ));
            }
        }
        return $item;
    }

    /**
     * Function to get the link from a downloadable option
     *
     * @param $linkId
     * @return bool|Mage_Downloadable_Model_Link
     */
    private function _getLink($linkId)
    {
        $link = false;
        if (!($linkId instanceof Mage_Downloadable_Model_Link)) {
            try {
                $link = Mage::getModel('downloadable/link')->load($linkId);
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            }
        } else {
            $link = $linkId;
        }

        return $link;
    }
}
