<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

class MT_Exitoffer_Model_Core_Email_Template
    extends Mage_Core_Model_Email_Template
{

    public function sendTransactional($templateId, $sender, $email, $name, $vars=array(), $storeId=null)
    {
        $this->addAttachment($templateId, $storeId);
        return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
    }

    protected function addAttachment($templateId, $storeId = null)
    {
        if ($templateId != Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_SUCCESS_EMAIL_TEMPLATE, $storeId)) {
            return false;
        }

        $fileWithPath = Mage::helper('exitoffer')->getTemplateAttachment($storeId);

        if (!$fileWithPath) {
            return false;
        }

        $content = file_get_contents($fileWithPath);
        $type = Mage::getStoreConfig('exitoffer/email/mime');
        $disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $encoding = Zend_Mime::ENCODING_BASE64;
        $fileNameTmp = explode('/', $fileWithPath);
        $fileName = $fileNameTmp[count($fileNameTmp)-1];

        switch (get_class($this->getMail())) {
            case 'Mandrill_Message':
                $this->getMail()->createAttachment(
                    $content,
                    $type,
                    $disposition,
                    $encoding,
                    $fileName
                );
                break;
            default:
                $attachment = new Zend_Mime_Part($content);
                $attachment->type = $type;
                $attachment->disposition = $disposition;
                $attachment->encoding = $encoding;
                $attachment->filename = $fileName;
                $this->getMail()->addAttachment($attachment);
                break;
        }

        return true;
    }
}