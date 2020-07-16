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
 * Class Ophirah_Qquoteadv_Model_Pdf_Qquote
 */
class Ophirah_Qquoteadv_Model_Pdf_Qquote extends Mage_Sales_Model_Order_Pdf_Abstract
{
    //font sizes
    public $fontSizeSmall                   = 7;    //8
    public $fontSizeDefault                 = 8;    //9
    public $fontLineHeightDefault           = 11;   //12
    public $fontSizeBold                    = 11;   //12
    public $remarkLength                    = 155;  //140

    //table margin
    public $leftMarginProductName           = 110; //($this->_leftTextPad + $this->imgWidth - 5)
    public $leftMarginSku                   = 340;
    public $leftMarginQty                   = 440;
    public $leftMarginPrice                 = 470;
    public $leftMarginSubtotal              = 520;

    //max length of text lines
    public $maxProductNameChars             = 42;   //45-3
    public $maxShortDescChars               = 109;  //115-6
    public $maxItemClientRequestChars       = 69;   //80-11
    public $maxGlobalClientRequestChars     = 94;   //115-21
    public $maxSkuChars                     = 25;
    public $maxAttributeLength              = 60;
    public $maxAddressLength                = 60;

    //"QUOTATION", quote data, totals margin
    public $floatRightMargin                = 335;

    //max content width (like the width of the boxes)
    public $maxContentWidth                 = 570;

    //distance from quote data lable (used for the quote id)
    public $distanceFromQuoteDataLable      = 85;

    //needed spacing
    public $minimalSpaceNeededForProduct    = 90;
    public $minimalSpaceNeededForTotals     = 60;
    public $minimalSpaceNeededForRemark     = 30;

    //Width and Height of the image in the product table
    public $imgWidth                        = 60;
    public $imgHeight                       = 60;

    //The left margin for the start position of rectangles
    public $_leftRectPad                    = 45;

    //The left margin for the start position of text
    public $_leftTextPad                    = 55;

    //Position for shipping address
    public $_midTextPad                     = 220;

    //highest point by vercical axis
    public $y                               = 800;
    public $yOrg                            = 800;

    //lowest position by vercical axis
    public $_minPosY                        = 15;

    //Default Magento PDF logo size
    public $magentoPdfLogoWidth             = 200;
    public $magentoPdfLogoHeight            = 50;

    //Image rendering settings
    public $renderOptimizedImages           = 1;
    public $renderAlphaChannel              = 'FFFFFF';
    public $renderGifImagesAs               = 2;
    public $renderGifAlphaChannel           = 'FFFFFF';
    public $renderPngImagesAs               = 2;
    public $renderPngAlphaChannel           = 'FFFFFF';
    public $jpegQuality                     = 70;

    //save space by using an other font (not magento default)
    public $saveSpaceOnFonts                = false;

    /**
     * This value is used in the Magento image resize functions
     *
     * @var string
     */
    public $defaultImg = 'thumbnail'; // options: 'image', 'small_image','thumbnail'

    /**
     * Tracks the amount of total rows
     *
     * @var int
     */
    public $totalCounter = 0;

    /**
     * Id of quote object
     *
     * @var null|int
     */
    public $_quoteadvId = null;

    /**
     * Quote object
     *
     * @var Ophirah_Qquoteadv_Model_Qqadvcustomer $_quoteadv
     */
    public $_quoteadv = null;

    /**
     * Store object of quote
     *
     * @var null
     */
    public $quoteStore = null;

    /**
     * save product name to avoid dublicate display produc't names
     *
     * @var string
     */
    public $_prevItemName = '';

    /**
     * @var array
     */
    public $_prevItemOptions = array();

    /**
     * @var null
     */
    public $_itemId = null;

    /**
     * @var array
     */
    public $columns = array();

    /**
     * @var
     */
    public $pdf;

    /**
     * @var
     */
    public $requestId;

    /**
     * @var null
     */
    public $latestY = null;

    /**
     * @var int
     */
    public $totalPrice = 0;

    /**
     * @var int
     */
    public $isSetTierPrice = 0;

    /**
     * Show item price
     *
     * @var string
     */
    public $itemPriceReplace = ' ';

    /**
     * @var string
     */
    public $rowTotalReplace = '--';

    /**
     * @var null
     */
    protected $_currentPage = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->renderOptimizedImages = Mage::getStoreConfig('qquoteadv_advanced_settings/pdf_rendering/optimize_image_performance');
        if($this->renderOptimizedImages == 0){
            $this->renderAlphaChannel = Mage::getStoreConfig('qquoteadv_advanced_settings/pdf_rendering/alpha_channel');
        } else {
            $this->renderPngImagesAs = Mage::getStoreConfig('qquoteadv_advanced_settings/pdf_rendering/png');
            $this->renderGifImagesAs = Mage::getStoreConfig('qquoteadv_advanced_settings/pdf_rendering/gif');
            if($this->renderPngImagesAs == 2){
                $this->renderPngAlphaChannel = Mage::getStoreConfig('qquoteadv_advanced_settings/pdf_rendering/png_alpha');
            } elseif($this->renderGifImagesAs == 2) {
                $this->renderGifAlphaChannel = Mage::getStoreConfig('qquoteadv_advanced_settings/pdf_rendering/gif_alpha');
            }
        }

