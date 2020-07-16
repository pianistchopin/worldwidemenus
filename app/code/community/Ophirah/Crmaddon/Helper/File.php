<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Crmaddon_Helper_File
 */
class Ophirah_Crmaddon_Helper_File extends Mage_Core_Helper_Abstract
{
    /**
     * Function to get the MimeType if a file
     * This function is deprecated so there is a fallback in case it gets removed
     *
     * @param $filename
     * @return mixed|string
     */
    public function getMimeType($filename){
        if(!function_exists('mime_content_type')) {
            $mime_types = array(
                //text based and common files
                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',

                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',

                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',

                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',
                'mp4' => 'video/mp4',

                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',

                // ms office
                'doc' => 'application/msword',
                'docx' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',

                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            );

            $ext = strtolower(array_pop(explode('.',$filename)));
            if (array_key_exists($ext, $mime_types)) {
                return $mime_types[$ext];
            }
            elseif (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME);
                $mimetype = finfo_file($finfo, $filename);
                finfo_close($finfo);
                return $mimetype;
            }
            else {
                return 'application/octet-stream';
            }

        } else {
            return mime_content_type($filename);
        }
    }

    /**
     * Function that shows and logs the file errors based on an error number
     *
     * @param $errorNumber
     */
    public function getPhpFileErrorMessage($errorNumber){
        switch ($errorNumber) {
            case 0:
                // No Error
                Break;
            case 1:
                Mage::getSingleton('adminhtml/session')->addError($this->__("Upload error 1: Max upload size exceeded."));
                $message = "Upload error 1: Max upload size exceeded. Please increase your upload_max_filesize in your PHP.ini.";
                Mage::log('Message: CRMAddon: ' .$message, null, 'c2q.log', true);
                Break;
            case 2:
                Mage::getSingleton('adminhtml/session')->addError($this->__("Upload error 2: Max upload size exceeded."));
                $message = "Upload error 2: Max upload size exceeded.  Please increase your MAX_FILE_SIZE in your HTML form";
                Mage::log('Message: CRMAddon: ' .$message, null, 'c2q.log', true);
                Break;
            case 3:
                Mage::getSingleton('adminhtml/session')->addError($this->__("Upload error 3: File was partially uploaded, please retry."));
                $message = "Upload error 3: The uploaded file was only partially uploaded.";
                Mage::log('Message: CRMAddon: ' .$message, null, 'c2q.log', true);
                Break;
            case 4:
                // No file was uploaded
                //Mage::getSingleton('adminhtml/session')->addError($this->__("Upload error 4: No file was uploaded"));
                //$message = "Upload error 4: No file was uploaded";
                //Mage::log('Message: CRMAddon: ' .$message, null, 'c2q.log', true);
                Break;
            case 6:
                Mage::getSingleton('adminhtml/session')->addError($this->__("Upload error 6: Unable to upload file: missing temporary folder."));
                $message = "Upload error 6: Unable to upload file, missing temporary folder.";
                Mage::log('Message: CRMAddon: ' .$message, null, 'c2q.log', true);
                Break;
            case 7:
                Mage::getSingleton('adminhtml/session')->addError($this->__("Upload error 7: Unable to upload file: failed to write file to disk."));
                $message = "Upload error 7: Unable to upload file: failed to write file to disk.";
                Mage::log('Message: CRMAddon: ' .$message, null, 'c2q.log', true);
                Break;
            case 8:
                Mage::getSingleton('adminhtml/session')->addError($this->__("Upload error 8: Unable to upload file: Unknown error. More information is availible in Magento exception log."));
                $message = "A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.";
                Mage::log('Message: CRMAddon: ' .$message, null, 'c2q.log', true);
                Break;
            Default:
                Mage::getSingleton('adminhtml/session')->addError($this->__("Upload error: Unknown error"));
                $message = "An error number different then the default PHP FILES error message has been inserted in the function getPhpFilesErrorMessage in the file app/code/community/Ophirah/Qquoteadv/Helper/File.php";
                Mage::log('Message: CRMAddon: ' .$message, null, 'c2q.log', true);
                break;
        }
        return;
    }

    /**
     * Function that returns an array that maps the PHP post file upload object array
     *
     * @return array
     */
    public function getPhpFileMapping(){
        return array(
            'name',
            'type',
            'tmp_name',
            'error',
            'size'
        );
    }
}
