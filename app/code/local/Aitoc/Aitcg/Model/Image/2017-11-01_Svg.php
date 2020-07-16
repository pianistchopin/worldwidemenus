<?php

class Aitoc_Aitcg_Model_Image_Svg extends Mage_Core_Model_Abstract
{
    protected $_backgroundData = null;
    protected $_additionalData = array();
    protected $_bottomData = '';
    protected $_maskApplied = false;
    protected $_paletteSize = null;
    protected $_masksImages = array(
        'masks_created' => array(
            'white_mask_inverted.png',
            'white_pdf_mask_inverted.png',
            'black_mask_inverted.png'
        ),
        'alpha' => array(
            '1x1_white.png',
            '1x1_white_fill.png',
            '1x1_black.png'
        )
    );

    /**
     * Layers can be extracted in several classes.
     */
    protected $_printLayers = array(
        'bottom' => 0,
        'bg'     => 0,
        'top'    => 1,
        'mask'   => 0
    );
    protected $_layers = array(
        'header'    => '',
        'mask'      => '',
        'pdf_bg'    => '',
        'bottom'    => '',
        'bg'        => '',
        'top'       => ''
    );

    /**
     *
     * @param string $key
     * @param string $data
     *
     * @return Aitoc_Aitcg_Model_Image_Svg
     */
    public function initLayer($key, $data)
    {
        switch($this->getDataType())
        {
            case 'VML':
                preg_match('/WIDTH:\s(\d+)px;.*?HEIGHT:\s(\d+)px;/si',$data,$matches);
                $data = Mage::getModel('aitcg/image_converter')->vmlToSvg($data);
                $data = $model->normalizeMultiSvg($data, $matches[1],$matches[2]);
                break;
            case 'SVG':
                $this->_layers['header'] = '<?xml version="1.0" ?>';
                /// encoding="UTF-8" standalone="no"
                break;
            default:
                Mage::throwException(Mage::helper('aitcg')->__('Unknown image type'));
        }
        if($key == 'top') {
            $this->getPaletteSize($data);
        }
        //greedy! match
        if($key=="top")
        {
            if(preg_match('/\<image(.*)(\<\/svg\>|\/\>)/is', $data, $matches)) {
                $this->_layers[$key] = str_replace("</svg>","",$matches[0]);;
            }
        }
        else if(preg_match('/\<image(.*)(\<\/image\>|\/\>)/is', $data, $matches)) {
            $this->_layers[$key] = $matches[0];

        }
        /*if(preg_match('/\<image(.*)(\<\/image\>|\/\>)/is', $data, $matches)) {
            $this->_layers[$key] = $matches[0];
        }*/

        return $this;
    }

    public function getLayer($key = 'top')
    {
        return isset($this->_layers[$key]) ? $this->_layers[$key] : '';
    }

    public function setLayer($key, $data)
    {
        $this->_layers[$key] = $data;
        return $this;
    }

    public function getSvgData($header = true, $xlink = false)
    {
        $sizes = $this->getPaletteSize();
        $svg = ($header ? $this->getLayer('header') : '').
            '<svg '.
                'version="1.1" width="'.$sizes['width'].'" height="'.$sizes['height'].'" xmlns="http://www.w3.org/2000/svg" '.// style="overflow:hidden;position:relative"'.
                ($xlink ? 'xmlns:xlink="http://www.w3.org/1999/xlink" ' : '').
                '>'.
            '<desc>Created with Raphaël 2.1.2</desc>'.
            '<defs>'.
                ($this->_maskApplied ? $this->getLayer('mask') : '').
                ( (!$this->_maskApplied || !$this->allowToPrint('mask')) && $this->allowToPrint('bg') ? $this->_backgroundData['mask_xml'] : '').
            '</defs>'.
                $this->getLayer('pdf_bg').
                $this->_generateLayerSvg('bottom').

                $this->_generateLayerSvg('top').($this->allowToPrint('bg') ? $this->_backgroundData['image_xml'] : '').
            '</svg>';
        /**  $this->getLayer('pdf_bg').
        $this->_generateLayerSvg('bottom').
        ($this->allowToPrint('bg') ? $this->_backgroundData['image_xml'] : '').
        $this->_generateLayerSvg('top'). **/
        return $svg;
    }

