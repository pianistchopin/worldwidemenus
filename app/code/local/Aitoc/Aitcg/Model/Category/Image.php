<?php
class Aitoc_Aitcg_Model_Category_Image extends Mage_Core_Model_Abstract
{   
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/category_image');
    }

    public function getImagesPath()
    {
        return Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'predefined_images' . DS;
    }
    public function getEmbossImagesUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). 'custom_product_preview/predefined_images/emboss/';
    }
    public function getEmbossImagesPath()
    {
        return Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'predefined_images' . DS.'emboss'.DS;
    }
    public function getImagesUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). 'custom_product_preview/predefined_images/';
    }

    public function delete()
    {
         if($this->getFilename()) {
            $fullPath = $this->getImagesPath() . $this->getFilename();
            @unlink($fullPath);                    
            $fullPath = $this->getImagesPath() . 'preview' . DS . $this->getFilename();
            @unlink($fullPath);                                
        }
        if($this->getEmbossfilename()) {
            $fullPath = $this->getEmbossImagesPath() . $this->getEmbossfilename();
            @unlink($fullPath);
            $fullPath = $this->getEmbossImagesPath() . 'preview' . DS . $this->getEmbossfilename();
            @unlink($fullPath);
        }
        return parent::delete();
    }
    public function setEmbossFilenameWithUnlink($filename){

        if($this->getEmbossfilename() && $this->getEmbossfilename() !=  $filename)
        {
            $fullPath = $this->getEmbossImagesPath() .$this->getEmbossfilename();
            @unlink($fullPath);
            $fullPath = $this->getEmbossImagesPath() . 'preview' . DS . $this->getFilename();
            @unlink($fullPath);

        }
        $this->setEmbossfilename($filename);
        $thumb = new Varien_Image($this->getEmbossImagesPath() . $this->getEmbossfilename());
        $thumb->open();
        $thumb->keepAspectRatio(true);
        $thumb->keepFrame(true);
        $thumb->backgroundColor(array(255,255,255));
        #$thumb->keepTransparency(true);
        $thumb->resize(130);
        $thumb->save($this->getEmbossImagesPath() . 'preview' . DS . $this->getEmbossfilename());
    }
    public function setFilenameWithUnlink($filename)
    {
        if($this->getFilename() && $this->getFilename() !=  $filename) 
        {
            $fullPath = $this->getImagesPath() . $this->getFilename();
            @unlink($fullPath);                    
            $fullPath = $this->getImagesPath() . 'preview' . DS . $this->getFilename();
            @unlink($fullPath);                           
        }                    
        $this->setFilename($filename);
        $thumb = new Varien_Image($this->getImagesPath() . $this->getFilename());
        $thumb->open();
        $thumb->keepAspectRatio(true);
        $thumb->keepFrame(true);
        $thumb->backgroundColor(array(255,255,255));
        #$thumb->keepTransparency(true);
        $thumb->resize(135);
        $thumb->save($this->getImagesPath() . 'preview' . DS . $this->getFilename());

    }
}
