<?php
class Aitoc_Aitcg_Model_Product_Image extends Mage_Catalog_Model_Product_Image
{
    /**
     * @return Aitoc_Aitcg_Varien_Image
     */
    public function getImageProcessor()
    {
        if( !$this->_processor ) {
            $this->_processor = new Aitoc_Aitcg_Varien_Image( $this->getBaseFile() );
        }
        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        if(version_compare(Mage::getVersion(), '1.4.0.0', '>='))
        {
            $this->_processor->quality($this->_quality);
        }
        return $this->_processor;
    }
    
    public function calcOriginalSize() {
        $processor = $this->getImageProcessor();
        $processor->getMimeType();
        return $processor->getSrcImageDimension();
    }
    
    /**
     * Set filenames for base file and new file
     *
     * @param string $file
     * @param string $baseFileDir
     * @return Aitoc_Aitcg_Model_Product_Image
     */
    public function setBaseFile($file, $baseFileDir = false)
    {
        $this->_isBaseFilePlaceholder = false;

        if (($file) && (0 !== strpos($file, '/', 0))) {
            $file = '/' . $file;
        }
        //$baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        $baseDir = Mage::getBaseDir('media');
        if($baseFileDir === false) {
            $baseFileDir = $baseDir;
        }
        $file = $this->_getFileName($file, $baseFileDir);
        
        if (!$file) {
            // check if placeholder defined in config
            $file = $this->_getConfigPlaceholder($file, $baseDir);
            
                // replace file with skin or default skin placeholder
                $baseDir = $this->_getBaseDirSkin($baseDir,$file);
            
            $this->_isBaseFilePlaceholder = true;
        }

        //$baseFile = $baseDir . $file;
        $this->_setBaseFile($baseFileDir, $file);

        // append prepared filename
        $this->_newFile = $this->_getPathImageName($file); // the $file contains heading slash

        return $this;
    }  
    
    protected function _getBaseDirSkin($baseDir, $file)
    {
        
        $skinBaseDir     = Mage::getDesign()->getSkinBaseDir();
        $isConfigPlaceholder = Mage::getStoreConfig("catalog/placeholder/{$this->getDestinationSubdir()}_placeholder");
        $configPlaceholder   = '/placeholder/' . $isConfigPlaceholder;
        if (!$isConfigPlaceholder || !$this->_fileExists($baseDir . $configPlaceholder)) 
        {
            $skinBaseDir     = Mage::getDesign()->getSkinBaseDir();
            if (file_exists($skinBaseDir . $file)) {
                $baseDir = $skinBaseDir;
            }
            else {
                $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default'));
                if (!file_exists($baseDir . $file)) {
                    $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default', '_package' => 'base'));
                }
            }
        }        
        
        return $baseDir;
    }
    
    protected function _setBaseFile($baseFileDir, $file)
    {
        
        //$baseFile = $baseDir . $file;
        $baseFile = $baseFileDir . $file;

        if ((!$file) || (!file_exists($baseFile))) {
            throw new Exception(Mage::helper('catalog')->__('Image file was not found.'));
        }

        $this->_baseFile = $baseFile;
        
    }
    
    protected function _getSkinPlaceholder()
    {
        
        $skinBaseDir     = Mage::getDesign()->getSkinBaseDir();
        $skinBaseDir     = Mage::getDesign()->getSkinBaseDir();
        return "/images/catalog/product/placeholder/{$this->getDestinationSubdir()}.jpg";
                
    }
    
    protected function _getConfigPlaceholder($file, $baseDir)
    {
        $isConfigPlaceholder = Mage::getStoreConfig("catalog/placeholder/{$this->getDestinationSubdir()}_placeholder");
        $configPlaceholder   = '/placeholder/' . $isConfigPlaceholder;
        if ($isConfigPlaceholder && $this->_fileExists($baseDir . $configPlaceholder)) {
            $file = $configPlaceholder;
        }
        else
        {
            $file = $this->_getSkinPlaceholder();
        }
        return $file;
    }
    
    protected function _getFileName($file, $baseFileDir)
    {
    
        if ('/no_selection' == $file) {
            return null;
        }
        if ($file) {
            //if ((!$this->_fileExists($baseDir . $file)) || !$this->_checkMemory($baseDir . $file)) {
            if ((!$this->_fileExists($baseFileDir . $file)) || !$this->_checkMemory($baseFileDir . $file)) {
                return null;
            }
        }
        return $file;
    }
    /**
     * @param string $filename
     * @return bool
     */
    protected function _getPathImageName($file)
    {
            // build new filename (most important params)
        $path = array(
            Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath(),
            'cache',
            Mage::app()->getStore()->getId(),
            $path[] = $this->getDestinationSubdir()
        );
        if((!empty($this->_width)) || (!empty($this->_height)))
            $path[] = "{$this->_width}x{$this->_height}";

    // add misk params as a hash
            $miscParams = array(
                ($this->_keepAspectRatio  ? '' : 'non') . 'proportional',
                ($this->_keepFrame        ? '' : 'no')  . 'frame',
                ($this->_keepTransparency ? '' : 'no')  . 'transparency',
                ($this->_constrainOnly ? 'do' : 'not')  . 'constrainonly',
                $this->_rgbToString($this->_backgroundColor),
                'angle' . $this->_angle,
                'quality' . $this->_quality
        );

        // if has watermark add watermark params to hash
        if ($this->getWatermarkFile()) {
            $miscParams[] = $this->getWatermarkFile();
            $miscParams[] = $this->getWatermarkImageOpacity();
            $miscParams[] = $this->getWatermarkPosition();
            $miscParams[] = $this->getWatermarkWidth();
            $miscParams[] = $this->getWatermarkHeigth();
        }

        $path[] = md5(implode('_', $miscParams));
            return implode('/', $path) . $file;
    }
    
    /**
     * @param string $filename
     * @return bool
     */
    protected function _fileExists($filename) {
        if(version_compare(Mage::getVersion(), '1.5.0.0') >= 0) {
            return parent::_fileExists($filename);
        } else {
            return file_exists($filename);
        }
    }
    
    protected function _rgbToString($rgbArray)
    {
        if(version_compare(Mage::getVersion(), '1.4.0.1') > 0) {
            return parent::_rgbToString($rgbArray);
        } else {
            $result = array();
            foreach ($rgbArray as $value) {
                if (null === $value) {
                    $result[] = 'null';
                }
                else {
                    $result[] = sprintf('%02s', dechex($value));
                }
            }
            return implode($result);
        }
    }
 
}