    public function getPaletteSize($data = false)
    {
        if(is_null($this->_paletteSize)) {
            if(!$data) {
                Mage::throwException(Mage::helper('aitcg')->__('Palette size were not set'));
            }
            $this->_paletteSize = array();
            $arrTableWight = array();
            preg_match_all('/<svg[^>]*width="([^"]*)"/U', $data, $arrTableWight);
            $arrTableHeight = array();
            preg_match_all('/<svg[^>]*height="([^"]*)"/U', $data, $arrTableHeight);
            if(!empty($arrTableWight[1]) && !empty($arrTableHeight[1])) {
                $this->_paletteSize = array(
                    'width' => $arrTableWight[1][0],
                    'height' => $arrTableHeight[1][0],
                );
            }
        }
        return $this->_paletteSize;
    }

    /**
    *
    * @param float $width
    * @param float $height
    *
    * @return Aitoc_Aitcg_Model_Image_Svg
    */
    public function setPaletteSize($width, $height)
    {
        $this->_paletteSize = array(
            'width' => $width,
            'height' => $height,
        );
        return $this;
    }
    
    /**
     * Format svg xml output and apply 'xlink' tag to href elements
     * 
     * @param string $data
     * 
     * @return string
     */
    public function applyXlinkToData($data)
    {
        // incorrect href attribute for Safari and Chrome browser
        // the HACK
        $data = str_replace('href=','xlink:href=',$data);
        $data = preg_replace('/ NS(\d+):/si',' ',$data);
        // prevent from double xlink:xlink:href if SVG is saved form Firefox
        $data = str_replace('xlink:xlink','xlink',$data);


        return $data;
    }

    public function normalizeSvgData()
    {
        $data = $this->getSvgData();
        $data = preg_replace('/xmlns:xlink="http:\/\/www\.w3\.org\/1999\/xlink"/si','',$data);

        $data = $this->applyXlinkToData($data);

        $data = preg_replace('/xmlns="http:\/\/www\.w3\.org\/2000\/svg"/si','xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"',$data);

        $data = $this->_changeUrlToImage($data);

        return $data;
    }

    public function normalizeMultiSvg($data, $w, $h)
    {
        $pregTable = '/<svg x="(.*)px"[^>]*y="(.*)px"[^>]*><image x="(.*)"[^>]*y="(.*)"[^>]*\/>[^>]*<\/svg>/U';
        $arrTable = array();
        preg_match_all($pregTable, $data, $arrTable);

        foreach($arrTable[0] as $key=>$img)
        {
            $img_new = preg_replace('/<svg[^>]*>/U','',$img);
            $img_new = preg_replace('/<\/svg>/U','',$img_new);
            $img_new = preg_replace('/x="(.*)"/U','x="'.($arrTable[1][$key]+$arrTable[3][$key]).'"',$img_new);
            $img_new = preg_replace('/y="(.*)"/U','y="'.($arrTable[2][$key]+$arrTable[4][$key]).'"',$img_new);
            $data = str_replace($img, $img_new, $data);
        }
        $data = preg_replace('/<svg[^>]*>/U','<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"   preserveAspectRatio="none" width="'.$w.'" height="'.$h.'">',$data);


        return $data;
    }

    public function normalizeMask()
    {
        $pregTable = '/<image [^>]*\/mask.*>.*<\/image>/U';
        $arrTable = array();
        $data = $this->getLayer('top');
        preg_match_all($pregTable, $data, $arrTable);
        if(empty($arrTable[0]))
        {
            $pregTable = '/<image [^>]*\/mask.*\/>/U';
            preg_match_all($pregTable, $data, $arrTable);
            if(empty($arrTable[0]))
            {
                return $this;
            }
        }

        //flag that mask was applied to a SVG
        $this->_maskApplied = true;

        foreach($arrTable[0] as $img)
        {
            $data = str_replace ($img, '',$data);
        }

        $this->setLayer('top', $data);
        if(!$this->allowToPrint('mask')) {
            return $this;
        }

        $palette = $this->getPaletteSize();
        $masks = implode('',$arrTable[0]);
        if( $this->allowToPrint('bg') && $this->getApplyOffsets() ) {
            $masks = preg_replace('/x="([^"]*)"/eU', ' "x=\"". ("$1" + '.$this->_backgroundData['offset_x'].') ."\"" ', $masks);
            $masks = preg_replace('/y="([^"]*)"/eU', ' "y=\"". ("$1" + '.$this->_backgroundData['offset_y'].') ."\"" ', $masks);
        }
        if(isset($palette['width'], $palette['height'])) {
            $masks = '<mask id="fademask" x="0" y="0" width="'.$palette['width'].'" height="'.$palette['height'].'">'.$masks.'</mask>';
        } else {
            $masks = '<mask id="fademask" >'.$masks.'</mask>';
        }
        $this->setLayer('mask', $masks);
        return $this;
    }