        //saveSpaceOnFonts
        $this->saveSpaceOnFonts = Mage::getStoreConfig('qquoteadv_advanced_settings/pdf_rendering/save_space_on_fonts');
    }

    /**
     * Set font as regular
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        if($this->saveSpaceOnFonts){
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES, Zend_Pdf_Font::EMBED_DONT_EMBED);
        } else {
            $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
        }

        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        if($this->saveSpaceOnFonts){
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD, Zend_Pdf_Font::EMBED_DONT_EMBED);
        } else {
            $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        }

        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        if($this->saveSpaceOnFonts){
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ITALIC, Zend_Pdf_Font::EMBED_DONT_EMBED);
        } else {
            $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        }
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Image rendering for PDF
     *
     * @param $imagePath
     * @return Zend_Pdf_Image|null
     * @throws Zend_Pdf_Exception
     */
    protected function renderImage($imagePath)
    {
        $image = null;
        $imageType = $this->getImageType($imagePath);
        $converted = false;

        if ($imageType == 'PNG') {
            $source = imagecreatefrompng($imagePath);
            $converted = $this->convertImageBasedOnSettings($source, $this->renderPngAlphaChannel);
        } elseif ($imageType == 'GIF') {
            $source = imagecreatefromgif($imagePath);
            $converted = $this->convertImageBasedOnSettings($source, $this->renderGifAlphaChannel);
        }

        try {
            if ($converted != false) {
                $image = Zend_Pdf_Image::imageWithPath($converted);
                unlink($converted);
            } else {
                $image = Zend_Pdf_Image::imageWithPath($imagePath);
            }
        } catch (Zend_Pdf_Exception $exception) {
            Mage::log('imageType: ' . $imageType, null, 'c2q_pdf.log', true);
            Mage::log('imagePath: ' . $imagePath, null, 'c2q_pdf.log', true);
            Mage::log('Exception: ' . $exception->getMessage(), null, 'c2q_pdf.log', true);
        }

        //fallback when an issue occured
        if (!isset($image) || empty($image)) {
            $image = $this->renderPlaceholderImage();
        }

        return $image;
    }

    /**
     * Return a palceholder image
     *
     * @return null|\Zend_Pdf_Resource_Image
     */
    protected function renderPlaceholderImage()
    {
        $image = null;
        $imagePath = '';

        //use the user set default placeholder
        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        $placeholder = Mage::getStoreConfig("catalog/placeholder/thumbnail_placeholder");
        if ($placeholder && file_exists($baseDir . '/placeholder/' . $placeholder)) {
            $imagePath = $baseDir . '/placeholder/' . $placeholder;
        } else {
            $placeholder = Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
            if ($placeholder && file_exists($baseDir . '/placeholder/' . $placeholder)) {
                $imagePath = $baseDir . '/placeholder/' . $placeholder;
            } else {
                $placeholder = Mage::getStoreConfig("catalog/placeholder/image_placeholder");
                if ($placeholder && file_exists($baseDir . '/placeholder/' . $placeholder)) {
                    $imagePath = $baseDir . '/placeholder/' . $placeholder;
                }
            }
        }

        //if still no file, use the default magento file
        if (!isset($imagePath) || empty($imagePath)) {
            $baseDir = Mage::getDesign()->getSkinBaseDir(array('_area' => 'frontend'));
            $placeholder = "/images/catalog/product/placeholder/thumbnail.jpg";
            if (file_exists($baseDir . $placeholder)) {
                $imagePath = $baseDir . $placeholder;
            } else {
                $placeholder = "/images/catalog/product/placeholder/small_image.jpg";
                if ($placeholder && file_exists($baseDir . $placeholder)) {
                    $imagePath = $baseDir . $placeholder;
                } else {
                    $placeholder = "/images/catalog/product/placeholder/image.jpg";
                    if ($placeholder && file_exists($baseDir . $placeholder)) {
                        $imagePath = $baseDir . $placeholder;
                    }
                }
            }
        }

        $imageType = $this->getImageType($imagePath);
        $converted = false;

        if ($imageType == 'PNG') {
            $source = imagecreatefrompng($imagePath);
            $converted = $this->convertImageBasedOnSettings($source, $this->renderPngAlphaChannel);
        } elseif ($imageType == 'GIF') {
            $source = imagecreatefromgif($imagePath);
            $converted = $this->convertImageBasedOnSettings($source, $this->renderGifAlphaChannel);
        }

        try {
            if ($converted != false) {
                $image = Zend_Pdf_Image::imageWithPath($converted);
                unlink($converted);
            } else {
                $image = Zend_Pdf_Image::imageWithPath($imagePath);
            }
        } catch (Zend_Pdf_Exception $exception) {
            Mage::log('imageType: ' . $imageType, null, 'c2q_pdf.log', true);
            Mage::log('imagePath: ' . $imagePath, null, 'c2q_pdf.log', true);
            Mage::log('Exception: ' . $exception->getMessage(), null, 'c2q_pdf.log', true);
        }

        return $image;
    }

    /**
     * Get temporary converted image
     *
     * @param $source
     * @param $alphaChannel
     * @return String Path to new image
     */
    protected function convertImageBasedOnSettings($source, $alphaChannel)
    {
        if ($this->renderOptimizedImages > 0) {
            //It is preferred to use Jpeg instead of PNG
            $path = $this->createJpegImage($source);
        } else {
            //If optimized images is turned off do as user specified
            switch ($this->renderPngImagesAs) {
                case 1:
                    $path = $this->createPngImage($source);
                    break; //8 bit png
                case 2:
                    $path = $this->createJpegImage($source, $alphaChannel);
                    break; //jpeg
                default:
                    $path = false;
            }
        }

        return $path;
    }

    /**
     * Create a 8bit PNG image
     *
     * @param $sourceImage
     * @return String path //path to temporary png image
     */
    protected function createPngImage($sourceImage)
    {
        //Create a new 8bit PNG image
        $image = imagecreatetruecolor(imagesx($sourceImage), imagesy($sourceImage));
        $bga = imagecolorallocatealpha($image, 0, 0, 0, 127);

        //Set transparancy
        imagecolortransparent($image, $bga);
        imagefill($image, 0, 0, $bga);

        //Duplicate current image
        imagecopy($image, $sourceImage, 0, 0, 0, 0, imagesx($sourceImage), imagesy($sourceImage));
        imagetruecolortopalette($image, false, 255);
        imagesavealpha($image, true);

        //Convert new image to file
        $imagePath = sys_get_temp_dir() . '/' . md5(rand() . time()) . '.png';
        imagepng($image, $imagePath);
        imagedestroy($image); //Free allocated memory by image.

        return $imagePath;
    }

    /**
     * Create a Jpeg Image
     *
     * @param $sourceImage
     * @param string $alpha
     * @return String path //path to temporary Jpeg image
     */
    protected function createJpegImage($sourceImage, $alpha = 'FFFFFF')
    {
        //Get numeric RGB value
        $rgb = $this->hexToRgb($alpha);

        //Create a background for the JPEG file
        $image = imagecreatetruecolor(imagesx($sourceImage), imagesy($sourceImage));
        imagefill($image, 0, 0, imagecolorallocate($image, $rgb['R'], $rgb['G'], $rgb['B']));
        imagealphablending($image, TRUE);

        //Duplicate the current image to the background
        imagecopy($image, $sourceImage, 0, 0, 0, 0, imagesx($sourceImage), imagesy($sourceImage));

        //Convert new image to file
        $imagePath = sys_get_temp_dir() . '/' . md5(rand() . time()) . '.jpg';
        $quality = $this->jpegQuality; //70~100
        imagejpeg($image, $imagePath, $quality);
        imagedestroy($image); //Free allocated memory by image

        return $imagePath;
    }

    /**
     * Get image type
     *
     * @param $imagePath
     * @return String filetype
     */
    protected function getImageType($imagePath)
    {
        if (is_file($imagePath)) {
            if(function_exists('exif_imagetype')) {
                //Check actual file type
                $imageType = exif_imagetype($imagePath);
                if($imageType == IMAGETYPE_GIF) {
                    return 'GIF';
                } else if($imageType == IMAGETYPE_JPEG){
                    return 'JPEG';
                } else if($imageType == IMAGETYPE_PNG){
                    return 'PNG';
                } else if($imageType == IMAGETYPE_SWF){
                    return 'SWF';
                } else if($imageType == IMAGETYPE_PSD){
                    return 'PSD';
                } else if($imageType == IMAGETYPE_BMP){
                    return 'BMP';
                } else if($imageType == IMAGETYPE_TIFF_II){
                    return 'TIFF_II';
                } else if($imageType == IMAGETYPE_TIFF_MM){
                    return 'TIFF_MM';
                } else if($imageType == IMAGETYPE_ICO){
                    return 'ICO';
                } else {
                    return 'Unsupported file type';
                }
            } else {
                //Check file type based on extension
                $ext = strtoupper(pathinfo($imagePath, PATHINFO_EXTENSION));
                $extensions = array("GIF", "JPEG", "PNG", "SWF", "PSD", "BMP", "TIFF_II", "TIFF_MM", "ICO");
                if(in_array($ext, $extensions)){
                    return $ext;
                } else {
                    return 'Unsupported file type';
                }
            }
        } else {
            return 'File does not exist';
        }
    }

    /**
     * Getter for _currentPage
     *
     * @var String hex ( #ffffff )
     *
     * @return array
     */
    protected function hexToRgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));

        return array('R' => $r, 'G' => $g, 'B' => $b);
    }


    /**
     * Setter for _currentPage
     *
     * @param $page
     * @return $this
     */
    protected function setCurrentPage($page)
    {
        $this->_currentPage = $page;
        return $this;
    }

    /**
     * Getter for _currentPage
     *
     * @return null
     */
    protected function getCurrentPage()
    {
        return $this->_currentPage;
    }

    /**
     * Adds the store logo to the pdf
     *
     * @param $page
     * @param null $store
     * @return bool isLogoPrinted
     */
    protected function insertLogo(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $x = $this->_leftRectPad;
            $y = $this->y;
            $image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = $this->renderImage($image);
                $page->drawImage($image, $x, $y - $this->magentoPdfLogoHeight, $x + $this->magentoPdfLogoWidth, $y);
                $this->y = $y - $this->magentoPdfLogoHeight;

                return true;
            }
        }

        return false;
    }

    /**
     * Function that inserts the address on the PDF page
     *
     * @param $page
     * @param null $store
     * @param bool $isLogoPrinted
     */
    protected function insertAddress(&$page, $store = null, $isLogoPrinted = false)
    {
        $y = $this->yOrg - $this->fontSizeBold;
        $x = $this->_leftRectPad + $this->floatRightMargin; //= 380
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        //add QUOTATION mark
        $y = $this->insertQuotationMark($page, $isLogoPrinted, $x, $y);

        $storeAddress = Mage::getStoreConfig('sales/identity/address', $store);
        $itemRequest = wordwrap(rtrim($storeAddress), $this->maxAddressLength, "\n", true);
        $lines = explode("\n", $itemRequest);
        foreach ($lines as $value) {
            $value = str_replace("\r", "", $value);
            $value = str_replace("\n", "", $value);
            $page->drawText(trim(strip_tags($value)), $x, $y, 'UTF-8');
            $y -= $this->fontSizeDefault;
        }

        //in case when address text height is more than logo height
        if($this->yOrg == $this->y){
            //no logo set
            $this->y = $y;
        } else {
            //only overwrite when address has more height
            if($this->y > $y){
                $this->y = $y;
            }
        }
    }

    /**
     * Adds a new page to the PDF
     *
     * @return mixed
     */
    public function addNewPage()
    {
        /* Add new table head */
        $page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $page;
        $this->setCurrentPage($page);
        $this->y = $this->yOrg;

        $this->_setFontRegular($page);
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle($this->_leftRectPad, $this->y, $this->maxContentWidth, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        foreach ($this->columns as $item) {
            $textLabel = $item[0];
            $xx = $item[1];
            $yy = $this->y;
            $textEncod = $item[3];

            $page->drawText($textLabel, $xx, $yy, $textEncod);
        }

        $this->y -= 20;
        return $page;
    }

    /**
     * Function that generates the PDF for a give quote
     *
     * @param array $quotes
     * @return Zend_Pdf
     */
    public function getPdf($quotes = array())
    {
        $this->_beforeGetPdf();

        $this->pdf = new Zend_Pdf();
        $style = new Zend_Pdf_Style();

        $this->_setFontBold($style, $this->fontSizeBold);

        if ($quotes instanceof Ophirah_Qquoteadv_Model_Qqadvcustomer) {
            $this->_quoteadvId = $quotes->getId();
            $this->_quoteadv = $quotes;
        } else {
            foreach ($quotes as $item) {
                $this->_quoteadvId = $item['quote_id'];
            }

            $quoteadv = $this->getQuote($this->_quoteadvId);
            $quoteadv->collectTotals();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_beforesafe_final', array('quote' => $quoteadv));
            $quoteadv->save();
            Mage::dispatchEvent('qquoteadv_qqadvcustomer_aftersafe_final', array('quote' => $quoteadv));
            $this->_quoteadv = $quoteadv;

        }

        if ($this->_quoteadv->getStoreId()) {
            Mage::app()->getLocale()->emulate($this->_quoteadv->getStoreId());
            $this->quoteStore = Mage::app()->getStore($this->_quoteadv->getStoreId());
        }

        $page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->setCurrentPage($page);
        $page = $this->getCurrentPage();
        $this->pdf->pages[] = $page;

        /* Add image */
        $isLogoPrinted = $this->insertLogo($page, $this->_quoteadv->getStoreId());

        /* Add address */
        $this->insertAddress($page, $this->_quoteadv->getStoreId(), $isLogoPrinted);

        /* Add head */
        $this->insertTitles($page, $this->_quoteadvId, $this->_quoteadv->getStoreId());

        /* Only print shipping options when no shipping method is selected */
        $shippingCode = $this->_quoteadv->getShippingCode();
        if(empty($shippingCode)){
            $shippingCode = $this->_quoteadv->getShippingMethod();
        }

        if(!isset($shippingCode) || empty ($shippingCode)) {
            $this->insertShippingMethods();
        } else {
            $this->insertCurrentShippingMethod();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, $this->fontSizeDefault);

        $rectHeight = 15;
        /* Add table */
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        $page->drawRectangle($this->_leftRectPad, $this->y, $this->maxContentWidth, $this->y - $rectHeight);

        $this->y -= 10;
        /* Add table head */
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.4, 0.4, 0.4));

        $this->columns = array(
            array(Mage::helper('catalog')->__('Product image'), $this->_leftTextPad, $this->y, 'UTF-8'),
            array(Mage::helper('catalog')->__('Product name'),  $this->leftMarginProductName, $this->y, 'UTF-8'),
            array(Mage::helper('catalog')->__('SKU'),           $this->leftMarginSku, $this->y, 'UTF-8'),
            array(Mage::helper('catalog')->__('QTY'),           $this->leftMarginQty, $this->y, 'UTF-8'),
            array(Mage::helper('catalog')->__('Price'),         $this->leftMarginPrice, $this->y, 'UTF-8'),
            array(Mage::helper('adminhtml')->__('Subtotal'),    $this->leftMarginSubtotal, $this->y, 'UTF-8')
        );

        //#draw TABLE TITLES
        foreach ($this->columns as $item) {
            $textLabel = $item[0];
            $textPosX = $item[1];
            $textPosY = $item[2];
            $textEncod = $item[3];

            $page->drawText($textLabel, $textPosX, $textPosY, $textEncod);
        }

        $this->y -= 18;
        if ($this->_minPosY + 60 > $this->y) {
            $page = $this->addNewPage();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        $requestItems = Mage::getModel('qquoteadv/qqadvproduct')->getCollection()
            ->addFieldToFilter('quote_id', $this->_quoteadvId);

        /* Add body */
        foreach ($requestItems as $product) {
            if ($this->_minPosY + $this->minimalSpaceNeededForProduct > $this->y) {
                $page = $this->addNewPage();
            }
            /* Draw item */
            if(Mage::helper('core')->isModuleEnabled('Ophirah_CustomProducts') && Mage::getModel('customproducts/customproduct')->isCustomProduct($product->getProductId())){
                $page = Mage::getModel('customproducts/pdf_product')->setQuote($this->_quoteadv)->setPdfModel($this)->draw($product, $page);
            }else{
                $page = $this->draw($product, $page);
            }
        }

        if ($this->_minPosY + $this->minimalSpaceNeededForTotals > $this->y) {
            $this->addNewPage();
        }

        /* Add total */
        $page = $this->getCurrentPage();
        $this->insertTotal($page);

        if ($this->_minPosY + $this->minimalSpaceNeededForRemark > $this->y) {
            $page = $this->addNewPage();
        }

        /* Add quote2cart general remark */
        $this->insertGeneralRemark($page);

        if ($this->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }

        $this->_afterGetPdf();

        return $this->pdf;
    }

    /**
     * Function that renders a configurable product
     *
     * @param $product
     * @param $item
     * @return array
     */
    protected function _renderConfigurable($product, $item)
    {
        $html = array();

        $x = Mage::helper('qquoteadv')->getQuoteItem($product, $item->getAttribute());

        foreach ($x->getAllItems() as $_zz) {
            if ($_zz->getProductId() == $product->getId()) {
                $obj = new Ophirah_Qquoteadv_Block_Item_Renderer_Configurable;
                $obj->setTemplate('qquoteadv/item/configurable.phtml');
                $obj->setItem($_zz);

                $_options = $obj->getOptionList();
                if ($_options) {
                    foreach ($_options as $_option) {
                        $_formatedOptionValue = $obj->getFormatedOptionValue($_option);
                        $html[] = $_option['label'];
                        $html[] = '  ' . $_formatedOptionValue['value'];
                    }
                }
            }
        }

        return $html;
    }

    /**
     * Function that renders a downloadable product
     *
     * @param $product
     * @param $item
     * @return array
     */
    protected function _renderDownloadable($product, $item)
    {
        $html = array();
        $qqadvproductdownloadable = Mage::getModel('qquoteadv/qqadvproductdownloadable');
        $html[] = 'Links:';

        $qqadvproductdownloadable->loadProduct($item);
        $links = $qqadvproductdownloadable->getLinks();
        if ($links) {
            foreach ($links as $link) {
                $html[] = $link->getTitle();
            }
        }

        return $html;
    }

    /**
     * Function that renders a bundle product
     *
     * @param $product
     * @param $item
     * @return array
     */
    protected function _renderBundle($product, $item)
    {
        $html = array();
        $product->setStoreId($item->getStoreId() ? $item->getStoreId() : 1);

        /** @var Mage_Bundle_Helper_Catalog_Product_Configuration $bundleConfigurationHelper */
        $bundleConfigurationHelper = Mage::helper('bundle/catalog_product_configuration');

        /** @var Mage_Catalog_Helper_Product_Configuration $productConfigurationHelper */
        $productConfigurationHelper = Mage::helper('catalog/product_configuration');

        $virtualQuote = Mage::helper('qquoteadv')->getQuoteItem($product, $item->getAttribute());
        foreach ($virtualQuote->getAllItems() as $_unit) {
            if ($_unit->getProductId() == $product->getId()) {
                $_options = $bundleConfigurationHelper->getOptions($_unit);
                if (is_array($_options)) {
                    foreach ($_options as $_option) {
                        $html[] = $_option['label'];

                        $params = array(
                            'max_length' => $this->maxAttributeLength,
                            'cut_replacer' => ' <a href="#" class="dots" onclick="return false">...</a>'
                        );
                        $formattedValue = $productConfigurationHelper->getFormattedOptionValue($_option, $params);

                        $simple = explode("\n", $formattedValue['value']);
                        foreach ($simple as $option) {
                            $option = str_replace("\r", "", $option);
                            $html[] = '  ' . $option;
                        }
                    }
                }
            }
        }

        return $html;
    }

    /**
     * For some reason some sites use the wrong extension type in the url like .jpg for a .png image...
     * support for gif, jpg/jpeg and png
     * @param $imagePath
     * @return bool|null
     */
    public function checkImageExtAndType($imagePath){
        if (function_exists('exif_imagetype')) {
            //get extention from pathinfo
            $ext = pathinfo($imagePath, PATHINFO_EXTENSION);

            //gif
            if(strcasecmp('gif', $ext) == 0) {
                if(exif_imagetype($imagePath) == IMAGETYPE_GIF){
                    return true;
                } else {
                    return false;
                }
            }

            //jpg
            if(strcasecmp('jpg', $ext) == 0) {
                if(exif_imagetype($imagePath) == IMAGETYPE_JPEG){
                    return true;
                } else {
                    return false;
                }
            }

            //jpeg
            if(strcasecmp('jpeg', $ext) == 0) {
                if(exif_imagetype($imagePath) == IMAGETYPE_JPEG){
                    return true;
                } else {
                    return false;
                }
            }

            //png
            if(strcasecmp('png', $ext) == 0) {
                if(exif_imagetype($imagePath) == IMAGETYPE_PNG){
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            //if no exif support, always return true
            return true;
        }

        return null;
    }

    /**
     * Draws a product on the give page
     *
     * @param $unit
     * @param $page Zend_Pdf_Page
     * @return \Zend_Pdf_Page
     * @throws \Mage_Core_Exception
     * @throws \Zend_Pdf_Exception
     */
    public function draw($unit, $page)
    {
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
        $item = Mage::getModel('catalog/product')->setStoreId($this->_quoteadv->getStoreId())->load($productId);
        $imageItem = $item;

        if ($item->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $attr = $this->_renderBundle($item, $unit);
        } elseif ($item->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $attr = $this->_renderConfigurable($item, $unit);
            // Load configured simple for correct product image
            $childProduct = $unit->getConfChildProduct($unit->getId(), $item);
            $imageItem = Mage::helper('qquoteadv/catalog_product_data')->getImageProduct(
                $item,
                $childProduct
            );
        } elseif($item->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE){
            $attr = $this->_renderDownloadable($item, $unit);
        }else{
            $superAttribute = $this->getOption($item, $unit->getAttribute());

            //render custom options
            $attr = $this->retrieveOptions($item, $superAttribute);
        }

        // Draw product image
        $prodImage = (string)Mage::getBaseDir('media') . '/catalog/product' . $imageItem->getData($this->defaultImg);
        if (is_file($prodImage)) {
            // get picture dimensions
            $image = Mage::helper('catalog/image')->init($imageItem, $this->defaultImg);
            if(!is_object($image) && !get_class($image) == 'Mage_Catalog_Helper_Image') {
                $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($image, $this->imgWidth, $this->imgHeight);
            } else {
                //file fallback
                $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($prodImage, $this->imgWidth, $this->imgHeight);
            }

            $x = $this->_leftRectPad;
            $y = $this->y - ($newDim['height'] - ($lineHeight - 3)); // aligning top of the image with text

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
                        $newDim = Mage::helper('qquoteadv/catalog_product_data')->getItemPictureDimensions($pdfimage, $this->imgWidth, $this->imgHeight);
                        $x = $this->_leftRectPad;
                        $y = $this->y - ($newDim['height'] - ($lineHeight - 3)); // aligning top of the image with text
                        $page->drawImage($pdfimage, $x, $y, $x + $newDim['width'], $y + $newDim['height']);

                    }
                }
            }
        }

        /* in case Product name is longer than 45 chars / $maxChar /$this->maxProductNameChars - it is written in a few lines */
        $name = $item->getName();
        $line[] = array(
            'text' => Mage::helper('core/string')->str_split(strip_tags($name), $maxChar, true, true),
            'feed' => $this->leftMarginProductName,
            'font' => 'bold',
            'font_size' => $this->fontSizeBold,
            'height' => $lineHeight
        );

        // draw SKUs
        $sku = $this->getSku($item);
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

        $_quote = $this->getQuote($unit->getQuoteId());
        $currency = $_quote->getData('currency');
        $currentCurrencyCode = $this->quoteStore->getCurrentCurrencyCode();
        $this->quoteStore->setCurrentCurrencyCode($currency);

        //#tier price section
        $k = 0;
        $showCurrentTier = false; // set this to true to show current tier in sub tier list
        $txt1 = $txt2 = $txt3 = array();
        // Setting first price
        if ($showPrice && isset($tierPrices)) {
            //$this->quoteStore->formatPrice(
            $price = $this->quoteStore->formatPrice($tierPrices[$unit->getQty()], false);
            $row = $unit->getQty() * $tierPrices[$unit->getQty()];
            $rowTotal = $this->quoteStore->formatPrice($row, false);
        } else {
            $price = $this->itemPriceReplace;
            $rowTotal = $this->rowTotalReplace;
        }

        $size = $this->fontSizeSmall;
        $productQty = $unit->getQty() * 1;
        $txt1[] = array('text' => $productQty, 'font' => 'regular', 'font_size' => $size);
        $txt2[] = $price; //480
        $txt3[] = $rowTotal; //542

        if (count($requestedProductData) > 1):
            foreach ($requestedProductData as $product) {
                if ($k > 0) {
                    $this->isSetTierPrice = 1;
                }
                //set first line
                $productQty = $product->getRequestQty() * 1;
                $priceProposal = $product->getOwnerCurPrice();

                $showTier = true;
                $price = $this->quoteStore->formatPrice($priceProposal, false);
                $row = $productQty * $priceProposal;
                $rowTotal = $this->quoteStore->formatPrice($row, false);
                if ($productQty == $unit->getQty()) {
                    $rowTotal .= "*";
                    if ($showCurrentTier === false) {
                        $showTier = false;
                    }
                }

                // add row total
                $this->totalPrice += $row;

                if ($showTier === true) {
                    if (!$showPrice) {
                        $price = $this->itemPriceReplace;
                        $rowTotal = $this->rowTotalReplace;
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

        $this->quoteStore->setCurrentCurrencyCode($currentCurrencyCode);

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
        if (!empty($desc)) {
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
                $value = str_replace("\n", "", $value);
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
        $page = $this->drawLineBlocks($page, $drawItems, array('table_header' => true));

        return $page;
    }

    /**
     * Function that inserts the title/header on a page
     *
     * @param $page
     * @param $source
     * @param $storeId
     */
    protected function insertTitles(&$page, $source, $storeId)
    {
        $quoteadvId = $source;
        if (is_null($this->_quoteadv)) {
            $this->_quoteadv = $this->getQuote($quoteadvId);
        }

        $billingAddress = $this->_formatAddress($this->_quoteadv->getBillingAddressFormatted('pdf'));
        $shippingAddress = $this->_formatAddress($this->_quoteadv->getShippingAddressFormatted('pdf'));

        $this->y -= 5; //add margin
        $y = $this->y;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
        $page->setLineWidth(0.5);

        //height of address box is determined by the last value
        $countBillingAddress = count($billingAddress);
        $countShippingAddress = count($shippingAddress);

        if($countShippingAddress > $countBillingAddress){
            $countBillingAddress = $countShippingAddress;
        }
        if($countBillingAddress < 3){
            $countBillingAddress = 3;
        }

        $addressHeight = ($countBillingAddress * $this->fontLineHeightDefault) + 10;
        $page->drawRectangle($this->_leftRectPad, $y, $this->maxContentWidth, $this->y -= $addressHeight);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, $this->fontSizeDefault);
        $topPosY = $y - $this->fontLineHeightDefault;
        $page->drawText(Mage::helper('qquoteadv')->__('Bill to:'), $this->_leftTextPad, $topPosY, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Ship to:'), $this->_midTextPad, $topPosY, 'UTF-8');

//        $shippingCompany = $this->_quoteadv->getShippingCompany();
//        $value = trim($shippingCompany);
//        if(!empty($value)) {
//            $y-=10;
//            $this->_setFontBold($page);
//            $page->drawText($shippingCompany, $this->_leftTextPad + 20, $y, 'UTF-8');
//        }
        $y -= $this->fontLineHeightDefault;

        /*$shipTo = $this->reformatAddress($this->_quoteadv->getAddressFormatted(Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING));

        $this->_setFontRegular($page, $this->fontSizeDefault);
        foreach ($shipTo as $value) {
            if ($value !== '') {
                $page->drawText($value, $this->_leftTextPad + 20, $y, 'UTF-8');
                $y -= 10;
            }
        }*/
        $y -= 10;
        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = array();
                $splitAddress = Mage::helper('core/string')->str_split($value, $this->maxAddressLength, true, true);
                foreach ($splitAddress as $_value) {
                    $text[] = $_value;
                }

                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), $this->_leftTextPad, $y, 'UTF-8');
                    $y -= 10;
                }
            }
        }
        $y = $topPosY - 10;

        foreach ($shippingAddress as $value){
            if ($value!=='') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, $this->maxAddressLength, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)),  $this->_midTextPad, $y, 'UTF-8');
                    $y -= 10;
                }
            }
        }

        $x = $this->_leftRectPad + $this->floatRightMargin; //=380
        $xPad = $x + $this->distanceFromQuoteDataLable;
        $y = $topPosY;

        $page->drawText(Mage::helper('qquoteadv')->__('Quote Proposal'), $x, $topPosY, 'UTF-8');

        $realQuoteadvId = $this->_quoteadv->getIncrementId() ? $this->_quoteadv->getIncrementId() : $this->_quoteadv->getId();
        $page->drawText($realQuoteadvId, $xPad, $y, 'UTF-8');

        $y -= 10;
