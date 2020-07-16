<?php
class Aitoc_Aitcg_Model_Sharedimage extends Aitoc_Aitcg_Model_Image
{

    protected $_eventPrefix = 'aitcg_sharedimage';

    protected $_sharedImagesFolderName = 'shared_images';

    protected $_widthForImagesOnProductViewPage = 400;

    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/sharedimage');
    }

    public function getSharedImgPath()
    {
        $path = Mage::getBaseDir('media') . DS . $this->_moduleFolderPath . $this->_sharedImagesFolderName . DS . 
                    $this->getProductId() . DS . $this->getSharedImgId() . DS . $this->getSharedImgId() . '.png';

        return $path;        
    }

    public function getSharedSmallImgPath()
    {
        $path = Mage::getBaseDir('media') . DS . $this->_moduleFolderPath . $this->_sharedImagesFolderName . DS . 
                    $this->getProductId() . DS . $this->getSharedImgId() . DS . $this->getSharedImgId() . '_sm.png';

        return $path;        
    }

    public function getSharedThumbnailImgPath()
    {
        $path = Mage::getBaseDir('media') . DS . $this->_moduleFolderPath . $this->_sharedImagesFolderName . DS . 
                    $this->getProductId() . DS . $this->getSharedImgId() . DS . $this->getSharedImgId() . '_thumb.png';

        return $path;        
    }

    public function getUrlFullSizeSharedImg()
    {
        return Mage::getBaseUrl('media') . $this->_moduleFolderPath . $this->_sharedImagesFolderName . '/' .
            $this->getProductId() . '/' . $this->getId() . '/' . $this->getId() . '.png';
    }

    public function getUrlSmallSizeSharedImg()
    {
        return Mage::getBaseUrl('media') . $this->_moduleFolderPath . $this->_sharedImagesFolderName . '/' . 
            $this->getProductId() . '/' . $this->getId() . '/' . $this->getId() . '_sm.png';
    }

    public function getUrlThumbnailSharedImg()
    {
        return Mage::getBaseUrl('media') . $this->_moduleFolderPath . $this->_sharedImagesFolderName . '/' . 
            $this->getProductId() . '/' . $this->getId() . '/' . $this->getId() . '_thumb.png';
    }
    
    public function productNotExist()
    {
        if(!$this->getProductId())
        {
            return true;
        }
        $storeId = Mage::app()->getStore()->getId();
        $product = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($this->getProductId());
        if (!Mage::helper('catalog/product')->canShow($product)) 
        {
            return true;
        }
        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) 
        {
            return true;
        }

        return false;
    }

    public function imagesNotExist()
    {
        $images = array(
            'img_full'      => $this->getSharedImgPath(), 
            'img_small'     => $this->getSharedSmallImgPath(), 
            'img_thumbnail' => $this->getSharedThumbnailImgPath()
        );
        foreach($images as $image)
        {
            if(!file_exists($image))
            {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @param   integer $productId
     * @param   string $reservedImgId
     * @param   string $pngData
     * 
     * @return integer
     */
    public function createImage($productId, $reservedImgId, $pngData)
    {
        if($reservedImgId == 0)
        {
            return self::RESULT_CODE_ERROR;
        }

        if(!$this->_writeImage($productId, $reservedImgId, $pngData))
        {
            return self::RESULT_CODE_ERROR;
        }
        
        $this->setData('shared_img_id', $reservedImgId);
        $this->setData('product_id', $productId);
        
        $this->save();
        
        if($this->imagesNotExist())
        {
            return self::RESULT_CODE_ERROR;
        }

        return self::RESULT_CODE_SUCCESS;
    }

    protected function _initPath(&$params)
    {
        $folderName = Mage::getBaseDir('media') . DS . $this->_moduleFolderPath . $this->_sharedImagesFolderName . DS . $params->getProductId() . DS . $params->getReservedImageId();
        $fileNamePattern = $folderName . DS . $params->getReservedImageId();

        $params
            ->setFolderName($folderName)
            ->setFileName($fileNamePattern . '.' . Aitoc_Aitcg_Model_Image::FORMAT_PNG)
            ->setFileNameSmall($fileNamePattern . '_sm.' . Aitoc_Aitcg_Model_Image::FORMAT_PNG)
            ->setFileNameThumb($fileNamePattern . '_thumb.' . Aitoc_Aitcg_Model_Image::FORMAT_PNG);
    }

    /**
     * @param   Varien_Object $params
     *
     * @return bool
     */
    protected function _writeAdditionalImages(Varien_Object $params)
    {
        $imageSizes = getimagesize($params->getFileName());
        if ($imageSizes[0] > $this->_widthForImagesOnProductViewPage)
        {
            $this->resize(
                $params->getFileName(),
                $params->getFileNameSmall(),
                $this->_widthForImagesOnProductViewPage,
                0,
                true,
                Aitoc_Aitcg_Model_Image::FORMAT_PNG
            );
        } else {
            if (!copy($params->getFileName(), $params->getFileNameSmall()))
            {
                return false;
            }
        }
        $this->resize($params->getFileName(),$params->getFileNameThumb(), 200, 0, true, Aitoc_Aitcg_Model_Image::FORMAT_PNG);
        return true;
    }

    protected function checkImageDimensions($sizeX, $sizeY)
    {
        $allowedValueX = Mage::getStoreConfig('catalog/aitcg/aitcg_social_networks_sharing_max_img_width');
        $allowedValueY = Mage::getStoreConfig('catalog/aitcg/aitcg_social_networks_sharing_max_img_height');

        if(($sizeX > $allowedValueX) || ($sizeY > $allowedValueY))
        {
            return false;
        }

        return true;
    }

    /**
     * Extract image source path from image url
     * Ex: $url = 'http://local.aitoc.com/1702burim/media/custom_product_preview/quote/white_97.jpg';
     * Return: '/home/magento/1702burim/media/custom_product_preview/quote/white_97.jpg'
     * 
     * @param string $url
     * 
     * @return string
     */
    protected function _getSrcImgPathFromUrl($url)
    {
        $pos = strpos($url, '/media/' . $this->_moduleFolderPath);
        $newurl = substr($url, $pos+30);
        $newurl = Mage::getBaseDir('media') . DS . $this->_moduleFolderPath . $newurl;
        $newurl = str_replace('/', DS, $newurl);

        return $newurl;
    }
}
