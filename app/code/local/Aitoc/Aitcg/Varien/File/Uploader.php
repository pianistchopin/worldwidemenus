<?php
class Aitoc_Aitcg_Varien_File_Uploader extends Varien_File_Uploader
{
    public function getUploadedFileName() {
        return parent::getCorrectFileName($this->_file['name']);
    }
    
    public function validate() {
        return $this->_validateFile();
    }
    
    public function getUploadImageSize() {
        return getimagesize($this->_file['tmp_name']);
    }    
}    