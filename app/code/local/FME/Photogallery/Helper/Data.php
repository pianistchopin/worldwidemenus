<?php
/**
 * Photo Photogallery & Products Photogallery extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Photogallery
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */
 
 
class FME_Photogallery_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_LIST_PAGE_TITLE                =    'photogallery/list/page_title';
    const XML_PATH_LIST_IDENTIFIER                =    'photogallery/list/identifier';
    const XML_PATH_LIST_META_DESCRIPTION        =    'photogallery/list/meta_description';
    const XML_PATH_LIST_META_KEYWORDS            =    'photogallery/list/meta_keywords';
    const XML_PATH_SEO_URL_SUFFIX                =    'photogallery/list/seourl_suffix';
        
    const XML_PATH_PHOTOGALLERY_ENABLED                    =    'photogallery/photogallery/enabled';
    const XML_PATH_PHOTOGALLERY_IMAGES_PER_PAGE            =    'photogallery/photogallery/imagesperpage';
    const XML_PATH_PHOTOGALLERY_GALLERY_WIDTH            =    'photogallery/photogallery/photogallerywidth';
    const XML_PATH_PHOTOGALLERY_THUMB_WIDTH                =    'photogallery/photogallery/thumbwidth';
    const XML_PATH_PHOTOGALLERY_THUMB_HEIGHT            =    'photogallery/photogallery/thumbheight';
    const XML_PATH_PHOTOGALLERY_KEEP_ASPECT_RATIO        =    'photogallery/photogallery/keepaspectratio';
    const XML_PATH_PHOTOGALLERY_KEEP_FRAME                =    'photogallery/photogallery/keepframe';
    const XML_PATH_PHOTOGALLERY_BG_COLOR                =    'photogallery/photogallery/thumbbackgroundColor';
    const XML_PATH_PHOTOGALLERY_THUMB_MARGIN            =    'photogallery/photogallery/thumbmargin';
    const XML_PATH_PHOTOGALLERY_SHOW_CATEGORIES            =    'photogallery/photogallery/showcategories';
    const XML_PATH_PHOTOGALLERY_CATEGORIES_NAV_WIDTH    =    'photogallery/photogallery/categoriesnavwidth';
    const XML_PATH_PHOTOGALLERY_ANIMATION_SPEED            =    'photogallery/photogallery/animationspeed';
    const XML_PATH_PHOTOGALLERY_WAVY_ANIMATION            =    'photogallery/photogallery/wavyanimation';
    
    //lightbox config options have beeen disabled
    const XML_PATH_LIGHTBOX_THEME                    =    'photogallery/lightbox/theme';
    const XML_PATH_LIGHTBOX_ANIMATION_SPEED            =    'photogallery/lightbox/animationspeed';
    const XML_PATH_LIGHTBOX_SLIDE_SHOW                =    'photogallery/lightbox/slideshow';
    const XML_PATH_LIGHTBOX_AUTOPLAY_SLIDE_SHOW        =    'photogallery/lightbox/autoplayslideshow';
    const XML_PATH_LIGHTBOX_OPACITY                    =    'photogallery/lightbox/opacity';
    const XML_PATH_LIGHTBOX_SHOW_TITLE                =    'photogallery/lightbox/showtitle';
    const XML_PATH_LIGHTBOX_DEFAULT_WIDTH            =    'photogallery/lightbox/defaultwidth';
    const XML_PATH_LIGHTBOX_DEFAULT_HEIGHT            =    'photogallery/lightbox/defaultheight';
    
    /*Start Photo Gallery Page Settings*/
    public function getEnable()
    {
            return Mage::getStoreConfig('photogallery/list/enablephotogallery');
    }
    public function getPgStatus()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_ENABLED);
    }
    public function getPgImagesperpage()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_IMAGES_PER_PAGE);
    }
    public function getPgWidth()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_GALLERY_WIDTH);
    }
    public function getThumbWidth()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_THUMB_WIDTH);
    }
    public function getThumbHeight()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_THUMB_HEIGHT);
    }
    public function getAspectratioflag()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_KEEP_ASPECT_RATIO);
    }
    public function getKeepframe()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_KEEP_FRAME);
    }
    public function getBgcolor()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_BG_COLOR);
    }
    
    public function getThumbmargin()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_THUMB_MARGIN);
    }
    public function getPgShowcategories()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_SHOW_CATEGORIES);
    }
    public function getPgNavwidth()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_CATEGORIES_NAV_WIDTH);
    }
    public function getAnimationSpeed()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_ANIMATION_SPEED);
    }
    public function getWavyAnimation()
    {
        return Mage::getStoreConfig(self::XML_PATH_PHOTOGALLERY_WAVY_ANIMATION);
    }
    /*End Photo Gallery Page Settings*/
    
    /*Start Lightbox Settings*/
    public function getLightboxTheme()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX_THEME);
    }
    public function getLightboxAnimationSpeed()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX_ANIMATION_SPEED);
    }
    public function getLightboxSlideShow()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX_SLIDE_SHOW);
    }
    public function getLightboxAutoplay()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX_AUTOPLAY_SLIDE_SHOW);
    }
    public function getLightboxOpacity()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX_OPACITY);
    }
    public function getLightboxShowTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX_SHOW_TITLE);
    }
    public function getLightboxWidth()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX_DEFAULT_WIDTH);
    }
    public function getLightboxHeight()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX_DEFAULT_HEIGHT);
    }
    /*End Lightbox Settings*/

    /*Start General Settings*/
    public function getListPageTitle()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIST_PAGE_TITLE);
    }
    
    public function getListIdentifier()
    {
        $identifier = Mage::getStoreConfig(self::XML_PATH_LIST_IDENTIFIER);
        if (!$identifier) {
            $identifier = 'photogallery';
        }

        return $identifier;
    }
    
    public function getListMetaDescription()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIST_META_DESCRIPTION);
    }
    
    public function getListMetaKeywords()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIST_META_KEYWORDS);
    }
    
    public function getUrl($identifier = null)
    {
        
        if (is_null($identifier)) {
            $url = Mage::getUrl('') . self::getListIdentifier() . self::getSeoUrlSuffix();
        } else {
            $url = Mage::getUrl('') . $identifier . self::getSeoUrlSuffix();
        }

        return $url;
        
    }
    public function getSeoUrlSuffix()
    {
        return Mage::getStoreConfig(self::XML_PATH_SEO_URL_SUFFIX);
    }
    
    public function geturlIdentifier()
    {
        $identifier = $this->getListIdentifier() . Mage::getStoreConfig(self::XML_PATH_SEO_URL_SUFFIX);
        return $identifier;
    }
    
    public function getPhotogalleryUrl()
    {
        $url = Mage::getUrl('') . self::getListIdentifier()  . self::getSeoUrlSuffix();
        return $url;
        
    }
    
    public function recursiveReplace($search, $replace, $subject)
    {
        if(!is_array($subject))
            return $subject;

        foreach($subject as $key => $value)
            if(is_string($value))
                $subject[$key] = str_replace($search, $replace, $value);
            elseif(is_array($value))
                $subject[$key] = self::recursiveReplace($search, $replace, $value);

        return $subject;
    }
    
    
    
    public function getThumbsDirPath($filePath = false)
    {
       $mediaRootDir = Mage::getBaseUrl('media')."gallery/galleryimages/";
           $thumbnailDir = Mage::getBaseUrl('media')."gallery/galleryimages/";
        if ($filePath && strpos($filePath, $mediaRootDir) === 0) {
        $thumbnailDir .= dirname(substr($filePath, strlen($mediaRootDir)));
        }

            $thumbnailDir .= DS . "thumb/";
        return $thumbnailDir;
    }
    
    public function strip_only($str, $tags, $stripContent = false) 
    {
        $content = '';
        if(!is_array($tags)) {
            $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
            if(end($tags) == '') array_pop($tags);
        }

        foreach($tags as $tag) {
            if ($stripContent)
                 $content = '(.+</'.$tag.'[^>]*>|)';
             $str = preg_replace('#</?'.$tag.'[^>]*>'.$content.'#is', '', $str);
        }

        return $str;
    }
    
    public function getWysiwygFilter($data)
    {
        $data=$this->strip_only($data, 'div');
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        return $processor->filter($data);
    }
    
    /**
     * Returns the resized Image URL
     *
     * @param string $imgUrl - This is relative to the the media folder (custom/module/images/example.jpg)
     * @param int $x Width
     * @param int $y Height
     */
    public function getResizedUrl($imgUrl,$x,$y=NULL)
    {

        $imgPath=$this->splitImageValue($imgUrl, "path");
        $imgName=$this->splitImageValue($imgUrl, "name");

        /**
         * Path with Directory Seperator
         */
        $imgPath=str_replace("/", DS, $imgPath);

        /**
         * Absolute full path of Image
         */
        
         //$imgPathFull=Mage::getBaseDir("media").DS.$imgPath.DS.$imgName;
         
        $imgPathFull=Mage::getSingleton('photogallery2/config')->getBaseTmpMediaPath().$imgPath.DS.$imgName;

        /**
         * If Y is not set set it to as X
         */
        $widht=$x;
        $y?$height=$y:$height=$x;

        /**
         * Resize folder is widthXheight
         */
        $resizeFolder=$widht."X".$height;

        /**
         * Image resized path will then be
         */
        $imageResizedPath=Mage::getSingleton('photogallery2/config')->getBaseTmpMediaPath().$imgPath.DS.$resizeFolder.DS.$imgName;

        /**
         * First check in cache i.e image resized path
         * If not in cache then create image of the width=X and height = Y
         */
        if (!file_exists($imageResizedPath) && file_exists($imgPathFull)) :
            $imageObj = new Varien_Image($imgPathFull);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->resize($widht, $height);
            $imageObj->save($imageResizedPath);
        endif;

        /**
         * Else image is in cache replace the Image Path with / for http path.
         */
        $imgUrl=str_replace(DS, "/", $imgPath);

        /**
         * Return full http path of the image
         */
        return Mage::getBaseUrl("media")."/gallery/galleryimages".$imgUrl."/".$resizeFolder."/".$imgName;
    }

    /**
     * Splits images Path and Name
     *
     * Path=custom/module/images/
     * Name=example.jpg
     *
     * @param string $imageValue
     * @param string $attr
     * @return string
     */
    public function splitImageValue($imageValue,$attr="name")
    {
        $imArray=explode("/", $imageValue);

        $name=$imArray[count($imArray)-1];
        $path=implode("/", array_diff($imArray, array($name)));
        if($attr=="path"){
            return $path;
        }
        else
            return $name;

    }

    /**
     * Get the Galerries Title
     *
     */
    public function getGalleryHeadings($galleryId)
    {
        $galleries = Mage::getModel('photogallery/photogallery')->getCollection()
                            ->addFieldToFilter('parent_gallery_id', $galleryId)
                            ->addFieldToFilter('status', 1)
                            ->addStoreFilter(Mage::app()->getStore(true)->getId())
                            ->addOrder('main_table.gorder', 'ASC')
                            ->getData();
        

        return $galleries;
    }


    public function getAlbums()
    {
        $collection = Mage::getModel('photogallery/gallery')->getCollection()
                  ->addStoreFilter(Mage::app()->getStore(true)->getId())
                  ->addFieldToFilter('status', 1);
        return $collection;
    }

    
    
    
    
    /*
	* Converts title to URL key according to URL standard
	* @param string @name title
	* @return string URL key
	*/
    
    public function nameToUrlKey($name)
    {
        $_URL_ENCODED_CHARS = array(
        ' ', '+', '(', ')', ';', ':', '@', '&', '`', '\'',
        '=', '!', '$', ',', '/', '?', '#', '[', ']', '%',
        );
    
        $name = strtolower(trim($name));
        $name = str_replace($_URL_ENCODED_CHARS, '-', $name);
        do {
        $name = $newStr = str_replace('--', '-', $name, $count);
        } while($count);
    
        return $name;
    }
    
}
