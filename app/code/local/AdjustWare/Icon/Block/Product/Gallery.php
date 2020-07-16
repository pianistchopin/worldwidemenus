<?php
class AdjustWare_Icon_Block_Product_Gallery extends Mage_Catalog_Block_Product_Gallery
{
    public function getGalleryCollection()
    {
        $product = $this->getProduct();
        $optionId = $product->getAdjOption();
        $collection = Mage::getModel('adjicon/image')->getCollection();
        $images = $collection->addFieldToFilter('option_id', array('eq'=>$optionId))
                             ->addFieldToFilter('product_id', array('eq'=>$product->getId()));
        return $images;
    }

    public function getImageWidth()
    {
        $fileName = $this->getCurrentImage()->getFile();
        $file = Mage::getBaseDir('media') . DS . 'adjconfigurable' . DS . $fileName;
        if (file_exists($file)) {
            $size = getimagesize($file);
            if (isset($size[0])) {
                if ($size[0] > 600) {
                    return 600;
                } else {
                    return $size[0];
                }
            }
        }

        return false;
    }

    public function getImageFile()
    {
        $file = $this->getCurrentImage()->getFile();
        return Mage::helper('adjicon/image')->getAdjConfigurableBaseUrl($file);
    }

    public function getPreviusImage()
    {
        $current = $this->getCurrentImage();
        if (!$current) {
            return false;
        }
        $previus = false;
        foreach ($this->getGalleryCollection() as $image) {
            if ($image->getImageId() == $current->getImageId()) {
                return $previus;
            }
            $previus = $image;
        }
        return $previus;
    }

    public function getNextImage()
    {
        $current = $this->getCurrentImage();
        if (!$current) {
            return false;
        }

        $next = false;
        $currentFind = false;
        foreach ($this->getGalleryCollection() as $image) {
            if ($currentFind) {
                return $image;
            }
            if ($image->getImageId() == $current->getImageId()) {
                $currentFind = true;
            }
        }
        return $next;
    }

    public function getPreviusImageUrl()
    {
        if ($image = $this->getPreviusImage()) {
            return $this->getUrl('*/*/*', array('_current'=>true, 'image'=>$image->getImageId()));
        }
        return false;
    }

    public function getNextImageUrl()
    {
        if ($image = $this->getNextImage()) {
            return $this->getUrl('*/*/*', array('_current'=>true, 'image'=>$image->getImageId()));
        }
        return false;
    }
}
