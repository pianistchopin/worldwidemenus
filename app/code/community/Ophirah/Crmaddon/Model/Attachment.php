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
 * Class Ophirah_Crmaddon_Model_Attachment
 */
class Ophirah_Crmaddon_Model_Attachment extends Mage_Core_Model_Abstract
{

    /**
     * Sets the required vars
     * Returns true if success
     * @param Ophirah_Crmaddon_Model_Crmaddonmessages $messageModel
     * @param null $file
     * @param null $key
     * @return Ophirah_Crmaddon_Model_Attachment
     */
    public function ini(Ophirah_Crmaddon_Model_Crmaddonmessages $messageModel, $file = null, $key = null){
        $this->setCrmAddonMessages($messageModel);
        if($file && $key){
            $this->setFile($file, $key);
        }
        return $this;
    }

    /**
     * Allows you to upload a file to media/qquoteadv/[quote id]/[file name]
     * You need to specify the file name and the form key of the FILE type.
     */
    public function uploadFile()
    {
        if(!$this->hasErrors()){
            $uploader = new Varien_File_Uploader($this->getKey());
            $uploader
                ->setAllowRenameFiles(true)
                ->setFilesDispersion(false)
                ->setAllowCreateFolders(true);

            try {
                $uploader->save($this->getPath(), $this->getName());
            } catch (Exception $e) {
                Mage::log('Exception: CRMAddon: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
    }

    /**
     * Sets file info to this object
     * @param $file
     * @param $key
     * @return bool
     */
    public function setFile($file, $key)
    {
        if (is_array($file) && isset($key)) {
            foreach (Mage::helper('crmaddon/file')->getPhpFileMapping() as $field) {
                if (array_key_exists($field, $file)) {
                    $this->setData($field, $file[$field]);
                } else {
                    return false;
                }
            }
            $this->setKey($key);
            $this->setIsFileSet(true);
        } else {
            return false;
        }
    }

    /**
     * Returns the a list of files in per crm message
     * @return array
     * @throws Exception
     */
    public function getAttachments(){
        $attachments = array();
        if($this->getCrmAddonMessages() && $this->getCrmAddonMessages()->getMessageId()){
            try{
                $file = new Varien_Io_File();
                //$file->open(array('path' =>$this->getPath(2)));
                $file->open(array('path' =>$this->getPath()));
                $attachments = $file->ls();
            }catch(Exception $e){
                Mage::log('Exception: CRMAddon: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            }
        }
        return $attachments;
    }

    /**
     * Removes a single file from the quote
     * @param $fileTitle
     * @return boolean
     */
    public function removeFile($fileTitle){
        $pathToFile = 'media/qquoteadv/'.$this->getId().'/'.$fileTitle;
        $fileRemoved = false;
        try {
            unlink($pathToFile);
            $fileRemoved = true;
        } catch (Exception $e) {
            Mage::log('Exception: CRMAddon: ' .$e->getMessage(), null, 'c2q_exception.log', true);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $fileRemoved;
    }

    /**
     * Returns an error if the required vars are not set or if there is an error in the file upload
     * @return bool
     */
    public function hasErrors(){
        $hasErrors = true;
        if($this->getIsFileSet() && $this->getCrmAddonMessages() && $this->getCrmAddonMessages()->getMessageId()){
            $error =  $this->getError();
            if($error > 0){
                Mage::helper('crmaddon/file')->getPhpFileErrorMessage($error);
            }else{
                $hasErrors = false;
            }
        }
        return $hasErrors;
    }

    /**
     * Returns the path based on the quoteId and messageId
     * @return string
     */
    public function getPath()
    {
        $path = Mage::getBaseDir('media')
            . DS . 'crmaddon'
            . DS . $this->getCrmAddonMessages()->getQuoteId()
            . DS . $this->getCrmAddonMessages()->getMessageId();
        return $path;
    }

    /**
     * Returns the file URL
     * @param $fileName
     * @return string
     */
    public function getAttachmentPath($fileName = '')
    {
        $path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, true)
            . 'crmaddon' . DS
            . $this->getCrmAddonMessages()->getQuoteId() . DS
            . $this->getCrmAddonMessages()->getMessageId() . DS
            . $fileName;
        return $path;
    }
}
