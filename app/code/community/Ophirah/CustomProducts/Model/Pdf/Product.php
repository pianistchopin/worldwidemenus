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
 * Class Ophirah_CustomProducts_Model_Pdf_Product
 */
class Ophirah_CustomProducts_Model_Pdf_Product extends Ophirah_Qquoteadv_Model_Pdf_Qquote
{
    protected $_helper;

    /**
     * Id of quote object
     *
     * @var null
     */
    public $_quoteadvId = null;

    /**
     * Quoteadv pdf object
     *
     * @var null
     */
    public $_pdfModel = null;

    /**
     * @param $quote
     * @return $this
     */
    public function setQuote($quote){
        $this->_quoteadv = $quote;
        return $this;
    }

    /**
     * @param $pdfModel
     * @return $this
     */
    public function setPdfModel($pdfModel){
        $this->_pdfModel = $pdfModel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPdfModel(){
        return $this->_pdfModel;
    }

    /**
     * Draws a specific render for a custom product.
     *
     * @param $unit (product)
     * @param $page
     * @return mixed
     * @throws Zend_Pdf_Exception
     * @since 1.0.5
     */
    public function draw($unit, $page)
    {
        $_quote = Mage::getModel('qquoteadv/qqadvcustomer')->load($unit->getQuoteId());
        $showPrice = Mage::helper('qquoteadv')->isPriceByDefaultAllowed();

        $line = array();
        $drawItems = array();
        $lineHeight = $this->fontLineHeightDefault; // characters Line height
        $maxChar = $this->maxProductNameChars; // Max characters to split string

        $this->_setFontRegular($page, $this->fontSizeDefault);

        $productId = $unit->getProductId();
        $itemRequest = $unit->getClientRequest();

        if (!$productId){
            return null;
        }

        /** @var Mage_Catalog_Model_Product $item */
        $item = Mage::getModel('catalog/product')->setStoreId($_quote->getStoreId())->load($productId);
        $imageItem = $item;

        //$productInformation = $this->getProductInformation($unit, $item);
        $productInformation = $this->getProductInformation($unit);
        $superAttribute = $this->getOption($item, $unit->getAttribute());

        //render custom options
        $attr = $this->retrieveOptions($item, $superAttribute);


        // Draw product image
        $prodImage = $productInformation->getFullpath();
        if (is_file($prodImage)) {
            // get picture dimensions
            $image = Mage::helper('catalog/image')->init($imageItem, $this->defaultImg, $prodImage);
            if(!is_object($image) && !get_class($image) == 'Mage_Catalog_Helper_Image') {
                $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($image, $this->imgWidth, $this->imgHeight);
            } else {
                //file fallback
                $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($prodImage, $this->imgWidth, $this->imgHeight);
            }

            $x = $this->_leftRectPad;
            $y = $this->getPdfModel()->y - ($newDim['height'] - ($lineHeight - 3)); // aligning top of the image with text

            $pdfimage = $this->renderImage($prodImage);

            $page->drawImage($pdfimage, $x, $y, $x + $newDim['width'], $y + $newDim['height']);
        } else {
            //posible image url?
            $imageUrl = $imageItem->getData($this->defaultImg);
            if ($imageUrl !== 'no_selection' && !empty($imageUrl)) {
                if(is_file($imageUrl)){
                    if(file_get_contents($imageUrl, 0, null, 0, 1)){
                        // url has image
                        $imagePath = sys_get_temp_dir() . '/' . basename($imageUrl);
                        file_put_contents($imagePath, file_get_contents($imageUrl));

                        $pdfimage = $this->renderImage($imagePath);
                        unlink($imagePath);
                        $newDim = Mage::helper('qquoteadv/catalog_product_data')
                            ->getItemPictureDimensions($pdfimage, $this->imgWidth, $this->imgHeight);
                        $x = $this->_leftRectPad;
                        // aligning top of the image with text
                        $y = $this->getPdfModel()->y - ($newDim['height'] - ($lineHeight - 3));
                        $page->drawImage($pdfimage, $x, $y, $x + $newDim['width'], $y + $newDim['height']);

                    }
                }
            }
        }

        /**
         * in case Product name is longer than 45 chars / $maxChar /$this->maxProductNameChars
         * - it is written in a few lines
         */
        $name = $productInformation->getName();
        $line[] = array(
            'text' => Mage::helper('core/string')->str_split(strip_tags($name), $maxChar, true, true),
            'feed' => $this->leftMarginProductName,
            'font' => 'bold',
            'font_size' => $this->fontSizeBold,
            'height' => $lineHeight
        );

        // draw SKUs
        $sku = $productInformation->getSku();
        $text = array();
        foreach (Mage::helper('core/string')->str_split($sku, $this->maxSkuChars) as $part) {
            $text[] = $part;
        }
        $line[] = array(
            'text' => $text,
            'feed' => $this->leftMarginSku
        );

        $requestedProductData = Mage::getModel('qquoteadv/requestitem')
            ->getCollection()->setQuote($this->_quoteadv)
            ->addFieldToFilter('quote_id', $unit->getQuoteId())
            ->addFieldToFilter('quoteadv_product_id', $unit->getId())
            ->addFieldToFilter('product_id', $productId);
        $requestedProductData->getSelect()->order(array('product_id ASC', 'request_qty ASC'));
        // create Tier array with prices
        foreach ($requestedProductData as $reqProduct) {
            $tierPrices[$reqProduct['request_qty']] = $reqProduct['owner_cur_price'];
        }
        $currency = $_quote->getData('currency');
        $currentCurrencyCode = Mage::app()->getStore($_quote->getStoreId())->getCurrentCurrencyCode();
        Mage::getModel('customproducts/customproduct')->setCurrentCurrency($currentCurrencyCode, $_quote->getStoreId());

        //#tier price section
        $k = 0;
        $showCurrentTier = false; // set this to true to show current tier in sub tier list
        $txt1 = $txt2 = $txt3 = array();
        // Setting first price
        if ($showPrice) {
            //Mage::app()->getStore($_quote->getStoreId())->formatPrice(
            $price = Mage::app()->getStore($_quote->getStoreId())->formatPrice($tierPrices[$unit->getQty()], false);
            $row = $unit->getQty() * $tierPrices[$unit->getQty()];
            $rowTotal = Mage::app()->getStore($_quote->getStoreId())->formatPrice($row, false);
        } else {
            $price = $this->getPdfModel()->itemPriceReplace;
            $rowTotal = $this->getPdfModel()->rowTotalReplace;
        }

        $size = $this->fontSizeSmall;
        $productQty = $unit->getQty() * 1;
        $txt1[] = array('text' => $productQty, 'font' => 'regular', 'font_size' => $size);
        $txt2[] = $price; //480
        $txt3[] = $rowTotal; //542

        if (count($requestedProductData) > 1):
            foreach ($requestedProductData as $product) {
                if ($k > 0) {
                    $this->getPdfModel()->isSetTierPrice = 1;
                }
                //set first line
                $productQty = $product->getRequestQty() * 1;
                $priceProposal = $product->getOwnerCurPrice();

                $showTier = true;
                $price = Mage::app()->getStore($_quote->getStoreId())->formatPrice($priceProposal, false);
                $row = $productQty * $priceProposal;
                $rowTotal = Mage::app()->getStore($_quote->getStoreId())->formatPrice($row, false);
                if ($productQty == $unit->getQty()) {
                    $rowTotal .= "*";
                    if ($showCurrentTier === false) {
                        $showTier = false;
                    }
                }

                // add row total
                $this->getPdfModel()->totalPrice += $row;

                if ($showTier === true) {
                    if (!$showPrice) {
                        $price = $this->getPdfModel()->itemPriceReplace;
                        $rowTotal = $this->getPdfModel()->rowTotalReplace;
                    }
                    $size = $this->fontSizeSmall;
                    $font = 'italic';
                    $txt1[] = array('text' => $productQty, 'font' => $font, 'font_size' => $size); //405
                    $txt2[] = array('text' => $price, 'font' => $font, 'font_size' => $size); //480
                    $txt3[] = array('text' => $rowTotal, 'font' => $font, 'font_size' => $size); //542
                }
                $k++;
            }
        endif;

        Mage::getModel('customproducts/customproduct')->setCurrentCurrency($currentCurrencyCode, $_quote->getStoreId());
        $line[] = array(
            'text' => $txt1,
            'feed' => $this->leftMarginQty
        );

        $line[] = array(
            'text' => $txt2,
            'feed' => $this->leftMarginPrice
        );

        $line[] = array(
            'text' => $txt3,
            'feed' => $this->leftMarginSubtotal
        );

        $drawItems[0]['lines'][] = $line;
        unset($line);
        //#section 2
        $desc = Mage::getStoreConfig('qquoteadv_quote_emails/attachments/short_desc', $this->_quoteadv->getStoreId());
        if (!empty($desc) && false) {//[DISABLED]
            $shortDesc = strip_tags($item->getShortDescription());
            $shortDesc = str_replace("&nbsp;", ' ', $shortDesc);
            $shortDesc = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $shortDesc);
            $line[] = array(
                'text' => Mage::helper('core/string')->str_split($shortDesc, $this->maxShortDescChars, true, true),
                'feed' => $this->leftMarginProductName,
                'font' => 'italic',
                'font_size' => $this->fontSizeSmall,
                'height' => $this->fontSizeSmall + 2
            );
            $drawItems[1]['lines'][] = $line;
            unset($line);
        }

        //#section 3
        $text = array();
        if (count($attr) > 0) {

            foreach ($attr as $value) {
                if ($value !== '') {
                    $value = str_replace('&quot;', '"', $value);
                    $value = strip_tags($value);
                    $str = Mage::helper('core/string')->str_split($value, $this->maxAttributeLength, true, true);
                    foreach ($str as $valueA) {
                        if (!empty($valueA)) {
                            $text[] = $valueA;
                        }
                    }
                }
            }
        }

        $itemRequest = strip_tags($itemRequest);
        $itemRequest = wordwrap($itemRequest, $this->maxItemClientRequestChars, "\n", true);
        foreach (explode("\n", $itemRequest) as $value) {
            if (!empty($value)) {
                $value = str_replace("\r", "", $value);
                $text[] = $value;
            }
        }
        $text[] = '';

        if(isset($newDim['height'])){
            $minHeight = $newDim['height'];
        } else {
            $minHeight = 0;
        }
        $line[] = array(
            'text' => $text,
            'feed' => $this->leftMarginProductName,
            'font' => 'italic',
            'min-height' => $minHeight

        );
        $drawItems[2]['lines'][] = $line;
        $page = $this->getPdfModel()->drawLineBlocks($page, $drawItems, array('table_header' => true));

        return $page;
    }

    /**
     * Retrieves product information by product attributes.
     * @param $unit
     * @return Varien_Object
     * @since 1.0.5
     */
    public function getProductInformation($unit){
        return $this->getHelper()->getProductOptionValues($unit->getAttribute());
    }

    /**
     * Unset specific options.
     * @param $item
     * @param $superAttribute
     * @return array|void
     * @since 1.0.5
     */
    public function retrieveOptions($item, $superAttribute){
        $superAttribute = $this->getHelper()->filterOptions($superAttribute);
        return parent::retrieveOptions($item, $superAttribute);
    }

    /**
     * Retrieves custom pro helper.
     * @return Ophirah_CustomProducts_Model_Customproduct
     * @since 1.0.5
     */
    protected function getHelper(){
        if(!$this->_helper){
            $this->_helper = Mage::getModel('customproducts/customproduct');
        }
        return $this->_helper;
    }

}
