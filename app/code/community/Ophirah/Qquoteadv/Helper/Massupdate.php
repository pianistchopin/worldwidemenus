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
 * Class Ophirah_Qquoteadv_Helper_Massupdate
 */
class Ophirah_Qquoteadv_Helper_Massupdate extends Mage_Core_Helper_Data {

    /** Maximum end position for splitting the product array */
    const MAX_END_POS           = 100;

    protected $_startPos        = 0;
    protected $_endPos          = false;
    protected $_tableEnd        = false;
    protected $_increment       = false;
    protected $_connection      = false;
    protected $_currentTable    = false;

    /**
     * Mass update products based on the type given.
     * @param $type
     * @param array $products
     * @param $newSettings
     * @throws Exception
     */
    public function update($type, array $products, $newSettings){
        if(in_array($type, $this->getMassUpdateTypes())){
            $this->setTable($type);
            if($this->hasTable() && $newSettings) {
                $this
                    ->resetStartPos()
                    ->setArrayEnd(count($products))
                    ->setIncrement()
                    ->setEndPos($this->getIncrement())
                    ->updateProducts($products, $newSettings);
            }
        }
    }

    /**
     * Update groups settings of the given products.
     * - delete existing settings
     * - insert new settings
     * @param array $products
     * @param $newSettings
     * @return $this
     */
    protected function updateProducts(array $products, $newSettings)
    {
        $newSettingsCollection = $this->prepareNewSettings($newSettings);
        while ($this->incrementingProducts() && count($newSettingsCollection)) {
            $chunkOfProducts = $this->getArrayPiece($products);

            $this->deleteCurrentSettings($chunkOfProducts, $newSettings);
            foreach ($newSettingsCollection as $newSetting) {
                $this->insertNewSettings($newSetting, $chunkOfProducts);
            }

            $this->incrementProducts();
        }
        return $this;
    }

    /**
     * Generates SQL for updating the products
     * @param $products
     * @param $tableData
     * @return string
     */
    protected function getInsertUpdateSql($products, $tableData) {
        $sql = "";
        $insertSql = "INSERT INTO `" . $this->_currentTable ."`";
        $insertSql .= "(website_id, all_groups, customer_group_id, value, entity_id)";
        $insertSql .= "VALUES";
        $insertSql .= "('" . $tableData['website_id'] . "', '" . $tableData['all_groups']  . "', '" . $tableData['customer_group_id']  . "', '" . $tableData['value']  . "', '";
        foreach($products as $productId) {
            $sql .= $insertSql . $productId . "');";
        }
        return $sql;
    }

    /**
     * Generates SQL for removing the records for the products that are going to be updated.
     * When mass-updating you always start from no groups.
     *
     * @param $products
     * @param $newSettings
     * @return string
     */
    protected function getCleanUpSql($products, $newSettings) {
        $removeProductData = "";
        $deleteSql = "DELETE FROM `" . $this->_currentTable . "` WHERE ( `website_id` = '" . $newSettings[0]['website_id'] . "' AND `entity_id` = '";
        foreach ($products as $key => $product) {
            $removeProductData .= $deleteSql . $product . "'); ";
        }
        return $removeProductData;
    }

    /**
     * Retrieves the connection.
     * @return bool
     */
    public function getConnection(){
        if($this->_connection == false){
            $this->setConnection();
        }
        return $this->_connection;
    }

    /**
     * Sets the connection class to this helper.
     */
    public function setConnection(){
        $this->_connection = $this->getResource()->getConnection('core_write');
    }

    /**
     * Retrieves the end position.
     * @param $endPos
     * @return $this
     */
    public function setEndPos($endPos){
        $this->_endPos = $endPos;
        return $this;
    }

    /**
     * Splits the array on the given start and end position.
     * @param $products
     * @return array
     */
    protected function getArrayPiece($products){
        return array_slice($products, $this->_startPos, $this->_endPos);
    }

