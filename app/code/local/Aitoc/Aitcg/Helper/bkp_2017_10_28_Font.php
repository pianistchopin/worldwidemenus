<?php

class Aitoc_Aitcg_Helper_Font extends Aitoc_Aitcg_Helper_Abstract
{
    const MARGIN_IMAGE_PERCENT      = 10;
    const MARGIN_RESOLUTION_PERCENT = 10;
    protected static $_size=array(
        /*'A4'=>array('32.43'=>'48','24.32'=>'36'),
        'A5'=>array('45.7'=>'48','34.29'=>'36'),
        'A3'=>array('34.43'=>'48','24.32'=>'36'),
        '2/3 A4'=>array('34.43'=>'48','24.32'=>'36'),
        '1/2 A4'=>array('34.43'=>'48','24.32'=>'36'),
        'DEFAULT'=>array('22'=>'22','24.32'=>'36','50'=>'50',)*/
        'A4'=>array('48'=>'48','36'=>'36'),
        'BILL_PRESENTERS'=>array('48'=>'21.75','36'=>'16.5'),
        'A5'=>array('48'=>'48','36'=>'36'),
        'A3'=>array('48'=>'48','36'=>'36'),
        '2/3 A4'=>array('48'=>'48','36'=>'36'),
        '1/2 A4'=>array('48'=>'48','36'=>'36'),
        'COASTERS'=>array('48'=>'48','36'=>'36'),
        'PLACEMATS'=>array('48'=>'48','36'=>'36'),
        'DEFAULT'=>array('36'=>'36','48'=>'48')
    );

    public function getFontOptionHtml()
    {
        $collection = Mage::getModel('aitcg/font')
            ->getCollection()
            ->addFieldToFilter('filename', array('neq' => ''))
            ->addFieldToFilter('status', '1');

        $optionsHtml = '<option value="">' . Mage::helper('aitcg')->__('Select font...') . '</option>';
        foreach ($collection->load() as $font) {
            $optionsHtml .= '<option value="' . $font->getFontId() . '" data-family-id="' . $font->getFontFamilyId()
                . '" data-font-file="' . $font->getFilename() . '">' . $font->getName() . '</option>';
        }

        return $optionsHtml;
    }
    public function getFontFamilyOptionHtml()
    {
        $collection = Mage::getModel('aitcg/font_family')
            ->getCollection();

        $optionsHtml = '<option value="">' . Mage::helper('aitcg')->__('Select font family...') . '</option>';

        foreach ($collection->load() as $fontFamily) {
            $fontCollection = Mage::getModel('aitcg/font')->getCollection();
            foreach ($fontCollection as $item) {
                if ($item->getFontFamilyId() === $fontFamily->getFontFamilyId()) {
                    $optionsHtml .= '<option value="' . $fontFamily->getFontFamilyId() . '">' . $fontFamily->getTitle()
                        . '</option>';
                    break;
                }
            }

        }

        return $optionsHtml;
    }
    /**
     * @return  array of font size depends on size attribute of product
     */

    public function getFontSizeOptionHtml($isCheckCat=array()){
        $product=Mage::registry('current_product');
        if ($product && $product->getId()) {
          $size =$product->getResource()->getAttribute('size')->setStoreId(0)->getFrontend()->getValue($product);
        }

        if(!empty($size)){
            $size=strtoupper($size);
            if(isset(self::$_size[$size])){
                $fontSize=self::$_size[$size];
            }
        }
        if(isset($isCheckCat['bill_presenters']) && $isCheckCat['bill_presenters'])
        {
            if(isset(self::$_size['BILL_PRESENTERS'])){
                $fontSize=self::$_size['BILL_PRESENTERS'];
            }
        }
        $fontSize=!empty($fontSize)?$fontSize:self::$_size['DEFAULT'];
        $optionsHtml = '<option value="">' . Mage::helper('aitcg')->__('Select font size...') . '</option>';
        foreach ($fontSize as $key=>$value) {
                    $optionsHtml .= '<option value="' . $key . '">' . $value . '</option>';
        }
        return $optionsHtml;
    }

