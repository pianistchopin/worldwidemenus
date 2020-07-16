<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


class Amasty_Geoip_Model_Import extends Mage_Core_Model_Abstract
{
    protected static $_sessionKey = 'am_geoip_import_process_%key%';

    protected $_rowsPerTransaction = 200;

    protected $_rowsPerCsvTransaction = 10000;

    protected $_isCacheEnabled;

    protected $_tablePrefix;

    protected $_geoipRequiredFiles = array(
        'block'    => 'amasty_geiop_block.sql',
        'location' => 'amasty_geiop_location.sql'
    );

    protected $_geoipCsvFiles = array(
        'block'    => 'GeoLite2-City-Blocks-IPv4.csv',
        'location' => 'GeoLite2-City-Locations-en.csv'
    );

    protected $_modelsCols = array(
        'block'    => array(
            'start_ip_num', 'end_ip_num', 'geoip_loc_id', 'postal_code', 'latitude', 'longitude'
        ),
        'location' => array(
            'geoip_loc_id', 'country', 'city', 'region'
        )
    );

    public function getRequiredFiles()
    {
        return $this->_geoipRequiredFiles;
    }

    public function filesAvailable()
    {
        $ret = TRUE;

        $varDir = Mage::getBaseDir('var');
        $dir = $varDir . DS . 'amasty' . DS . 'geoip';

        foreach ($this->_geoipCsvFiles as $file) {
            if (!file_exists($dir . DS . $file)) {
                $ret = FALSE;
                break;
            }
        }

        return $ret;
    }

    public function isFileExist($filePath)
    {
        if (file_exists($filePath)) {
            return true;
        }
        return false;
    }

    public function getFilePath($type, $action)
    {
        $dir = $this->getDirPath($action);
        $file = $dir . DS . $this->_geoipRequiredFiles[$type];
        return $file;
    }

    public function getCsvFilePath($type, $action)
    {
        $dir = $this->getDirPath($action);
        $file = $dir . DS . $this->_geoipCsvFiles[$type];
        return $file;
    }

    public function getDirPath($action)
    {
        $varDir = Mage::getBaseDir('var');
        if ($action == 'download_and_import') {
            $dir = $varDir . DS . 'amasty' . DS . 'geoip' . DS . 'amasty_files';
        } else {
            $dir = $varDir . DS . 'amasty' . DS . 'geoip';
        }
        return $dir;
    }

    function startProcess($table, $filePath, $ignoredLines = 0, $action)
    {
        $importProcess = array(
            'position'    => 0,
            'rows_count'  => $this->_getRowsCount($filePath),
            'current_row' => 0
        );

        if ($action == 'import') {
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $query = 'SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE \'%amasty_geoip_'. $table .'_%\'';
            $columns = $write->fetchCol($query);
            $oldTemporary = implode(', ', $columns);
            if (!empty($oldTemporary)) {
                $delete = "DROP TABLE IF EXISTS $oldTemporary";
                $write->query($delete);
            }

            if (($handle = fopen($filePath, "r")) !== FALSE) {
                $tmpTableName = $this->_prepareImport($table);

                while ($ignoredLines > 0 && ($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                    $ignoredLines--;
                }

                $importProcess['position'] = ftell($handle);
                $importProcess['tmp_table'] = $tmpTableName;
            }

            $importProcess['rows_count'] = $importProcess['rows_count'] - $ignoredLines;
        }

        $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
        if ($sessionSaveMethod == 'files') {
            Mage::getSingleton('core/session')->setData(self::getSessionKey($table), $importProcess);
        } else {
            $this->_saveInDb($table, $importProcess);
        }

        $this->_truncateTables();

        return $importProcess;
    }

    function doProcess($table, $filePath, $action)
    {
        $ret = array();
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
            if ($sessionSaveMethod == 'files') {
                $importProcess = Mage::getSingleton('core/session')->getData(self::getSessionKey($table));
            } else {
                $importProcess = $this->_getFromDb($table);
            }
            /** @var Magento_Db_Adapter_Pdo_Mysql $write */
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            if ($importProcess) {
                try {
                    $position = $importProcess['position'];
                    fseek($handle, $position);
                    $transactionIterator = 0;
                    $write->beginTransaction();

                    $tmpTableName = isset($importProcess['tmp_table']) ? $importProcess['tmp_table'] : '';
                    if ($action == 'import') {
                        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                            $this->_importItem($table, $tmpTableName, $data, $action);
                            $transactionIterator++;
                            if ($transactionIterator >= $this->_rowsPerCsvTransaction) {
                                break;
                            }
                        }
                    } else {
                        $this->_tablePrefix = Mage::getConfig()->getTablePrefix();
                        while (($data = fgets($handle)) !== FALSE) {
                            $this->_importItem($table, $tmpTableName, $data, $action);
                            $transactionIterator++;
                            if ($transactionIterator >= $this->_rowsPerTransaction) {
                                break;
                            }
                        }
                    }

                    $write->commit();
                    if ($this->_rowsPerTransaction > $importProcess['rows_count']) {
                        $importProcess['current_row'] = $importProcess['rows_count'];
                    }
                    $importProcess['current_row'] += $transactionIterator;
                    $importProcess['position'] = ftell($handle);
                    $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
                    if ($sessionSaveMethod == 'files') {
                        Mage::getSingleton('core/session')->setData(self::getSessionKey($table), $importProcess);
                    } else {
                        $this->_saveInDb($table, $importProcess);
                    }
                } catch (Exception $e) {
                    $write->rollback();
                    if ($action == 'import') {
                        $this->_destroyImport($table, $tmpTableName);
                    }
                    throw new Exception($e->getMessage());
                }
            } else
                throw new Exception('run start before');
        }

