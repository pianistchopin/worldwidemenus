<?php
class Aitoc_Aitcg_Model_Image_Transform extends Mage_Core_Model_Abstract
{
    /**
    * Convert resource $imges with rules from $info
    * 
    * @param resource $image
    * @param array $info
    * @param array $MergedImage
    * @return resource
    */
    public function transformImage($image, &$info, $MergedImage = null)
    {
        $h=imagesy($image);
        $w=imagesx($image);
        //$imageTransform = $image;
        if($h != round($info['height']) || $w != round($info['width']))
        {
            
            $imageTransform=imagecreatetruecolor($info['width'],$info['height']);
            
            imageAlphaBlending($imageTransform, false);
            imagesavealpha($imageTransform, true);
            
            imagecopyresampled($imageTransform,$image,0,0,0,0,round($info['width']),round($info['height']),$w,$h);
            $image = $imageTransform;
        }
        //$info['rotation'] = 90;
        if(!empty($info['rotation']))
        {
            #$col = imagecolorexactalpha ($image, 57, 57, 57, 127); 
            $col = imagecolorexactalpha ($image, 255, 255, 255, 127); 
            
            $imageTransform = imagerotate($image, -$info['rotation'], $col);
            
            $ims = imagecreatetruecolor(imagesx($imageTransform),imagesy($imageTransform)); 
            
            imagecolortransparent($ims, $col);
            imagefill($ims, 0, 0, $col); 
            
            imagecopy($ims, $imageTransform, 0, 0, 0, 0, imagesx($imageTransform), imagesy($imageTransform));
            
            $info['x'] -= (imagesx($ims)-imagesx($image))/2;
            $info['y'] -= (imagesy($ims)-imagesy($image))/2;
            
            $image = $ims;
            //imagepng($image);die();
        }
        $info['width'] = imagesx($image);
        $info['height'] = imagesy($image);
        if (!empty($MergedImage))
        {
            $new_image = array();
            if($info['x'] > 0)
            {
                $info['x'] = $info['x'] + $MergedImage['areaOffsetX'];
                $info['beginX'] = 0;
            }
            else
            {
                $info['beginX'] = -$info['x'];
                $info['width'] = $info['width'] + $info['x'];
                $info['x'] = $MergedImage['areaOffsetX'];
            }
            if($info['y'] > 0)
            {
                $info['y'] =$info['y']+ $MergedImage['areaOffsetY'];
                $info['beginY'] = 0;
            }
            else
            {
                $info['beginY'] = -$info['y'];
                $info['height'] = $info['height'] + $info['y'];
                $info['y'] = $MergedImage['areaOffsetY'];
            }
            if($info['x']+$info['width'] > $MergedImage['areaSizeX']+$MergedImage['areaOffsetX'])
            {
                $info['width'] = $MergedImage['areaSizeX']+$MergedImage['areaOffsetX'] - $info['x']; 
            }
            if($info['y']+$info['height'] > $MergedImage['areaSizeY']+$MergedImage['areaOffsetY'])
            {
                $info['height'] = $MergedImage['areaSizeY']+$MergedImage['areaOffsetY'] - $info['y']; 
            }
        }
       // die();
        //imagepng($imageTransform);die();
        return $image;
    }    
    
