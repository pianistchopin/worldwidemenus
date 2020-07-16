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
        return parent::delete();
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
