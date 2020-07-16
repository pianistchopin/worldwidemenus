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
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Quotedetails_Multiupload
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Quotedetails_Multiupload extends Ophirah_Qquoteadv_Block_Qquote_Abstract
{
    /**
     * Returns Cart2Quote quote
     *
     * @param bool|false $recollect
     * @return mixed
     */
    public function getQuote($recollect = false){
        return $this->getParentBlock()->getQuoteData($recollect);
    }

    /**
     * Returns Cart2Quote quote id
     *
     * @return mixed
     * @throws Exception
     */
    public function getQuoteId(){
        $quoteId = $this->getRequest()->getParam('id');
        if(!$quoteId){
            $quoteId = $this->getQuote()->getId();
        }

        return $quoteId;
    }

    /**
     * Returns HTML table of the files for this quote
     * @return string
     */
    public function getAllFilesToHtml()
    {
        $quoteId = $this->getQuoteId();
        $path = Mage::getModel('qquoteadv/qqadvcustomer')->getUploadDirPath($quoteId);
        $html = "";
        $fileIncrementNumber = 0;
        if (file_exists($path)) {
            $handle = opendir($path);
            if ($handle) {
                while (false !== ($entry = readdir($handle))) {
                    $file_parts = pathinfo($entry);
                    if (!empty($file_parts['extension'])) {
                        $html .= $this->singleFileToHtml(
                            $quoteId,
                            $file_parts['filename'],
                            $entry,
                            $fileIncrementNumber,
                            $file_parts['extension']
                        );
                        $fileIncrementNumber++;
                    }
                }
                closedir($handle);
            }
        }

        if ($fileIncrementNumber == 0) {
            $html = false;
        }

        return $html;
    }

    /**
     * Returns single HTML row with the file name + URL and a remove option
     * @param $quoteId
     * @param $fileName
     * @param $fileEntry
     * @param $incrementId
     * @param $extension
     * @return string
     */
    public function singleFileToHtml($quoteId, $fileName, $fileEntry, $incrementId, $extension){

        $html = '   <tr>
                    <td><a href="'.$this->getUploadUrl($quoteId, $fileEntry). '">' . $fileName . '</a></td>
                    <td><i>'.$extension.'</i></td>
                    <td><input type="checkbox" name="removeImage_'.$incrementId.'" value="'.$fileEntry.'"></td>
                    </tr>';
        return $html;
    }

    /**
     * Returns the URL of a single file
     *
     * @param $quoteId
     * @param $fileEntry
     * @return mixed
     */
    protected function getUploadUrl($quoteId, $fileEntry)
    {
        return Mage::getModel('qquoteadv/qqadvcustomer')
            ->getUploadPath(
                array(
                    'dir'  => $quoteId,
                    'file' => $fileEntry
                )
            );
    }

    /**
     * Renders the HTML if no file is available
     *
     * @deprecated since v5.3.1
     * @return string
     */
    protected function getNoFileHtml()
    {
        $html = '<tr><td>';
        $html .= "<i>" . $this->__('No Files are available.') . "</i>";
        $html .= '</td></tr>';
        return $html;
    }
}
