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
 * Class Ophirah_Qquoteadv_Model_Entity_Increment_Numeric
 */
class Ophirah_Qquoteadv_Model_Entity_Increment_Numeric extends Mage_Eav_Model_Entity_Increment_Numeric
{
    CONST PARAM_STORE           = 'qquoteadv_quote_configuration/quote_number_format/increment_store';
    CONST PARAM_START_NUMBER    = 'qquoteadv_quote_configuration/quote_number_format/startnumber';
    CONST PARAM_PREFIX          = 'qquoteadv_quote_configuration/quote_number_format/prefix';
    CONST PARAM_INCREMENT       = 'qquoteadv_quote_configuration/quote_number_format/increment';
    CONST PARAM_PAD_LENGTH      = 'qquoteadv_quote_configuration/quote_number_format/pad_length';
    CONST PARAM_DEFAULT         = '_default';
    CONST PARAM_WEBSITE         = '_website';
    CONST QUOTE_TYPE_ID         = 888;

    /**
     * Construct function
     */
    public function __construct()
    {
        parent::__construct();

        $aEntityTypes = array(self::QUOTE_TYPE_ID => 'qquoteadv');
        $this->setData('entity_types', $aEntityTypes);
    }

    /**
     * Generates a new increment ID depending on the store config
     * @param null $storeId
     * @return string
     */
    protected function _generateNextId($storeId = null)
    {
        if($storeId == null){
            $storeId = Mage::app()->getStore()->getId();
        }
        $this->setStoreId($storeId);
        $aEntityTypes = $this->getData('entity_types');
        $entTypeId = self::QUOTE_TYPE_ID;

        if (isset($aEntityTypes[$entTypeId])) {
            //$this->setDataByConfig($this->getStoreId());
            $this->setDataByConfig();
            if ($this->getNextNumber()) {
                $this->saveStartNumberConfig();
            }
            return $this->format($this->getNextNumber());
        } else {
            return parent::getNextId();
        }
    }

    /**
     * Function that gets the next increment id for a given store
     *
     * @param null $storeId
     * @param bool|false $force
     * @return string
     */
    public function getNextId($storeId = null, $force = false)
    {
        if (Mage::helper('qquoteadv')->getTotalQty() > 0 || $force) {
            return $this->_generateNextId($storeId);
        } else {
            $last = $this->getLastId();

            if (strpos($last, $this->getPrefix()) === 0) {
                $last = (int)substr($last, strlen($this->getPrefix()));
            } else {
                $last = (int)$last;
            }

            $next = $last + 1;

            return $this->format($next);
        }
    }

    /**
     * Returns the configured pad length
     *
     * @return int
     */
    public function getPadLength()
    {
        $padLength = $this->getData('pad_length');
        if (empty($padLength)) {
            $padLength = 0;
        }

        return $padLength;
    }

    /**
     * Format a given id to a increment id
     *
     * @param $id
     * @return string
     */
    public function format($id)
    {
        $result = $this->getPrefix();
        $result .= str_pad((string)$id, $this->getPadLength(), $this->getPadChar(), STR_PAD_LEFT);

        return $result;
    }

    /**
     * Returns the next increment number
     * @return int
     */
    protected function getNextNumber(){
        return $this->getStartnumber() + $this->getIncrement();
    }

    /**
     * Sets PadLenth, Increment, Startnumber and Prefix by store config paths.
     */
    protected function setDataByConfig(){
        $this->setPadLength($this->getSpecificConfig(self::PARAM_PAD_LENGTH))
            ->setIncrement($this->getSpecificConfig(self::PARAM_INCREMENT))
            ->setStartnumber($this->getSpecificConfig(self::PARAM_START_NUMBER))
            ->setPrefix($this->getSpecificConfig(self::PARAM_PREFIX));
    }

    /**
     * Function that saves a/the new start number in the config
     */
    protected function saveStartNumberConfig(){
        $config = $this->getStoreConfigValues();
        Mage::app()->getConfig()
            ->saveConfig(
                self::PARAM_START_NUMBER.$config['path'],
                $this->getNextNumber(),
                $config['scope'],
                $config['id']
            )
            ->reinit();
    }

    /**
     * Function that collect the store config values
     *
     * @return array|string
     */
    protected function getStoreConfigValues(){
        $storeSpecified = Mage::getStoreConfig(self::PARAM_STORE, $this->getStoreId());
        switch($storeSpecified){
            case 0:
                $config = $this->getDefaultValues();
                break;
            case 1:
                $config = $this->getWebsiteValues();
                break;
            case 2:
                $config = $this->getStoreValues();
                break;
            default:
                $config = self::PARAM_DEFAULT;
                break;
        }
        return $config;
    }

    /**
     * Function that returns the default config settings
     *
     * @return array
     */
    protected function getDefaultValues(){

        return array(
            'scope' => 'default',
            'id' => 0,
            'path' => self::PARAM_DEFAULT,
            'scopeCode' => 0
        );
    }

    /**
     * Function that returns the website scope settings
     *
     * @return array
     */
    protected function getWebsiteValues(){
        $websiteId = Mage::getModel('core/store')->load($this->getStoreId())->getWebsiteId();
        $website = Mage::getModel('core/website')->load($websiteId);
        return array(
            'scope' => 'websites',
            'id' => (int)$this->getStoreId(),
            'path' => self::PARAM_WEBSITE,
            'scopeCode' => $website->getCode()
        );
    }

    /**
     * Function that returns the store scope settings
     *
     * @return array
     */
    protected function getStoreValues(){
        return array(
            'scope' => 'stores',
            'id' => (int)$this->getStoreId(),
            'path' => '',
            'scopeCode' => (int)$this->getStoreId()
        );
    }

    /**
     * Get specific config while not depending on the store
     * @param $type
     * @return mixed|null
     */
    protected function getSpecificConfig($type){
        $config = $this->getStoreConfigValues();
        $node = Mage::getConfig()->getNode($type.$config['path'], $config['scope'], $config['scopeCode']);
        if($node){
            $node = $node->asArray();
            if(is_array($node) && current($node)){
                return current($node);
            }
            return $node;
        }
        return null;
    }
}
