<?php
class Aitoc_Aitcg_Model_Mask_Created extends Mage_Core_Model_Abstract
{   
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/mask_created');
    }
    
    protected function _afterSave() {
        //parent::_afterSave($object);
        $mask = Mage::getModel('aitcg/mask')->load($this->getMaskId());
        $filename = $mask->getImagesPath(). 'alpha' . DS.$mask->getFilename();
        $filename_white = $mask->getImagesPath(). 'alpha' . DS.'white_'.$mask->getFilename();
        $filename_white_pdf = $mask->getImagesPath(). 'alpha' . DS.'white_pdf_'.$mask->getFilename();
        $filename_black = $mask->getImagesPath(). 'alpha' . DS.'black_'.$mask->getFilename();
        $path = $this->getImagesPath();
        $filename_new = $path . 'mask_inverted.png';
        $filename_new_white = $path . 'white_mask_inverted.png';
        $filename_new_black = $path . 'black_mask_inverted.png';
        $filename_new_white_pdf = $path . 'white_pdf_mask_inverted.png';
        if(file_exists($filename)) {
            mkdir($path, 0777, true);
            copy($filename, $filename_new);
        }
        if(file_exists($filename_white)) {
            copy($filename_white, $filename_new_white);
        }
        if(file_exists($filename_black)) {
            copy($filename_black, $filename_new_black);
        }
        if(file_exists($filename_white_pdf)) {
            copy($filename_white_pdf, $filename_new_white_pdf);
        }
        //$this->createTumb();
        return parent::_afterSave();
        
    }
    
    protected function _beforeDelete() {
        
        $path = $this->getImagesPath();
        $objs = glob($path."/*");
        foreach($objs as $obj) {
            unlink($obj);
        }
        
        rmdir($path);
        //$this->createTumb();
        return parent::_beforeDelete();
        
    }

    public function getImagesPath()
    {
        return Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'masks_created' . DS . $this->getId().DS;
    }    


    public function getImagesUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). 'custom_product_preview/masks_created/'.$this->getId().'/';
    }        
}
