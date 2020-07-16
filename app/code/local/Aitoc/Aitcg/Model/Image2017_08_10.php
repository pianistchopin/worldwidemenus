<?php
class Aitoc_Aitcg_Model_Image extends Mage_Core_Model_Abstract
{
    const FORMAT_JPG = 'jpg';
    const FORMAT_PNG = 'png';

    const RESULT_CODE_SUCCESS = 1;
    const RESULT_CODE_ERROR_IMG_SIZE = 2;
    const RESULT_CODE_ERROR = 3;
    
    protected $_moduleFolderPath = 'custom_product_preview/';
    protected $_quoteFolderName = 'quote';
    protected $_orderFolderName = 'order';
    protected $_emailFolderName = 'email';
    protected $_order_store_model = null;
    
    protected $_storePreviewName = 'preview_full';
    protected $_storePreviewThumbnailName = 'preview_thumb';    
    
    protected $_storeProductImageName = 'image_full';
    protected $_storeProductImageThumbnailName = 'image_thumb';
    
    protected $_saveImages = false;
    protected $_imagesDeleted = false;
    
    private static $_callbacks = array(
        IMAGETYPE_GIF  => array('output' => 'imagegif',  'create' => 'imagecreatefromgif'),
        IMAGETYPE_JPEG => array('output' => 'imagejpeg', 'create' => 'imagecreatefromjpeg'),
        IMAGETYPE_PNG  => array('output' => 'imagepng',  'create' => 'imagecreatefrompng'),
        IMAGETYPE_XBM  => array('output' => 'imagexbm',  'create' => 'imagecreatefromxbm'),
        IMAGETYPE_WBMP => array('output' => 'imagewbmp', 'create' => 'imagecreatefromxbm'),
    );

    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/image');
    }
    
    public function getFullTempPath() {
        // -> /custom_product_preview/temp
        return $this->_moduleFolderPath . $this->_quoteFolderName;
    }
    
    public function getMediaTempPath() {
        // -> /home/local/.../media/custom_product_preview/temp
        return Mage::getBaseDir('media') . '/' . $this->getFullTempPath();
    }

    public function getEmailPath() {
        return Mage::getBaseDir('media') . '/' .$this->_moduleFolderPath . $this->_emailFolderName;
    }

    public function getEmailUrl() {
        return Mage::getBaseUrl('media') . '/' .$this->_moduleFolderPath . $this->_emailFolderName;
    }
    
    private function _getStorePath() {
        return $this->_moduleFolderPath . $this->_orderFolderName;
    }

    private function _getMediaStorePath() {
        return Mage::getBaseDir('media') . '/' . $this->_moduleFolderPath. $this->_orderFolderName;
    }

    private function _getMediaStoreFolder() {
        return $this->_getMediaStorePath() .'/'. $this->getId() . '/';
    }    
    
    private function _getStoreFolder() {
        return $this->_getStorePath() .'/'. $this->getId() . '/';
    }

    private function _getQuoteFolderPath() {
        return Mage::getBaseDir('media') . '/' . $this->_quoteFolderName;
    }
    
    private function _getOrderFolderPath() {
        return Mage::getBaseDir('media') . '/' . $this->_orderFolderName;
    }
    
    private function _getStorePreviewImagePath() {
        /*$ext = explode(".", $this->getFileName() );
        $ext = end($ext);*/
        return '/' . $this->_getStoreFolder() /*. $this->_storePreviewName . '.' . $ext*/;
    }
    
    private function _getMediaStorePreviewImagePath() {
        return Mage::getBaseDir('media') . $this->_getStorePreviewImagePath();
    }
    
    private function _getStoreUrl( $file ) {
        return Mage::getBaseUrl('media') . $this->_getStoreFolder() . $file;
    }
    
    private function _getImageUrl() {
        return Mage::getBaseUrl('media') . $this->getFullTempPath() . '/' . $this->getFileName();
    }
    
    public function getFileName() {
        return $this->getData('temp_id'). '_' . $this->getData('file_name');
    }
    
    private function _getPngThumbnailName() {
        return $this->getFileName() . '.png';
    }
    
    public function getImageFullPath() {
        return $this->getMediaTempPath() . '/' . $this->getFileName();
    }
    
    public function getFullData( ) {
        $return = $this->getData();
        
        if($this->getIsOrder() > 1 && $this->getIsOrder() != 10 && $this->getIsOrder() != 11) {
            $return['temp_image_url'] = $this->_getStoreFile( $this->getFileName(), $this->_storePreviewName );
            $return['temp_thumbnail_url'] = $this->_getStoreLastFile($this->_storePreviewThumbnailName);
            return $return;
        } else if ($this->getIsOrder() == 1 || $this->getIsOrder() >= 10) {
            $temp_image = $this->_getStorePreviewImagePath() . '.png';
        } else {
            $temp_image = '/' . $this->getFullTempPath() . '/' . $this->_getPngThumbnailName();
        }
        if(!file_exists(Mage::getBaseDir('media') . $temp_image) && empty($return['img_data'])) {
            $return['id'] = 0;
            return $return;
        }
        
        $return['temp_image_url'] = $this->_getImageUrl();        
        
        $img = $this->_getImageObject($temp_image);
                   
        $return['temp_thumbnail_url'] = $img->__toString();
        
        if($this->getIsOrder() == 1 || $this->getIsOrder() == 11) {
            $return['temp_image_url'] = $this->_getStoreFile( $this->getFileName(), $this->_storePreviewName );
            $return['temp_thumbnail_url'] = $this->_moveToStore( $img->getNewFile(), $this->_storePreviewThumbnailName, true, true);
            $this->setData('is_order', $this->getIsOrder()+1);
        }
     
        return $return;
    }
    
    protected function _getImageObject($temp_image)
    {
        $return = $this->getData();
            $img = Mage::helper('aitcg/image')
            ->loadLite( $temp_image, 'media' );

        $size = Mage::helper('aitcg/options')
                ->calculateThumbnailDimension( 100, 100, $return['preview_width'], $return['preview_height'] );

        $default_size_x = ($return["scale_x"] == 1) ? $size[0] : round($size[0] * $return["scale_x"]);
        $default_size_y = ($return["scale_y"] == 1) ? $size[1] : round($size[1] * $return["scale_y"]);
        $img->keepAspectRatio(false);

        if( $return["angle"] != 0 ) {
            $img->rotate( -1 * $return["angle"] );
            $img->backgroundColor(0,0,0,127);
        }

        $img->keepFrame(false)
            ->resize($default_size_x, $default_size_y);
                return $img;
    }
    
    private function _moveToStore($url, $to_name, $rename = false, $thumb = false) {
        if($url == "") {
            return $url;
        }
        $dest = $this->_getMediaStoreFolder();
        $ext = explode(".", $url);
        $ext = end($ext);
        $name = $to_name . "." . $ext;
        if($rename) {
            if($thumb) {
                $files = glob($dest . $to_name . '*'.$ext);
                if( sizeof($files) > 0 ) {
                    $name = $to_name . '_'.sizeof($files).'.' .$ext;
                }
            }
            $check = rename($url, $dest . $name);
        } else {
            $check = copy($url, $dest . $name);
        }                                    
        if($check) {
            return $this->_getStoreUrl( $name );
        } else {
            return $url;
        }
    }
    
    private function _getStoreFile( $file, $name ) {
        $ext = explode(".", $file);
        return $this->_getStoreUrl( $name . "." . end($ext) );
    }
    
    private function _getStoreLastFile( $name ) {
        $ext = 'png';
        $dest = $this->_getMediaStoreFolder();
        $files = glob($dest . $name . '*'.$ext);
        if( sizeof($files) > 1 ) {
            $name = $name . '_'.(sizeof($files)-1).'.' .$ext;
        } else {
            $name = $name . "." . $ext;
        }
        return $this->_getStoreUrl( $name );
    }
    
    public function getMediaImage( $product_id, $entity_id = 0, $thumbType = Aitoc_Aitcg_Helper_Data::INTEGRATION_DEFAULT ) {
        if($this->getData("image_template_id") != 0 ) {
            $entity_id = $this->getData("image_template_id");
        }

        // compatibility with VYA extension. Used on cart page for example.
        $imgData =$this->getData('img_data');
        $imgData = json_decode($imgData);
        if(isset($imgData->config->productImage) && isset($imgData->config->productImage->thumb) && strstr($imgData->config->productImage->fullUrl, 'adjconfigurable')){
            $media = Mage::getSingleton('aitcg/resource_template')
                ->getTemplateImageForVYA($thumbType, $imgData->config->productImage->fullUrl);
        }
        else{
        $media = Mage::getSingleton('aitcg/resource_template')
            ->getTemplateImage( $product_id, $entity_id ,$thumbType );
        }
        if($media === false) {
            return false;
        }

        if($this->getIsOrder() == 1 || $this->getIsOrder() == 2) {
            $media['thumbnail_url'] = $this->_moveToStore( $media['thumbnail_file'], $this->_storeProductImageThumbnailName);
            $media['full_image'] =    $this->_moveToStore( 
                Mage::getSingleton('catalog/product_media_config')->getMediaPath($media['value']), 
                $this->_storeProductImageName
            );
            $this->setData('is_order',3);
        } elseif($this->getIsOrder() >= 3) {
            $media['thumbnail_url'] = $this->_getStoreFile( $media['thumbnail_file'], $this->_storeProductImageThumbnailName);
            $media['full_image'] =    $this->_getStoreFile( $media['value'], $this->_storeProductImageName);
        }

        return $media;
    }
    
    public function storeImage() {
        if($this->getIsOrder()== 10) {
            $this->setData('is_order',11);
            return true;
        }
        if($this->getIsOrder() != 0) {
            return true;
        }
        $dest = $this->_getMediaStoreFolder();
        if(!is_dir($dest)) {
            mkdir($dest);
        }
        /*$moved = $this->copyPreviewFile();*/
        $moved = $this->copyImages();
        if($moved) {
            //$this->unlinkFile();
            $this->setData('is_order',1);
            $this->setData('create_time', now());
            $this->save();
            return true;
        }
        return false;
    }
    
    
    public function copyImages() {

        if(version_compare(Mage::getVersion(), '1.4.0.0', '>='))
        {
            $pictureData = Mage::helper('core')->jsonDecode($this->getImgData());
        }
        else
        {
            $pictureData = Zend_Json::decode($this->getImgData());
        }
        $imgData = $pictureData['data'];

        foreach($imgData as $key => $img)
        {
            $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false);
            $mediaUrlSecure = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, true);
            $mediaUrlSecureIrreg = str_replace('https:','http:', $mediaUrlSecure);

            $sourceFile = str_replace(array($mediaUrl, $mediaUrlSecure, $mediaUrlSecureIrreg), Mage::getBaseDir('media').DS, $img['src']);
            $destFile = str_replace('/quote/', DS . 'order' . DS . $this->getId() . DS, $sourceFile);
            if(file_exists($sourceFile)) {
                copy($sourceFile, $destFile);
           /* } elseif(file_exists($destFile)) {
                $imgData['key']['src']
            */ }else {
      
                throw new Mage_Core_Exception(Mage::helper('aitcg')->__('The preview image was not found, please get back to the shopping cart and check all the required product options'));
            }            
            
        }
        $newImgData = str_replace('/quote/', '/order/'.$this->getId().'/', $this->getImgData());
        $this->setImgData($newImgData );
        return true;
    }    
    
    public function copyPreviewFile() {
        $source = $this->getMediaTempPath() . '/' . $this->getFileName();
        $dest = $this->_getMediaStorePreviewImagePath();
        
        if(file_exists($source)) {
            return rename($source, $dest) && rename($source.'.png', $dest.'.png');
        } elseif(file_exists($dest)) {
            return true;
        } else {
            throw new Mage_Core_Exception(Mage::helper('aitcg')->__('The preview image was not found, please get back to the shopping cart and check all the required product options'));
            return false;
        } 
    }
    
    public function unlinkStoreFiles( ) {
        $dir = $this->_getMediaStoreFolder();
        $files = glob($dir . '*');
        foreach($files as $file) {
            unlink($file);
        }
        rmdir($dir);
    }
    
    public function unlinkFile( $file_name = false ) {
        $file_name = $file_name!==false ? $file_name : $this->getFileName();
        $dest = $this->getMediaTempPath() . '/' . $file_name;
        if(file_exists($dest))
            unlink($dest);

        $png_name = $this->getMediaTempPath() . '/' . $this->_getPngThumbnailName();
        if(file_exists($png_name))
            unlink( $png_name );
    }
    
    public function deleteData() {
        if($this->getData('is_order') == 0) {
            if($this->getFileName() != "")
                $check = $this->unlinkFile();
            else if ($this->getImgData() != "")
            {
                if(version_compare(Mage::getVersion(), '1.4.0.0', '>='))
                {
                    $images = Mage::helper('core')->jsonDecode($this->getImgData());
                }
                else
                {
                    $images = Zend_Json::decode($this->getImgData());
                }
                foreach($images as $image)
                {
                    $fileName = str_replace(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), Mage::getBaseDir('media').DS, $image['src']);
                    unlink($fileName);
                }
            }        
            
            
        } else {
            $this->unlinkStoreFiles();
        }
        $this->_imagesDeleted = true;
        return parent::delete();
    }
    
    public function saveImage() {
        $this->_saveImages = true;
        return $this;
    }
    
    public function delete() {
        if($this->_saveImages == false && $this->getFileName() != "" && $this->_imagesDeleted == false)
            $check = $this->unlinkFile();
        else if ($this->_saveImages == false && $this->getImgData() != "" && $this->_imagesDeleted == false)
        {
            if(version_compare(Mage::getVersion(), '1.4.0.0', '>='))
            {
                $images = Mage::helper('core')->jsonDecode($this->getImgData());
            }
            else
            {
                $images = Zend_Json::decode($this->getImgData());
            }
            foreach($images as $image)
            {
                $fileName = str_replace(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), Mage::getBaseDir('media').DS, $image['src']);
                unlink($fileName);
            }
        }
        parent::delete();        
    }
    

    public function isOrder() {
        return $this->getData('is_order')==0?false:true;
    }
    
    public function getOrderIds() {
        if($this->_order_store_model != null) {
            return $this->getData('order_ids');
        }
        $this->_order_store_model = Mage::getModel('aitcg/image_store');
        $collection = $this->_order_store_model->getOrdersByImageId( $this->getId() );
        $ids = array();
        foreach($collection as $id) {
            $ids[] = $id->getData('order_id');
        }
        $this->setData('order_ids', $ids);
        return $ids;
    }
    
    public function setOrderId( $id ) {
        $ids = $this->getOrderIds();
        if(in_array($id,$ids)) {
            return true;
        }
        $ids[] = $id;
        $this->setData( 'order_ids', $ids );
        $this->_order_store_model->setData('image_id',$this->getId())
            ->setData('order_id', $id)
            ->setData('id', null);
        $this->_order_store_model->save();
    }





    protected function _getValueFromOption($name, $option)
    {
        preg_match('/'.$name.': ([0-9]*),/', $option['value'], $array_preg);
        return empty($array_preg[1])?0:$array_preg[1];
        
    }
    
    protected function _getImg($file)
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
                        imageSaveAlpha($im, true);
                        break;
                default:
                        $im = false;
                        break;
        }
        
        
        return $im;
    }
    
    
    public function resize($fileNameOld, $fileNameNew, $sizeX, $sizeY = null, $ifSquare = false, $format = self::FORMAT_JPG)
    {
        if($ifSquare)
            $sizeY = $sizeX;
        
        $imgForResize = $this->_getImg($fileNameOld);
        
        $sizeXOld = imagesx($imgForResize);
        $sizeYOld = imagesy($imgForResize);
        
        if(empty($sizeY))
        {
            $sizeMult = $sizeXOld/$sizeX;
            $sizeY = $sizeYOld/$sizeMult;
        }
        else
            $sizeMult = ($sizeXOld/$sizeX > $sizeYOld/$sizeY)?$sizeXOld/$sizeX:$sizeYOld/$sizeY;
        
        
        $x = ($sizeX - $sizeXOld/$sizeMult)/2;
        $y = ($sizeY - $sizeYOld/$sizeMult)/2;
        
        $image=imagecreatetruecolor($sizeX,$sizeY);
        
        switch ($format) {
            case self::FORMAT_PNG :
                $col = imagecolorallocatealpha ($image, 0, 0, 0, 127); 
                imagealphablending($image, false);
                imagesavealpha($image, true);
                break;
            case self::FORMAT_JPG :
            default:
                $col = imagecolorexactalpha ($image, 255, 255, 255, 127);
        }
        
        imagecolortransparent($image, $col);        
        imagefill($image, 0, 0, $col);
        imagecopyresampled($image, $imgForResize, $x, $y, 0, 0, $sizeXOld/$sizeMult, $sizeYOld/$sizeMult, $sizeXOld, $sizeYOld);
        
        switch ($format) {
            case self::FORMAT_PNG :
                imagepng($image, $fileNameNew);
                break;
            case self::FORMAT_JPG :
            default:
                imagejpeg($image, $fileNameNew);
        }
        imagedestroy($image);
        
        return $fileNameNew;
    }

    public static function uploadFile($path, $nameOfParam, $arrayOfExt)
    {
        $uploader = new Varien_File_Uploader($nameOfParam);
        $uploader->setAllowedExtensions($arrayOfExt); 
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $uploader->save($path, preg_replace('/[^A-Za-z\d\.]/','_',$_FILES['filename']['name']));
        return $uploader->getUploadedFileName();
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

    /**
     * @param  Varien_Object $params
     *
     * @return integer
     */
    protected function _write(Varien_Object $params)
    {
        if (!is_dir($params->getFolderName()))
        {
            if (!mkdir($params->getFolderName(), 0777, true))
            {
                return false;
            }
        }

        if (!file_put_contents($params->getFileName(), $params->getImage()))
        {
            return false;
        }

        return true;
    }

    protected function _initPng(&$params)
    {
        $image = str_replace(" ", "+", $params->getPngData());
        $params->setImage(
            base64_decode(substr($image, strpos($image, ",")))
        );
        return true;
    }

    /**
     * @param   integer $productId
     * @param   string $optionId
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

        return self::RESULT_CODE_SUCCESS;
    }

    /**
     * @param   integer $productId
     * @param   string $reservedImgId
     * @param   string $pngData
     *
     * @return integer
     */
    protected function _writeImage($productId, $reservedImgId, $pngData)
    {
        $params = new Varien_Object();

        $params->setData(array(
            'product_id'    => $productId,
            'reserved_image_id' => $reservedImgId,
            'png_data'  => $pngData
        ));

        $this->_initPath($params);

        try {
            $this->_initPng($params);
        }
        catch (Exception $e)
        {
            return false;
        }

        if (!$this->_write($params))
        {
            return false;
        }

        if (!$this->_writeAdditionalImages($params))
        {
            return false;
        }

        return true;
    }

    protected function _initPath(&$params)
    {
        $folderName = Mage::getBaseDir('media') . DS . $this->_moduleFolderPath . $this->_quoteFolderName;
        $fileName = $folderName . DS . $params->getReservedImageId() . '.' . Aitoc_Aitcg_Model_Image::FORMAT_PNG;

        $params
            ->setFolderName($folderName)
            ->setFileName($fileName);
    }

    /**
     * @param   Varien_Object $params
     *
     * @return bool
     */
    protected function _writeAdditionalImages(Varien_Object $params)
    {
        return true;
    }

    public function copyImage($old, $new)
    {
        if(!file_exists($new)){
            if(!file_exists(dirname($new))){
                mkdir(dirname($new), 0777, true);
            }
            $this->resize($old, $new, 75);
        }
    }

}