    /**
     * Increments the array.
     * @return $this
     */
    protected function incrementProducts(){
        $this->_startPos += $this->getIncrement();
        $this->_endPos += $this->getIncrement();
        if($this->_endPos > $this->_tableEnd){
            $this->_endPos = $this->_tableEnd;
        }
        return $this;
    }

    /**
     * Recursive function for splitting the product array into equally divided chunks.
     * @param $productsCount
     * @return mixed
     */
    protected function getIncrementAmount($productsCount){
        if($productsCount > self::MAX_END_POS){
            return $this->getIncrementAmount((int)($productsCount/2));
        }else{
            return $productsCount;
        }
    }

    /**
     * Prepares the array keys to contain the correct data for mass updating.
     * @param array $tableData
     * @return array
     */
    protected function prepareNewSettings(array $tableData){
        $newTableData = array();
        foreach($tableData as $fieldData){
            if(isset($fieldData['cust_group']) && isset($fieldData['value']) && $fieldData['delete'] != "1"){
                $newTableData[] = array(
                    'website_id' => $fieldData['website_id'],
                    'all_groups' => 0,
                    'customer_group_id' => $fieldData['cust_group'],
                    'value' => $fieldData['value'],
                );
            }
        }
        return $newTableData;
    }

    /**
     * Retrieves the table by given groups type.
     * @param $type
     * @throws Exception
     */
    protected function setTable($type){
        if($type == Ophirah_Qquoteadv_Helper_Data::TYPE_QUOTE_ALLOW){
            $this->_currentTable = $this->getResource()->getTableName('qquoteadv/product_attribute_group_allow');
        }else{
            throw new Exception('Cannot get table for Cart2Quote groups');
        }
    }


    /**
     * Sets the last value of the product array
     * @param $count
     * @return $this
     */
    public function setArrayEnd($count)
    {
        $this->_tableEnd = $count;
        return $this;
    }

    /**
     * Retrieves an array of update types.
     * @return array
     */
    public function getMassUpdateTypes(){
        return array(
            Ophirah_Qquoteadv_Helper_Data::TYPE_QUOTE_ALLOW
        );
    }

    /**
     * Retrieves the post field for mass updating.
     * @param $updateType @see getMassUpdateTypes()
     * @return array
     */
    public function getMassPostTypes($updateType){
        $postFields =  array(
            Ophirah_Qquoteadv_Helper_Data::TYPE_QUOTE_ALLOW => 'allowquote'
        );
        if(array_key_exists($updateType, $postFields)){
            return $postFields[$updateType];
        }else{
            return false;
        }
    }

    /**
     * Checks if incrementing through the products array.
     * @return bool
     */
    protected function incrementingProducts()
    {
        if($this->_startPos >= $this->_tableEnd) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * Retrieve the resource model.
     * @return Mage_Core_Model_Resource
     */
    protected function getResource(){
        return Mage::getSingleton('core/resource');
    }

    /**
     * Removes the current settings of the product group options.
     * @param $chunkOfProducts
     * @param $newSettings
     */
    protected function deleteCurrentSettings($chunkOfProducts, $newSettings)
    {
        $this->getConnection()->query(
            $this->getCleanUpSql($chunkOfProducts, $newSettings)
        );
    }

    /**
     * @param $tableData
     * @param $chunkOfProducts
     */
    protected function insertNewSettings($tableData, $chunkOfProducts)
    {
        $this->getConnection()->query(
            $this->getInsertUpdateSql($chunkOfProducts, $tableData)
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function hasTable(){
        if($this->_currentTable){
            return true;
        }else{
            throw new Exception('Table for Cart2Quote groups is undefined');
        }
    }

    /**
     * Sets the incrementing number
     * @return $this
     */
    protected function setIncrement(){
        $this->_increment = $this->getIncrementAmount($this->_tableEnd);
        return $this;
    }

    /**
     * @return bool
     */
    protected function getIncrement(){
        return $this->_increment;
    }

    /**
     * @return $this
     */
    protected function resetStartPos()
    {
        $this->_startPos = 0;
        return $this;
    }

}
