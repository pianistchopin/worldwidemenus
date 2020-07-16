<?php
class AdjustWare_Icon_Model_Image extends Mage_Core_Model_Abstract
{
    protected $_imagePath;

    public function _construct()
    {
        parent::_construct();
        $this->_init('adjicon/image');
        $this->_imagePath = Mage::getBaseDir('media') . DS . 'adjconfigurable' . DS;
    }

    public function getOptionImagesSet($optionId, $productId)
    {
        return $this->getResource()->getOptionImagesSet($optionId, $productId);
    }

    public function getOptionImageBase($optionId, $productId)
    {
        return $this->getResource()->getOptionImageBase($optionId, $productId);
    }

    public function getImagePath()
    {
        return $this->_imagePath;
    }

    public function upload($optionId, $optionLabel, $productId)
    {
        $fieldName = 'image_' . $optionId;
        if (empty($_FILES[$fieldName]['name'])) {
            return $this;
        }
        $optionLabel = preg_replace('/[^A-Za-z0-9_\-]/', '_', $optionLabel);
        $imageName = $productId . '_' . $optionId . '_' . $optionLabel;
        $imageName .= '_' . time();// give unique names here
        $imageName .= '.' . strtolower(substr(strrchr($_FILES[$fieldName]['name'], '.'), 1));

        //upload an icon file
        $uploader = new Varien_File_Uploader($fieldName);
        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $uploader->save($this->_imagePath, $imageName);

        $thumbSizes = Mage::helper('adjicon')->getThumbsSizes();
        foreach ($thumbSizes as $thumbSize)
        {
            $this->resize($imageName, $thumbSize);
        }

        // store new values in DB
        $this->setOptionId($optionId);
        $this->setProductId($productId);
        $this->setFile($imageName);
        $this->save();
    }

    /**
     * if there's no thumb for the image, we make it
     *
     * @param string $imageName
     * @param int $size
     *
     * @return string
     */
    public function resize($imageName, $size)
    {
        $helper = Mage::helper('adjicon');
        $fileName = $helper->getThumbnailFileName($size, $imageName);
        if(!file_exists($this->_imagePath . $fileName))
        {
            $image = new Varien_Image($this->_imagePath . $imageName);
            $image->keepFrame(true);
            $image->keepAspectRatio(true);
            $image->keepTransparency(true);
            $image->backgroundColor(array(255, 255, 255));
            $image->resize($size);
            $image->save(null, $fileName);
        }

        return $fileName;
    }
}