    /**
     * Unpack additional data sended from client and process it by dispatchin event to be able to customize resulted data
     *
     * @param string $type
     *
     * @return Aitoc_Aitcg_Model_Image_Svg
     */
    public function processAdditional($data)
    {
        $data = $this->_unpackJson($data);
        $this->_additionalData = $data;
        Mage::dispatchEvent('aitoc_aitcg_process_svg', array(
            'svg'        => $this,
            'additional' => $data
        ));
    }

    public function getAdditionalData($key = false)
    {
        if (!$key)
        {
            return $this->_additionalData;
        }
        if (isset($this->_additionalData[$key]))
        {
            return $this->_additionalData[$key];
        }
        return false;
    }

    protected function _isSvgMaskApplied()
    {
        return ((bool)$this->_maskApplied && $this->allowToPrint('mask')) || $this->allowToPrint('bg');
    }

    /**
     * Apply bottom layer images to data string
     *
     * @var string $key
     *
     * @return string
     */
    protected function _generateLayerSvg($key)
    {
        if(!$this->allowToPrint($key)) {
            return '';
        }
        if($this->_isSvgMaskApplied() == false) {
            return $this->getLayer($key);
        }
        $data = $this->getLayer($key);
        if($data == '') {
            return $data;
        }
        $offset_x = $offset_y = 0;
        if(!is_null($this->_backgroundData)) {
            $offset_x = $this->_backgroundData['offset_x'];
            $offset_y = $this->_backgroundData['offset_y'];
        }
        return '<g mask="url(#fademask)" transform="translate('.$offset_x.','.$offset_y.')">'.$data.'</g>';
    }

    /**
     * Apply background to svg if it's available for print
     *
     * @param string $backgroundImage
     * @param float $offset_x
     * @param float $offset_y
     * @param float $scale
     * @return Aitoc_Aitcg_Model_Image_Svg
     */
    public function prepareBackground($backgroundImage, $offset_x = 0, $offset_y = 0, $scale = 1)
    {
        $this
            ->setOffsetX($offset_x)
            ->setOffsetY($offset_y)
            ->setScale($scale);

        if(!$this->allowToPrint('bg')) {
            return $this;
        }
        $img = Mage::getSingleton('aitcg/image_transform')->getImg($backgroundImage);
        $src_w = round(imagesx($img) * $scale);
        $src_h = round(imagesy($img) * $scale);
        $offset_x = round($offset_x * $scale);
        $offset_y = round($offset_y * $scale);
        imagedestroy($img);

        $mask_x = 0;
        $mask_y = 0;
        if($this->getApplyOffsets()) {
            $mask_x = $offset_x;
            $mask_y = $offset_y;
        }

        $palette = $this->getPaletteSize();
        $this->setPaletteSize($src_w, $src_h);

        $this->_backgroundData = array(
            //xml string witch will apply background to svg
            'image_xml' => '<image xlink:href="'.$backgroundImage.'" preserveAspectRatio="none" height="'.$src_h.'" width="'.$src_w.'" y="0" x="0"></image>',
            //mask xml - will be applied if mask is not added to image, will hide all non-printable area places
            'mask_xml'  => '<mask id="fademask" x="0" y="0" width="'.$src_w.'" height="'.$src_h.'">'.
                    '<rect x="'.($mask_x).'" y="'.($mask_y).'" width="'.$palette['width'].'" height="'.$palette['height'].'" fill="white" />'.
                '</mask>',
            'width'     => $src_w,
            'height'    => $src_h,
            'offset_x'  => $offset_x,
            'offset_y'  => $offset_y,
        );
        return $this;
    }

    /**
     * Select data from background variables. Customization purpoces.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getBackgroundData($key = false)
    {
        if(!$key) {
            return $this->_backgroundData;
        }
        if(isset($this->_backgroundData[$key])) {
            return $this->_backgroundData[$key];
        }
        return null;
    }

    /**
     * Apply data to background variables if they are required to be updated
     *
     * @param string $key
     * @param mixed $value
     *
     * @return Aitoc_Aitcg_Model_Image_Svg
     */
    public function updateBackgroundData($key, $value)
    {
        $this->_backgroundData[$key] = $value;
        return $this;
    }

