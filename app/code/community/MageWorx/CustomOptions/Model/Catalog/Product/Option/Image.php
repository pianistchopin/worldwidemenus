<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_CustomOptions_Model_Catalog_Product_Option_Image {
    
    protected $_imageFile = '';
    protected $_width = 70;
    protected $_height = 70;
    
    public function init($imageFile) {
        $this->_imageFile = $imageFile;
        return $this;
    }
    
    public function resize($width, $height = null) {
        $this->_width = $width;
        $this->_height = $height;        
        return $this;
    }
    
    public function setWatermarkSize($size) {
        return $this;
    }
    
    public function __toString() {
        $imgData = Mage::helper('mageworx_customoptions')->getImgData($this->_imageFile, false, false, $this->_width);
        if (!isset($imgData['url'])) return '';
        return $imgData['url'];
    }
    
    public function constrainOnly($flag) {
        $this->_constrainOnly = $flag;
        return $this;
    }
    
    public function keepAspectRatio($flag) {
        $this->_keepAspectRatio = $flag;
        return $this;
    }
    
    public function keepFrame($flag, $position = array('center', 'middle')) {
        $this->_keepFrame = $flag;
        return $this;
    }

}