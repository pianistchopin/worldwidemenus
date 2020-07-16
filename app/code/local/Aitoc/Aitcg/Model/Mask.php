<?php
class Aitoc_Aitcg_Model_Mask extends Mage_Core_Model_Abstract
{   
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/mask');
    }
    public function getImagesPath()
    {
        return Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'mask' . DS;
    }    

    public function getResizename()
    {
        return 'fff';
    }    

    public function getImagesUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). 'custom_product_preview/mask/';
    }        
    
    public function createInvertMask()
    {
        $img_size=getimagesize($this->getImagesPath().$this->getFilename());
        
        $img=imagecreatefrompng($this->getImagesPath().$this->getFilename());
        $img2 = imagecreatetruecolor($img_size[0],$img_size[1]);
        $white = imagecolorallocatealpha ($img2,   255, 255, 255, 127);
        imagecolortransparent($img2,$white );
        imagefill($img2, 0, 0, $white); 
        $img3 = imagecreatetruecolor($img_size[0],$img_size[1]);
        $black2 = imagecolorallocatealpha ($img3,   0, 0, 0, 0);
        $white3 = imagecolorallocate ($img3,   255, 255, 255);
        imagecolortransparent($img3,$white3 );
        imagefill($img3, 0, 0, $black2); 
        $img4 = imagecreatetruecolor($img_size[0],$img_size[1]);
        $white4 = imagecolorallocatealpha ($img4,   255, 255, 255, 0);
        imagefill($img4, 0, 0, $white4); 
        $img5 = imagecreatetruecolor($img_size[0],$img_size[1]);
        $black5 = imagecolorallocatealpha ($img5,   0, 0, 0, 127);
        imagecolortransparent($img5,$black5 );
        imagefill($img5, 0, 0, $black5); 
        $white5 = imagecolorallocatealpha ($img5,   255, 255, 255, 0);
        $black_search = imagecolorallocate ($img,   0, 0, 0);
        $black = imagecolorallocate ($img2,   0, 0, 0);
        $black4 = imagecolorallocate ($img4,   0, 0, 0);

        imagealphablending($img2, false);
        imagesavealpha($img2, true);
        for($i = 0; $i < $img_size[0]; $i++){
            for($j = 0; $j < $img_size[1]; $j++){
                //echo imagecolorat($img, $i, $j)."<br />";
                if(imagecolorat($img, $i, $j) !== $black_search)
                {
                    imagesetpixel($img2, $i, $j, $black);
                    imagesetpixel($img3, $i, $j, $white3);
                    imagesetpixel($img4, $i, $j, $black4);
                    imagesetpixel($img5, $i, $j, $white5);
                }
            }
        }
        
        imagepng($img2, $this->getImagesPath().DS.'alpha'.DS.$this->getFilename());
        
        imagepng($img3, $this->getImagesPath().DS.'alpha'.DS.'white_'.$this->getFilename());
        
        imagepng($img4, $this->getImagesPath().DS.'alpha'.DS.'black_'.$this->getFilename());
        
        imagepng($img5, $this->getImagesPath().DS.'alpha'.DS.'white_pdf_'.$this->getFilename());
        
        imagedestroy($img);
        imagedestroy($img2);
        imagedestroy($img3);
    }        
    
    
    public function createTumb()
    {
        
        
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
}