    public function getFontPreview($font)
    {
        $im    = imagecreatetruecolor(550, 60);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 549, 60, $white);

        $text = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';

        imagettftext($im, 35, 0, 10, 40, $black, $font, $text);
        ob_start();
        imagepng($im);
        $image = ob_get_contents();
        ob_clean();
        imagedestroy($im);

        return 'data:image/png;base64,' . base64_encode($image);
    }

    public function getTextImage($font, $text, $color, $outline, $shadow, $aling, $size)
    {
        $resolution = !empty($size)?$size:$this->getResolution();
        $maxMargin  = (int)($resolution * self::MARGIN_RESOLUTION_PERCENT / 100);
        $coords     = imagettfbbox($resolution, 0, $font, $text);

        $baseWidth = $coords[2] - $coords[0];
        $margin    = (int)($baseWidth * self::MARGIN_IMAGE_PERCENT / 100);
        $margin    = ($margin <= $maxMargin) ? $margin : $maxMargin;

        // remove left negative margin
        if ($coords[0] < 0) {
            $coords[2] += abs($coords[0]);
            $coords[0] = 0;
        }
        // add horizontal margin
        $coords[0] += $margin;
        $coords[2] += $margin * 3;

        $wightoutline = empty($outline['wight']) ? 0 : $outline['wight'];
        $shadowX      = empty($shadow['x']) ? 0 : $shadow['x'];
        $shadowY      = empty($shadow['y']) ? 0 : $shadow['y'];

        $width  = $coords[2] - $coords[0] + 11 + $wightoutline * 2 + abs($shadowX);
        $height = $coords[1] - $coords[5] + $resolution * 0.75 + $wightoutline * 2 + abs($shadowY);
        $im     = $this->_getEmptyImage($width, $height);
        $color  = $this->_getColorOnImage($im, $color);

        $shadowX = ($shadow['x'] > 0) ? 0 : -$shadow['x'];
        $shadowY = ($shadow['y'] > 0) ? 0 : -$shadow['y'];

        $textArray = explode("\n", $text);
        $textPrint = $textArray[0];
        $leftX     = $coords[0] + $wightoutline + $shadowX;

        foreach ($textArray as $line) {
            list(, $yFirstLetter) = imagettfbbox($resolution, 0, $font, $textPrint);
            list($lX, , $rX) = imagettfbbox($resolution, 0, $font, $line);
            $lineWidth = $rX - $lX;
            switch ($aling) {
                case 'left':
                    $X = $leftX + 5;
                    break;
                case 'center':
                    $centerX = $leftX + ($baseWidth) / 2;
                    $X       = $centerX - $lineWidth / 2;
                    break;
                case 'right':
                    $rightX = $leftX + $baseWidth - 5;
                    $X      = $rightX - $lineWidth;
                    break;
            }

            $Y = $yFirstLetter - $coords[5] + $wightoutline + $shadowY;
            if (!empty($shadow)) {
                $shadowcolor = $this->_getColorOnImage($im, $shadow['color'], $shadow['alpha']);
                imagettftext($im, $resolution, 0, $X + $shadow['x'], $Y + $shadow['y'], $shadowcolor, $font, $text);
            }
            if (!empty($outline)) {
                $outlinecolor = $this->_getColorOnImage($im, $outline['color']);
                $this->_imagettftextoutline($im, $resolution, 0, $X, $Y, $outlinecolor, $font, $text, $wightoutline);
            }
            imagettftext($im, $resolution, 0, $X, $Y, $color, $font, $line);

            $textPrint .= "\n" . $line;
        }
        $path     = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
        $filename = $this->_getUniqueFilename($path);
        ob_start();
        imagepng($im);
        $image = ob_get_contents();
        ob_clean();
        imagedestroy($im);
        file_put_contents($path . $filename, $image);

        return $filename;
    }

    public function getCurveTextImage($font, $text, $color, $radius, $downText, $align)
    {
        $resolution = !empty($size)?$size:$this->getResolution();
        $resolution = 500;

        $coords     = imagettfbbox($resolution, 0, $font, $text);

        $baseWidth = $coords[2] - $coords[0];

        $draw = new \ImagickDraw();

        $draw->setFont($font);
        $draw->setFontSize(48);
        $draw->setStrokeAntialias(true);
        $draw->setTextAntialias(true);
        $draw->setFillColor('#' . $color);

        $textOnly = new \Imagick();
        $textOnly->newImage($baseWidth, 300, "rgba(230, 230, 230, 0)");
        $textOnly->setImageFormat('png');
        $textArray = explode("\n", $text);

        $numberOfLine = 1;
        foreach ($textArray as $line) {
            $X = 30;
            $Y = 40 * $numberOfLine;

            switch ($align) {
                case 'left':
                    $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
                    break;
                case 'center':
                    $draw->setTextAlignment(\Imagick::ALIGN_CENTER);
                    break;
                case 'right':
                    $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
                    break;
            }

            $textOnly->annotateImage($draw, 300, $Y, 0, $line);
            $numberOfLine++;
        }
        $textOnly->trimImage(0);
        $textOnly->setImagePage($textOnly->getimageWidth(), $textOnly->getimageheight(), 0, 0);

        $angle = (180 * $baseWidth) / (M_PI * $radius);

        if ($downText) {
            $distort = $angle > 180 ? array(180, 180) : array($angle, 180);
            $textOnly->flopImage();
            $textOnly->flipImage();
        } else {
            $distort = $angle > 180 ? array(180, 0) : array($angle, 0);
        }

        $textOnly->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);

        $textOnly->setImageMatte(true);
        $textOnly->distortImage(Imagick::DISTORTION_ARC, $distort, true);

        $textOnly->setformat('png');

        $path     = Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS;
        $filename = $this->_getUniqueFilename($path);
        file_put_contents($path . $filename, $textOnly);

        return $filename;
    }


    protected function _imagettftextoutline(&$im, $size, $angle, $x, $y, &$outlinecol, $fontfile, $text, $width)
    {
        // For every X pixel to the left and the right
        $widthIterration = ceil($width / 10);//for very big width
        for ($xc = $x - abs($width); $xc <= $x + abs($width); $xc += $widthIterration) {
            // For every Y pixel to the top and the bottom
            for ($yc = $y - abs($width); $yc <= $y + abs($width); $yc += $widthIterration) {
                $text1 = imagettftext($im, $size, $angle, $xc, $yc, $outlinecol, $fontfile, $text);
            }
        }
    }

    /* protected function _dropshadow($im, $shadow, $outline){
         // Create an image the size of the original plus the size of the drop shadow

     }*/

    protected function _getUniqueFilename($path)
    {
        do {
            $filename = Mage::helper('aitcg')->uniqueFilename('.png');
        } while (file_exists($path . $filename));

        return $filename;
    }


    protected function _getColorOnImage($image, $color, $alpha = 0)
    {
        $color = str_split($color, 2);
        array_walk($color, create_function('&$n', '$n = hexdec($n);'));

        $color[0] = isset($color[0]) ? $color[0] : 0;
        $color[1] = isset($color[1]) ? $color[1] : 0;
        $color[2] = isset($color[2]) ? $color[2] : 0;

        return imagecolorallocatealpha($image, $color[0], $color[1], $color[2], $alpha);

    }

    public function getResolution()
    {
        return (int)Mage::getStoreConfig('catalog/aitcg/aitcg_font_resolution_predefine');
    }

    private function _getEmptyImage($width, $height)
    {
        $image = imagecreatetruecolor((int)$width, (int)$height);
        imagesavealpha($image, true);
        $backgroundColor = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $backgroundColor);

        return $image;
    }


}