    /**
     * Unpack json encoded string into array if possible
     *
     * @param string $type
     *
     * @return array
     */
    protected function _unpackJson($data)
    {
        if ($data) {
            try {
                $data = json_decode($data, true);
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }
        if(!is_array($data)) {
            $data = array();
        }
        return $data;
    }

    /**
     * Validate client print_type and setup which layer we need to print
     *
     * @param string $type
     *
     * @return Aitoc_Aitcg_Model_Image_Svg
     */
    public function setPrintType($type)
    {
        $type = $this->_unpackJson($type);
        
        if (isset($type['all']) && (bool)$type['all']) {
            foreach($this->_printLayers as $key => $value) {
                $this->_printLayers[$key] = true;
            }
        } else {
            foreach($this->_printLayers as $key=>$value) {
                if(isset($type[$key])) {
                    $this->_printLayers[$key] = (bool)$type[$key];
                }
            }
        }
        Mage::dispatchEvent('aitoc_aitcg_process_svg_print_type', array(
            'svg'    => $this,
            'types'  => $type
        ));
        return $this;
    }

    /**
     * Check if special print layer is available for print
     *
     * @param boolean $key
     * @return boolean
     */
    public function allowToPrint($key)
    {
        if(isset($this->_printLayers[$key]) && $this->_printLayers[$key]) {
            return true;
        }
        return false;
    }

    public function resetMaskForPng()
    {
        $data = $this->getLayer('mask');
        $data = preg_replace('/1x1_black.png/U','1x1_white.png',$data);
        $data = preg_replace('/black_mask_inverted.png/U','white_mask_inverted.png',$data);
        $this->setLayer('mask',$data);
        return $this;
    }

    public function resetMaskForPDF()
    {
        $data = $this->getLayer('mask');
        $this->setLayer('mask',$data);
        return $this;
    }

    public function addWhiteFontForPDF()
    {
        $palette = $this->getPaletteSize();
        $this->setLayer('pdf_bg', '<image x="0" y="0" width="'.($palette['width']).'" height="'.($palette['height']).'" preserveAspectRatio="none" href="1x1_white_fill.png" />');

        return $this;
    }

    private function _changeUrlToImage($sData)
    {
        $oXml = new DOMDocument();

        $oXml->loadXML($sData);
        $oSvg = $oXml->getElementsByTagName('svg')->item(0);
        $oImages = $oSvg->getElementsByTagName('image');
        if(!$oImages->length > 0)
        {
            return $sData;
        }
        foreach($oImages as $oImage)
        {
            $sImg = Mage::getModel('aitcg/image_converter')->imageToBase64($this->_getPathFromUrl($oImage->getAttribute('xlink:href')));
            $oImage->setAttribute('xlink:href',$sImg);
        }
        return $oXml->saveXML();
    }


    /*
     * @param string $url
     * @return string
     */
    protected function _getPathFromUrl($url)
    {
        $maskPath = $this->_retrieveMaskPathFromUrl($url);
        if($maskPath){
            return $maskPath;
        }

        $path = $this->_retrievePathFromUrl($url);
        if($path){
            return $path;
        }
        
        return Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'quote' . DS.basename($url);
    }

    /*
     * @param string $url
     * @return mixed
     */
    protected function _retrieveMaskPathFromUrl($url)
    {
        $base_name = basename($url);
        foreach($this->_masksImages as $type => $names) {
            foreach($names as $file_name) {
                if($base_name == $file_name) {
                    if($type == 'masks_created') {
                        $part = dirname($url);
                        $part =  substr($part, strrpos($part, '/')+1);
                        $sub = 'masks_created' .DS.$part;
                    } else {
                        $sub = 'mask'.DS.'alpha';
                    }
                    return Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . $sub . DS . $base_name;
                }
            }
        }

        return false;
    }

    /*
     * @param string $url
     * @return mixed
     */
    protected function _retrievePathFromUrl($url)
    {
        $data = parse_url($url);
        if(isset($data['path'])) {
            $check = array(
                'media'.DS.'catalog', //checking if current image is from product images and located in media/catalog (default product image) folder
                'media'.DS.'custom_product_preview', //checking if current image is from product images and located in media/custom_product_preview folder (saved images for CPP module)
            );

            if(Mage::helper('aitcg')->isVYAEnabled()){
                $check[] = 'media'.DS.'adjconfigurable';
            }

            foreach($check as $path) {
                $pos = strpos($data['path'], $path);
                if($pos !== false) {
                    $folder = trim(substr($data['path'], $pos + 5 ), DS);
                    return Mage::getBaseDir('media') . DS . $folder;
                }
            }
        }

        return false;
    }
}

