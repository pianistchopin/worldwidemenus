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
 * Class Ophirah_Qquoteadv_Adminhtml_Quote_ExportController
 */
class Ophirah_Qquoteadv_Adminhtml_Quote_ExportController extends Mage_Adminhtml_Controller_Action
{
    /** @const EXPORT_FOLDER_PATH path where to save the exported reports (temporarily) */
    const EXPORT_FOLDER_PATH = '/var/qquoteadv_export/';

    /**
     * exportSalesrepCsvAction
     */
    public function exportSalesrepCsvAction()
    {
        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportCsv_before', array());

        $fileName = 'salesrep.csv';
        $content = $this->getLayout()
            ->createBlock('qquoteadv/adminhtml_report_salesrep_salesrep_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);

        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportCsv_after', array());
    }

    /**
     * exportSalesrepExcelAction
     */
    public function exportSalesrepExcelAction()
    {
        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportXml_before', array());

        $fileName = 'salesrep.xml';
        $content = $this->getLayout()
            ->createBlock('qquoteadv/adminhtml_report_salesrep_salesrep_grid')
            ->getExcelFile($fileName);
        $this->_prepareDownloadResponse($fileName, $content);

        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportXml_after', array());
    }

    /**
     * Export CSV action (used in the grid)
     */
    public function csvAction()
    {
        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportCsv_before', array());

        $fileName = 'qquote.csv';
        $content = $this->getLayout()
            ->createBlock('qquoteadv/adminhtml_qquoteadv_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);

        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportCsv_after', array());
    }

    /**
     * Action that generates the quote grid as Excel
     */
    public function excelAction()
    {
        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportXml_before', array());

        $fileName = 'qquote.xml';
        $content = $this->getLayout()
            ->createBlock('qquoteadv/adminhtml_qquoteadv_grid')
            ->getExcelFile($fileName);
        $this->_prepareDownloadResponse($fileName, $content);

        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportXml_after', array());
    }

    /**
     * Action that generates the quote grid as XML
     */
    public function xmlAction()
    {
        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportXml_before', array());

        $fileName = 'qquote.xml';
        $content = $this->getLayout()
            ->createBlock('qquoteadv/adminhtml_qquoteadv_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);

        Mage::dispatchEvent('ophirah_qquoteadv_admin_exportXml_after', array());
    }

    /**
     * Export selected quotes as csv
     */
    public function indexAction()
    {
        $quoteIds = $this->getRequest()->getParam('qquote');
        Mage::dispatchEvent('ophirah_qquoteadv_admin_export_before', array($quoteIds));

        if (!is_array($quoteIds) || empty($quoteIds)) {
            $this->_redirectErr($this->__('No quotes selected to export'));
            return;
        }

        //make the filename
        $filename = "cart2quoteExport_" . date("ymdHis") . ".csv";

        //check license
        if (!Mage::helper('qquoteadv/license')->validLicense('export', null, true)) {
            $this->_redirectErr(
                $this->__(
                    "The CSV export function is only available in Cart2Quote Enterprise. To update please go to
                    <a href='https://www.cart2quote.com/pricing-magento-quotation-module.html?utm_source=Customer%2Bwebsite&utm_medium=license%2Bpopup&utm_campaign=Upgrade%2Bversion'>
                    https://www.cart2quote.com</a>"
                )
            );
            return;
        }

        //get the folder
        $folder = Mage::getBaseDir() . self::EXPORT_FOLDER_PATH;

        //check the folder exists or create it
        if (!file_exists($folder)) {
            try {
                mkdir($folder);
            } catch (Exception $e) {
                Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
                $this->_redirectErr($this->__('Could not create cart2quote export folder: ') . $folder);
                return;
            }
        } else {
            if (!is_writable($folder)) {
                $this->_redirectErr($this->__('The cart2quote export folder is not writable: ') . $folder);
                return;
            }
        }

        //set filepath
        $filepath = $folder . $filename;
        /* @var Ophirah_Qquoteadv_Model_Mysql4_Qqadvcustomer_Collection $collection */
        $collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
            ->addFieldToFilter('quote_id', array('IN', $quoteIds));

        //export quotes to file
        try {
            Mage::getSingleton('qquoteadv/qqadvcustomer')->exportQuotes($collection, $filepath);
        } catch (Exception $e) {
            Mage::log('Exception: ' . $e->getMessage(), null, 'c2q_exception.log', true);
            $this->_redirectErr($this->__('Could not export quotes'));
            return;
        }

        $contents = file_get_contents($filepath);
        $this->_prepareDownloadResponse($filename, $contents);

        Mage::dispatchEvent('ophirah_qquoteadv_admin_export_after', array($quoteIds));
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $aclResource = 'sales/qquoteadv';
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }
}