//        $proposalDate = $this->_quoteadv->getProposalSent();  //Mage::helper('core')->formatDate( date( 'D M j Y')

        $proposalDate = $this->_quoteadv->getProposalDate();
        $page->drawText(Mage::helper('qquoteadv')->__('Date of Proposal'), $x, $y, 'UTF-8');
        $page->drawText(Mage::helper('core')->formatDate($proposalDate, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, false), $xPad, $y, 'UTF-8');

        $expiryDate = $this->_quoteadv->getExpiry();
        if ($expiryDate) {
            $expDays = (int)date_diff(
                date_create($proposalDate),
                date_create($expiryDate)
            )->format('%a');

            $validDate = date(
                'D M j Y',
                Mage::getModel('core/date')
                    ->timestamp(strtotime($expiryDate)) - Mage::getModel('core/date')
                    ->getGmtOffset()
            );
        } else {
            $expDays = (int)Mage::getStoreConfig(
                'qquoteadv_quote_configuration/expiration_times_and_notices/expirtime_proposal',
                $this->_quoteadv->getStoreId()
            );

            $validDate = date('D M j Y', strtotime("+$expDays days", strtotime($proposalDate)));
        }

        if ($expDays && $validDate) {
            $y -= 10;
            $page->drawText(Mage::helper('qquoteadv')->__('Proposal valid until'), $x, $y, 'UTF-8');
            $validDate = date('D M j Y', strtotime("+$expDays days", strtotime($proposalDate)));
            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

            if($expDays > 0){
                $note = "( " . Mage::helper('qquoteadv')->__("%s days", $expDays) . " )";
                $value = Mage::app()->getLocale()->date(strtotime($validDate), null, null, false)->toString($format) . '  ' . $note;
            } else {
                $value = Mage::app()->getLocale()->date(strtotime($validDate), null, null, false)->toString($format);
            }

            $page->drawText($value, $xPad, $y, 'UTF-8');
        }

        $this->_setFontRegular($page, $this->fontSizeDefault);
    }

    /**
     * Print the available  shipping methods
     */
    public function insertShippingMethods()
    {
        // Get Shipping Methods
        $shippingRates = Mage::getModel('qquoteadv/quoteshippingrate')->getShippingRatesList($this->_quoteadv);
        $shippingRateList = $shippingRates['shippingList'];

        //remove quote rate
        foreach ($shippingRateList as $key => $rates){
            foreach ($rates as $rate){
                if($rate['code'] == "qquoteshiprate_qquoteshiprate"){
                    unset($shippingRateList[$key]);
                }
            }
        }

        //count the rates
        $rateCounter = 0;
        foreach ($shippingRateList as $key => $rates){
            $rateCounter += count($rates);
        }
        $rateCounter += count($shippingRateList);
        $itemCount = $rateCounter;

        // Declare position variables
        $lineHeight = $this->fontLineHeightDefault;
        $maxWidth = $this->maxContentWidth;
        $boxHeader = 15;
        $boxMargin = array('top' => 10, 'right' => 0, 'bottom' => 10, 'left' => 0);
        $boxPadding = array('top' => 10, 'right' => 5, 'bottom' => 10, 'left' => 0);
        $boxBody['height'] = ($itemCount * $lineHeight) + $boxPadding['top'] + $boxPadding['bottom'];
        $boxBody['width'] = $maxWidth - $boxMargin['left'] - $boxMargin['right'];

        $fontHead = $this->fontSizeDefault;
        $fontIndent = 20;

        // Setting Margin
        $this->y -= $boxMargin['top'];
        $y = $this->y;

        // Get current page
        $page = $this->getCurrentPage();

        // Setting new y position
        $this->y -= ($boxHeader + $boxBody['height']);

        // Box Border
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        // Draw Box Header
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle($this->_leftRectPad + $boxMargin['left'], $y, $boxBody['width'], $y - $boxHeader);

        // Draw Box Body
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle($this->_leftRectPad + $boxMargin['left'], $y - $boxHeader, $boxBody['width'], $y - $boxBody['height']);

        // Insert Header Text
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, $this->fontSizeDefault);
        $topPosY = $y - 10;

        if ($shippingRates && count($shippingRateList) > 0) {
            $page->drawText(Mage::helper('qquoteadv')->__('Available Shipping Methods'), $this->_leftTextPad, $topPosY, 'UTF-8');

            // Draw Shipping Rates
            $topPosY = $y - ($boxHeader + $boxPadding['top']);
            foreach ($shippingRateList as $k => $v) {
                $posLeft = $this->_leftTextPad + $boxMargin['left'] + $boxPadding['left'];
                // Draw Carrier Title
                $this->_setFontBold($page, $fontHead);
                $page->drawText($k, $posLeft, $topPosY, 'UTF-8');
                $topPosY = $topPosY - $lineHeight;
                foreach ($v as $rate) {
                    $this->_setFontRegular($page, $this->fontSizeDefault);
                    if ($rate['method_list'] == '') {
                        $ratePieces = explode("_", $rate['code']);

                        if (isset($ratePieces[1])) {
                            $title = $ratePieces[1];
                        } else {
                            if (!$title = Mage::getStoreConfig("carriers/" . $ratePieces[0] . "/title")) {
                                $title = $rate['code'];
                            }
                        }
                        $rate['method_list'] = $title;
                    }
                    $line = ucwords($rate['method_list']) . ' - ' . $this->getCurrencyShipping($rate['price']);
                    $page->drawText($line, $posLeft + $fontIndent, $topPosY, 'UTF-8');
                    $topPosY = $topPosY - $lineHeight;
                }
            }
        } else {
            $page->drawText(Mage::helper('qquoteadv')->__('No Available Shipping Methods'), $this->_leftTextPad, $topPosY, 'UTF-8');
        }
    }

    /**
     * Print the selected shipping method
     */
    public function insertCurrentShippingMethod()
    {
        // Get Shipping Methods
        $shippingRates = Mage::getModel('qquoteadv/quoteshippingrate')->getShippingRatesList($this->_quoteadv);
        $shippingRateList = $shippingRates['shippingList'];

        //remove quote rate
        foreach ($shippingRateList as $key => $rates){
            foreach ($rates as $rateKey => $rate){
                //if($rate['code'] == "qquoteshiprate_qquoteshiprate"){
                //    unset($shippingRateList[$key]);
                //}

                //remove non selected rate
                $code = $this->_quoteadv->getShippingCode();
                if($rate['code'] != $code){
                    unset($shippingRateList[$key][$rateKey]);
                }

                //remove non selecte rate group
                if(count($shippingRateList[$key]) == 0){
                    unset($shippingRateList[$key]);
                }
            }
        }

        $itemCount = 2;

        // Declare position variables
        $lineHeight = $this->fontLineHeightDefault;
        $maxWidth = $this->maxContentWidth;
        $boxHeader = 15;
        $boxMargin = array('top' => 10, 'right' => 0, 'bottom' => 10, 'left' => 0);
        $boxPadding = array('top' => 10, 'right' => 5, 'bottom' => 10, 'left' => 0);
        $boxBody['height'] = ($itemCount * $lineHeight) + $boxPadding['top'] + $boxPadding['bottom'];
        $boxBody['width'] = $maxWidth - $boxMargin['left'] - $boxMargin['right'];

        $fontHead = $this->fontSizeDefault;
        $fontIndent = 20;

        // Setting Margin
        $this->y -= $boxMargin['top'];
        $y = $this->y;

        // Get current page
        $page = $this->getCurrentPage();

        // Setting new y position
        $this->y -= ($boxHeader + $boxBody['height']);

        // Box Border
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);

        // Draw Box Header
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->drawRectangle($this->_leftRectPad + $boxMargin['left'], $y, $boxBody['width'], $y - $boxHeader);

        // Draw Box Body
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle($this->_leftRectPad + $boxMargin['left'], $y - $boxHeader, $boxBody['width'], $y - $boxBody['height']);

        // Insert Header Text
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, $this->fontSizeDefault);
        $topPosY = $y - 10;

        if ($shippingRates && count($shippingRateList) > 0) {
            $page->drawText(Mage::helper('qquoteadv')->__('Shipping Method'), $this->_leftTextPad, $topPosY, 'UTF-8');

            // Draw Shipping Rates
            $topPosY = $y - ($boxHeader + $boxPadding['top']);
            foreach ($shippingRateList as $k => $v) {
                $posLeft = $this->_leftTextPad + $boxMargin['left'] + $boxPadding['left'];
                // Draw Carrier Title
                $this->_setFontBold($page, $fontHead);
                $page->drawText($k, $posLeft, $topPosY, 'UTF-8');
                $topPosY = $topPosY - $lineHeight;
                foreach ($v as $rate) {
                    $this->_setFontRegular($page, $this->fontSizeDefault);
                    if ($rate['method_list'] == '') {
                        $ratePieces = explode("_", $rate['code']);

                        if (isset($ratePieces[1])) {
                            $title = $ratePieces[1];
                        } else {
                            if (!$title = Mage::getStoreConfig("carriers/" . $ratePieces[0] . "/title")) {
                                $title = $rate['code'];
                            }
                        }
                        $rate['method_list'] = $title;
                    }
                    $line = ucwords($rate['method_list']) . ' - ' . $this->getCurrencyShipping($rate['price']);
                    $page->drawText($line, $posLeft + $fontIndent, $topPosY, 'UTF-8');
                    $topPosY = $topPosY - $lineHeight;
                }
            }
        } else {
            $page->drawText(Mage::helper('qquoteadv')->__('No Available Shipping Method'), $this->_leftTextPad, $topPosY, 'UTF-8');
        }
    }

    /**
     * Function that draws a label on a given position
     *
     * @param $label
     * @param $position
     * @return $this
     */
    protected function _drawLabel($label, $position)
    {
        $page = $this->getCurrentPage();
        $this->_setFontBold($page, $this->fontSizeDefault);
        $page->drawText($label, $position, $this->y, 'UTF-8');
        $this->_setFontRegular($page, $this->fontSizeDefault);
        return $this;
    }

    /**
     * Function that draws text on a given position
     *
     * @param $text
     * @param $position
     * @return $this
     */
    protected function _drawText($text, $position)
    {
        $page = $this->getCurrentPage();
        $page->drawText($text, $position, $this->y, 'UTF-8');
        return $this;
    }

    /**
     * Function that draws the total price
     *
     * @param $label
     * @param $price
     * @param int $storeId
     */
    protected function drawTotal($label, $price, $storeId = 0)
    {
        $this->totalCounter++;
        $text = Mage::app()->getStore($storeId)->formatPrice($price, false);
        //$this->_drawLabel($label, $this->_leftRectPad + $this->floatRightMargin, $this->y, 'UTF-8');
        $this->_drawLabel($label, $this->_leftRectPad + $this->floatRightMargin);
        $this->_drawText(strip_tags($text), $this->leftMarginSubtotal);
        $this->y -= $this->fontSizeBold;
    }

    /**
     * Total information by quoteadv
     *
     * @param  $page
     */
    protected function insertTotal(&$page)
    {
        $this->y -= 20;
        $currentY = $this->y;
        $totalsArray = $this->_quoteadv->getTotalsArray();

        /* $excl = Mage::helper('tax')->__('(excl. TAX)');

         //if(!$this->isSetTierPrice){
             $this->_setFontBold($page, $this->fontSizeBold);
             $sLabel     = Mage::helper('qquoteadv')->__('Total price')." ".$excl;
             $totalPrice = $this->quoteStore->formatPrice($this->totalPrice, false);

             $page->drawText($sLabel, 430, $this->y, 'UTF-8');
             $page->drawText(strip_tags($totalPrice), 530, $this->y, 'UTF-8');

             $this->y -= 10;        */
        //}

        $store = $this->_quoteadv->getStore();

        $taxConfig = Mage::getSingleton('tax/config');


        // Adding Quote Adjustment Total
        // To default totals
        if (Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/adjustment', $store) == 1) {

            if (Mage::helper('tax')->priceIncludesTax($store->getStoreId())) {
                $label = Mage::helper('qquoteadv')->__('Adjustment Quote (Incl. default Tax)');
            } else {
                $label = Mage::helper('qquoteadv')->__('Adjustment Quote');
            }

            $reduction = $this->_quoteadv->getQuoteReduction();
            if ($reduction != false) {
                $price = -1 * $reduction;
                $this->drawTotal($label, $price, $store->getStoreId());
            }
        }

        if ($taxConfig->displaySalesSubtotalExclTax($store)) {
            $label = Mage::helper('tax')->__('Subtotal (Excl. Tax)');
            $price = $this->_quoteadv->getSubtotal();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        if ($taxConfig->displaySalesSubtotalInclTax($store)) {
            $label = Mage::helper('tax')->__('Subtotal (Incl. Tax)');
            $price = $this->_quoteadv->getSubtotalInclTax();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        if ($taxConfig->displaySalesSubtotalBoth($store)) {
            $label = Mage::helper('tax')->__('Subtotal (Excl. Tax)');
            $price = $this->_quoteadv->getSubtotal();
            $this->drawTotal($label, $price, $store->getStoreId());

            $label = Mage::helper('tax')->__('Subtotal (Incl. Tax)');
            $price = $this->_quoteadv->getSubtotalInclTax();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        /*        if($this->_quoteadv->getShippingType() ==""):
                    $label= Mage::helper('tax')->__('Shipping & Handling');
                    $text = Mage::helper('qquoteadv')->__('Select in Checkout');
                    $this->_drawLabel($label, $this->_leftRectPad + $this->floatRightMargin, $this->y, 'UTF-8');
                    $this->_drawText(strip_tags($text), $this->leftMarginSubtotal, $this->y, 'UTF-8');
                    $this->y -= 12;

                else:
        */

        if ($taxConfig->displaySalesShippingInclTax($store)) {
            $label = Mage::helper('tax')->__('Shipping & Handling (Incl. Tax)');
            $price = $this->_quoteadv->getShippingInclTax();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        if ($taxConfig->displaySalesShippingExclTax($store)) {
            $label = Mage::helper('tax')->__('Shipping & Handling (Excl. Tax)');
            $price = $this->_quoteadv->getShippingAmount();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        if ($taxConfig->displaySalesShippingBoth($store)) {
            $label = Mage::helper('tax')->__('Shipping & Handling (Excl. Tax)');
            $price = $this->_quoteadv->getShippingAmount();
            $this->drawTotal($label, $price, $store->getStoreId());
            $label = Mage::helper('tax')->__('Shipping & Handling (Incl. Tax)');
            $price = $this->_quoteadv->getShippingAmountInclTax();
            $this->drawTotal($label, $price, $store->getStoreId());
        }

//        endif;

        // Fooman Surcharge
        if (isset($totalsArray['surcharge'])) {
            $surcharge = $totalsArray['surcharge'];
            $label = $surcharge['title'];
            $price = $surcharge['value'];
            $this->drawTotal($label, $price);
        }

        if (isset($totalsArray['discount'])) {
            $discount = $totalsArray['discount'];
            $label = $discount['title'];
            $price = $discount['value'];
            $this->drawTotal($label, $price, $store->getStoreId());
        }

        if ($taxConfig->displaySalesTaxWithGrandTotal($store)) {
            $label = Mage::helper('tax')->__('Grand Total (Excl. Tax)');
            $price = $this->_quoteadv->getGrandTotalExclTax();
            $this->drawTotal($label, $price, $store->getStoreId());

            $label = Mage::helper('tax')->__('Tax');
            $price = $this->_quoteadv->getTaxAmount();
            $this->drawTotal($label, $price, $store->getStoreId());

            $label = Mage::helper('tax')->__('Grand Total (Incl. Tax)');
            $price = $this->_quoteadv->getGrandTotal();
            $this->drawTotal($label, $price, $store->getStoreId());

        } else {
            $label = Mage::helper('tax')->__('Tax');
            $price = $this->_quoteadv->getTaxAmount();
            $this->drawTotal($label, $price, $store->getStoreId());

            $label = Mage::helper('tax')->__('Grand Total');
            $price = $this->_quoteadv->getGrandTotal();
            $this->drawTotal($label, $price, $store->getStoreId());
        }


        $this->y = $currentY;

        $remark = $this->_quoteadv->getClientRequest();
        if ($remark) {
            $remark = strip_tags($remark);
            $remark = wordwrap($remark, $this->maxGlobalClientRequestChars, "\n", true);
            $data = explode("\n", $remark);
        }

        //$min_height = 55;
        $min_height = ($this->totalCounter * $this->fontSizeBold) - (8 + 8);
        if (isset($data)) {
            $boxheight = count($data) * $this->fontSizeDefault;
        } else {
            $boxheight = 0;
        }

        if ($boxheight > $min_height) {
            $lowPoint = $this->y - ($boxheight + 8);
        } else {
            $lowPoint = $this->y - ($min_height + 8);
        }

//        $lowPoint = $this->y-55;

        if (isset($data)) {

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
            $page->setLineWidth(0.5);
//            $lowPoint = $this->y-55;
            $page->drawRectangle($this->_leftRectPad, $this->y + 5, $this->_leftRectPad + ($this->floatRightMargin - 10), $lowPoint);

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($page, $this->fontSizeDefault);

            //$this->y -= 10;

            $tmpY = $this->y - 8;
            $this->y = $lowPoint;

            foreach ($data as $value) {
                $value = trim($value, "\t\0\x0B"); //don't trim new lines
                if ($value !== '') {
                    $value = str_replace("\r", "", $value);
                    $value = str_replace("\n", "", $value);
                    $page->drawText($value, $this->_leftTextPad, $tmpY, 'UTF-8');
                    $tmpY -= $this->fontSizeDefault;
                }
            }

//            if( $this->y > $tmpY )
//                    $this->y = $tmpY;
        } else {

            $this->y = $lowPoint;

        }
    }

    /**
     * Quote general remark
     *
     * @param  $page
     */
    protected function insertGeneralRemark(&$page)
    {
        $qquoteadvRemark = Mage::getStoreConfig('qquoteadv_quote_configuration/proposal/qquoteadv_remark', $this->_quoteadv->getStoreId());
        if ($qquoteadvRemark) {
            $qquoteadvRemark = strip_tags($qquoteadvRemark);
            $qquoteadvRemark = wordwrap($qquoteadvRemark, $this->remarkLength, "\n", true);
            $data = explode("\n", $qquoteadvRemark);

            $this->y -= 15;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->setLineWidth(0.5);
            $textHeight = count($data) * $this->fontSizeDefault;
            $page->drawRectangle($this->_leftRectPad, $this->y, $this->maxContentWidth, ($this->y - $textHeight) - 8);

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($page, $this->fontSizeDefault);

            $this->y -= 10;

            foreach ($data as $value) {
                $value = trim($value, "\t\0\x0B"); //don't trim new lines
                if ($value !== '') {
                    $value = str_replace("\r", "", $value);
                    $value = str_replace("\n", "", $value);
                    $page->drawText($value, $this->_leftTextPad, $this->y, 'UTF-8');
                    $this->y -= $this->fontSizeDefault;
                }
            }
        }
    }

    /**
     * Get SKU with support for configurables and bundles
     *
     * @param $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku')) {
            return $item->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
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
            $superAttribute = Mage::helper('qquoteadv')->getSimpleOptions($product, unserialize($attribute));
        }
        return $superAttribute;
    }

    /**
     * Function that returns the options for a simple or virtual product
     *
     * @param $product
     * @param $superAttribute
     * @return array
     */
    protected function retrieveOptions($product, $superAttribute)
    {
        $attr = array();

        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
            if ($superAttribute) {
                foreach ($superAttribute as $option => $value) {
                    if (!empty($value)) {
                        $attr[] = $option;
                        $value = explode(PHP_EOL, $value);
                        foreach($value as $multipleValue){
                            $attr[] = '   ' . $multipleValue;
                        }
                    }
                }
            }
        }

        return $attr;
    }

    /**
     * Draw lines
     *
     * draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parametr), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param Zend_Pdf_Page $page
     * @param array $draw
     * @param array $pageSettings
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : $this->fontLineHeightDefault;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->addNewPage(); //$this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? $this->fontSizeDefault : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        $this->setFontStyle($page, $fontSize, $fontStyle);
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        $this->setFontStyle($page, $fontSize, $fontStyle);
                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        if (is_array($part)) {
                            $part_array = $part;
                            $part = $part_array['text'];
                            $this->setFontStyle($page, $part_array['font_size'], $part_array['font']);
                        }

                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }

                $this->y -= $maxHeight;
            }

            //add extra spacing for image
            if(isset($column['min-height'])){
                if($maxHeight < $column['min-height']){
                    $this->y -= ($column['min-height'] - $maxHeight);
                }
            }
        }

        return $page;
    }

    /**
     * Set Font Style for text
     *
     * @param object $page Zend_Pdf_Page
     * @param integer $fontSize
     * @param string $fontStyle
     */
    public function setFontStyle($page, $fontSize, $fontStyle)
    {
        switch ($fontStyle) {
            case 'bold':
                $this->_setFontBold($page, $fontSize);
                break;
            case 'italic':
                $this->_setFontItalic($page, $fontSize);
                break;
            default:
                $this->_setFontRegular($page, $fontSize);
                break;
        }
    }

    /**
     * Reformat Address for PDF
     *
     * @param array $shipTo
     * @return array
     */
    public function reformatAddress($shipTo)
    {
        // Combine Country and Region
        if (!empty($shipTo['region'])) {
            $shipTo['region'] = $shipTo['region'] . ', ' . $shipTo['country'];
            unset($shipTo['country']);
        } else {
            unset($shipTo['region']);
        }
        // Combine name and Company
        if (!empty($shipTo['company'])) {
            $shipTo['name'] = $shipTo['name'] . ', ' . $shipTo['company'];
            unset($shipTo['company']);
        }
        // Remove telephone info
        unset($shipTo['telephone']);

        return $shipTo;
    }

    /**
     * Function that gets the quotation quote for a given id
     *
     * @param $quoteId
     * @return Ophirah_Qquoteadv_Model_Qqadvcustomer
     */
    public function getQuote($quoteId){
        if(!$this->_quoteadv){
            $this->_quoteadvId = $quoteId;
            $this->_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($this->_quoteadvId);
            $this->quoteStore = Mage::app()->getStore($this->_quoteadv->getStoreId());
            return $this->_quoteadv;
        }

        if($this->_quoteadv->getId() != $quoteId){
            $this->_quoteadvId = $quoteId;
            $this->_quoteadv = Mage::getModel('qquoteadv/qqadvcustomer')->load($this->_quoteadvId);
            $this->quoteStore = Mage::app()->getStore($this->_quoteadv->getStoreId());
        }

        return $this->_quoteadv;
    }

    /**
     * Convert the shipping price to the quote currency and formats the value
     *
     * @param $basePrice
     * @return mixed
     */
    public function getCurrencyShipping($basePrice){
        return strip_tags($this->_quoteadv->formatPrice($this->convertCurrency($basePrice)));
    }

    /**
     * Converts the price by the currency set on the quote.
     *
     * @param $basePrice
     * @return mixed
     */
    public function convertCurrency($basePrice){
        $baseCurrencyCode = Mage::app()->getStore($this->_quoteadv->getStoreId())->getBaseCurrencyCode();
        return Mage::helper('directory')->currencyConvert(
            $basePrice,
            $baseCurrencyCode,
            $this->_quoteadv->getCurrency()
        );
    }

    /**
     * Function that prints the QUOTATION mark
     *
     * @param $page
     * @param $isLogoPrinted
     * @param $x
     * @param $y
     * @return int
     */
    protected function insertQuotationMark(&$page, $isLogoPrinted = true, $x, $y)
    {
        //print QUOTATION on the left side if there is nog logo printed
        if (!$isLogoPrinted) {
            $x = $this->_leftRectPad;
        }

        //set the font for the QUOTATION PART
        $this->_setFontBold($page, $this->fontSizeBold);

        //add QUOTATION
        $page->drawText(trim(strip_tags("QUOTATION")), $x, $y, 'UTF-8');
        $y -= $this->fontSizeBold;

        //add some space below
        $page->drawText(trim(strip_tags("\n")), $x, $y, 'UTF-8');
        $y -= $this->fontSizeDefault;

        //re-set the the normal font
        $this->_setFontRegular($page, $this->fontSizeDefault);

        //return the changed $y space
        return $y;
    }
}
