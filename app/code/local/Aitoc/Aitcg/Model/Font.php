<?php

class Aitoc_Aitcg_Model_Font extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitcg/font');
    }

    public function getFontsPath()
    {
        return Mage::getBaseDir('media') . DS . 'custom_product_preview' . DS . 'fonts' . DS;
    }

    public function setFilename($filename)
    {
        if ($this->getFilename() && $this->getFilename() != $filename) {
            $fullPath = $this->getFontsPath() . $this->getFilename();
            unlink($fullPath);
        }
        $this->setData('filename', $filename);
    }

    public static function uploadZip($path, $nameOfParam, $arrayOfExt)
    {
        //load zip to temp catalog
        $tempPath = $path . 'temp' . DS;

        $uploader = new Varien_File_Uploader($nameOfParam);
        $uploader->setAllowedExtensions($arrayOfExt);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $uploader->save($tempPath, preg_replace('/[^A-Za-z\d\.]/', '_', $_FILES['filename']['name']));

        $fullPath     = $path . $uploader->getUploadedFileName();
        $fullTempPath = $tempPath . $uploader->getUploadedFileName();

        $filesNamesArray = array();

        //unzip
        $zip = new ZipArchive();

        if ($zip->open($fullTempPath) === true) {

            $zip->extractTo($tempPath);

            //copy ttf and tte file to font catalog and put in $filesNamesArray
            for ($i = 0; $i < $zip->numFiles; $i++) {

                $fontName = $zip->getNameIndex($i);

                if (strpos($fontName, '.ttf') || strpos($fontName, '.tte')) {

                    if (strpos($fontName, '-')) {
                        //put in array of names
                        $filesNamesArray[] = $fontName;

                        //copy to fonts folder
                        copy($tempPath . $fontName, $path . $fontName);
                    } else {
                        $name = explode(".", $uploader->getUploadedFileName());
                        $name = $name[0];

                        //put in array of names
                        $filesNamesArray[] = $name . '-' . $fontName;

                        //copy to fonts folder
                        copy($tempPath . $fontName, $path . $name . '-' . $fontName);
                    }
                }
            }

            $zip->close();

            self::deleteFolder($tempPath);
        }

        return $filesNamesArray;
    }

    public function deleteFolder($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                self::deleteFolder(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else {
            if (is_file($path) === true) {
                return unlink($path);
            }
        }

        return false;
    }
}