    /**
    * put your comment there...
    * 
    * @param resource $dst_im
    * @param resource $src_im
    * @param int $dst_x
    * @param int $dst_y
    * @param int $src_x
    * @param int $src_y
    * @param int $src_w
    * @param int $src_h
    * @param float $pct Opacity
    * @return bool
    */
    public function imageCopyAlpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){         
        if(!isset($pct)){ 
            return false; 
        }
        $w = imagesx( $src_im ); 
        $h = imagesy( $src_im ); 
        imagealphablending( $src_im, false );
        $minalpha = 127; 
        for( $x = 0; $x < $w; $x++ ) 
        for( $y = 0; $y < $h; $y++ ){ 
            $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF; 
            if( $alpha < $minalpha ){ 
                $minalpha = $alpha; 
            } 
        } 
        for( $x = 0; $x < $w; $x++ ){ 
            for( $y = 0; $y < $h; $y++ ){ 
                $colorxy = imagecolorat( $src_im, $x, $y ); 
                $alpha = ( $colorxy >> 24 ) & 0xFF; 
                if( $minalpha !== 127 ){ 
                    $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha ); 
                } else { 
                    $alpha += 127 * $pct; 
                } 
                $alphacolorxy = imagecolorallocatealpha( $src_im,
                                        ( $colorxy >> 16 ) & 0xFF,
                                        ( $colorxy >> 8 ) & 0xFF,
                                            $colorxy & 0xFF, $alpha
                                ); 
                if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){ 
                    return false; 
                } 
            } 
        } 
        return imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }
    
    /**
    * put your comment there...
    * 
    * @param resource $originalImage
    * @param resource $newImage
    * @param int $maskId
    * @param array $override
    */
    public function combineImageByMask($originalImage, $newImage, $maskId, $override = array())
    {
        $mask_created = Mage::getModel('aitcg/mask_created')->load($maskId);
        $mask_created_image = $this->getImg($mask_created->getImagesPath().'mask_inverted.png');
        $img_size=getimagesize($mask_created->getImagesPath().'mask_inverted.png');
        $mask_created_image_resized = imagecreatetruecolor($mask_created->getWidth(), $mask_created->getHeight());
        $white = imagecolorallocatealpha ($mask_created_image_resized,   255, 255, 255, 127);
        imagecolortransparent($mask_created_image_resized,$white );
        imagefill($mask_created_image_resized, 0, 0, $white); 

        imagealphablending($mask_created_image_resized, false);
        imagesavealpha($mask_created_image_resized, true);
        imagecopyresampled($mask_created_image_resized, $mask_created_image, 0, 0, 0, 0, $mask_created->getWidth(), $mask_created->getHeight(), $img_size[0], $img_size[1]);
        /*imagepng($mask_created_image_resized, $mask_created->getImagesPath().'mask_inverted123.png');
        die();*/
        imagedestroy($mask_created_image);
        $originalImage_copy = $originalImage;
        $startX= isset($override['area_offset_x']) ? $override['area_offset_x'] :$mask_created->getX();
        $startY= isset($override['area_offset_y']) ? $override['area_offset_y'] :$mask_created->getY();
        $black = imagecolorallocate ($mask_created_image_resized,   0, 0, 0);
        for ($x = 0; $x < $mask_created->getWidth(); $x++)
            for ($y = 0; $y < $mask_created->getHeight(); $y++)
            {
                if(imagecolorat($mask_created_image_resized, $x, $y) != $black)
                {
                    $pixelX = $x+$startX;
                    $pixelY = $y+$startY;
                    
                    $color_new = imagecolorat($newImage, $pixelX, $pixelY);
                    
                    $color_old = imagecolorat($originalImage_copy, $pixelX, $pixelY);
                    if($color_new == $color_old) { //fix for transparent images, if new and old colors are equal - do not update anything
                        continue;
                    }
                    
                    $color_copy = imagecolorallocate($originalImage_copy, ($color_new >> 16 ) & 0xFF, ( $color_new >> 8 ) & 0xFF, $color_new & 0xFF);
                    imagesetpixel($originalImage_copy, $pixelX, $pixelY, $color_copy);
                    //echo $pixelX.'x'.$pixelY.'='.$color_new.'x'.$color_old.'<br>';
                }
            }
        imagedestroy($mask_created_image_resized);
        return $originalImage_copy;
    }    
    
    /**
    * Load image from a file to the resource
    * 
    * @param string $file
    * @return resource
    */
    public function getImg($file)
    {
        $extension = strrchr($file, '.');
        $extension = strtolower($extension);

        switch($extension) {
                case '.jpg':
                case '.jpeg':
                        $im = @imagecreatefromjpeg($file);
                        break;
                case '.gif':
                        $im = @imagecreatefromgif($file);
                        break;
                case '.png':
                        $im = @imagecreatefrompng($file);
                        imageAlphaBlending($im, true);
                        imagesavealpha($im, true);
                        break;
                default:
                        $im = false;
                        break;
        }
        return $im;
    }    
}