        return $importProcess;
    }

    function commitProcess($table, $isDownload = false)
    {
        $ret = FALSE;
        $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
        if ($sessionSaveMethod == 'files') {
            $importProcess = Mage::getSingleton('core/session')->getData(self::getSessionKey($table));
        } else {
            $importProcess = $this->_getFromDb($table);
        }
        if ($importProcess) {
            if (!$isDownload) {
                $tmpTableName = $importProcess['tmp_table'];
            }

            if ($isDownload) {
                $configDate = 'date_download';
            } else {
                $configDate = 'date';
            }

            try {
                Mage::app()->getConfig()
                    ->saveConfig('amgeoip/import/' . $table, 1)
                    ->saveConfig('amgeoip/import/' . $configDate, Mage::getModel('core/date')->gmtDate())
                    ->reinit()
                ;//clean cache
                if (!$isDownload) {
                    $this->_doneImport($table, $tmpTableName);
                }

            } catch (Exception $e) {
                if (!$isDownload) {
                    $this->_doneImport($table, $tmpTableName);
                }
                throw new Exception($e->getMessage());
            }

            if (!$isDownload) {
                $this->_doneImport($table, $tmpTableName);
            }

            $ret = TRUE;
        } else
            throw new Exception('run start before');

        return $ret;
    }

    function isDone()
    {
        return (Mage::getStoreConfig('amgeoip/import/location') && Mage::getStoreConfig('amgeoip/import/block'));
    }

    public function resetDone()
    {
        Mage::getConfig()->saveConfig('amgeoip/import/block', 0);
        Mage::getConfig()->saveConfig('amgeoip/import/location', 0);
    }

    static function getSessionKey($table)
    {
        return strtr(self::$_sessionKey, array(
            '%key%' => $table
        ));
    }

    protected function _truncateTables()
    {
        $tableLocation = Mage::getSingleton('core/resource')->getTableName('amgeoip/location');
        Mage::getSingleton('core/resource')
            ->getConnection('core_write')
            ->truncate($tableLocation);
        $tableBlock = Mage::getSingleton('core/resource')->getTableName('amgeoip/block');
        Mage::getSingleton('core/resource')
            ->getConnection('core_write')
            ->truncate($tableBlock);
    }

    protected function _getRowsCount($filePath)
    {
        $linecount = 0;
        $handle = fopen($filePath, "r");
        while(!feof($handle)){
            $line = fgets($handle);
            $linecount++;
        }
        return $linecount;
    }

    protected function _importItem($table, $tmpTableName, &$data, $action)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $query = $data;

        if ($action == 'import') {
            if ($table == 'block' && is_array($data) && isset($data[0])) {
                list($ip, $mask) = explode('/', $data[0]);
                $ip2long = ip2long($ip);
                $min = ($ip2long >> (32 - $mask))  << (32 - $mask);
                $max = $ip2long | ~(-1 << (32 - $mask));
                $newData = array($min, $max, $data[1], $data[6], $data[7], $data[8]);
                $data = $newData;
            } elseif($table == 'location' && is_array($data)) {
                $newData = array($data[0], $data[4], $data[10], $data[7]);
                $data = $newData;
            }

            $query = $query = 'insert into `' . $tmpTableName . '`' .
                '(`' . implode('`, `', $this->_modelsCols[$table]) . '`) VALUES ' .
                '(?)';

            $query = $write->quoteInto($query, $data);
        } elseif ($this->_tablePrefix) { //fix for tables with prefixes
            $query = "INSERT INTO `" . $this->_tablePrefix . substr($query, 13);
        }

        $write->query($query);
    }

    protected function _prepareImport($table)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $targetTable = Mage::getSingleton('core/resource')
            ->getTableName('amgeoip/' . $table)
        ;

        $tmpTableName = uniqid($targetTable . '_');

        $query = 'create table ' . $tmpTableName . ' like ' . $targetTable;
        $write->query($query);

        $query = 'alter table ' . $tmpTableName . ' engine innodb';
        $write->query($query);

        return $tmpTableName;
    }

    protected function _doneImport($table, $tmpTableName)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $targetTable = Mage::getSingleton('core/resource')
            ->getTableName('amgeoip/' . $table)
        ;

        $query = 'delete from ' . $targetTable;
        $write->query($query);

        $query = 'insert into ' . $targetTable . ' select * from ' . $tmpTableName;
        $write->query($query);

    }

    protected function _destroyImport($table, $tmpTableName)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $query = 'DROP TABLE IF EXISTS ' . $tmpTableName;
        $write->query($query);

        $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
        if ($sessionSaveMethod == 'files') {
            Mage::getSingleton('core/session')->setData(self::getSessionKey($table), NULL);
        } else {
            $this->_clearDb();
        }
    }

    protected function _saveInDb($table, $importProcess)
    {
        if ($this->_isCacheEnabled()) {
            Mage::app()->getCacheInstance()->cleanType('config');
        }
        Mage::getModel('core/config')->saveConfig('amgeoip/import/position' . $table, $importProcess['position']);
        if (isset($importProcess['tmp_table'])) {
            Mage::getModel('core/config')->saveConfig('amgeoip/import/tmp_table' . $table, $importProcess['tmp_table']);
        }
        Mage::getModel('core/config')->saveConfig('amgeoip/import/rows_count' . $table, $importProcess['rows_count']);
        Mage::getModel('core/config')->saveConfig('amgeoip/import/current_row' . $table, $importProcess['current_row']);
    }

    protected function _getFromDb($table)
    {
        if ($this->_isCacheEnabled()) {
            Mage::app()->getCacheInstance()->cleanType('config');
        }
        $importProcess = NULL;
        $importProcess['position'] = Mage::getStoreConfig('amgeoip/import/position' . $table);
        $importProcess['tmp_table'] = Mage::getStoreConfig('amgeoip/import/tmp_table' . $table);
        $importProcess['rows_count'] = Mage::getStoreConfig('amgeoip/import/rows_count' . $table);
        $importProcess['current_row'] = Mage::getStoreConfig('amgeoip/import/current_row' . $table);
        return $importProcess;
    }

    protected function _clearDb()
    {
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/position/location');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/position/block');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/tmp_table/location');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/tmp_table/block');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/rows_count/location');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/rows_count/block');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/current_row/location');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/current_row/block');
    }

    protected function _isCacheEnabled()
    {
        if (empty($this->_isCacheEnabled)) {
            $this->_isCacheEnabled = Mage::app()->useCache('config');
        }

        return $this->_isCacheEnabled;
    }
}
