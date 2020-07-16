<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Model_Mysql4_Product_Option_Value_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Option_Value_Collection {
    public function addPriceToResult($storeId) {
        $this->getSelect()
            ->joinLeft(array('default_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`default_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`default_value_price`.store_id=?',0),
                array('default_option_type_price_id'=>'option_type_price_id', 'default_price'=>'price', 'default_price_type'=>'price_type'))
            ->joinLeft(array('store_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`store_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_price`.store_id=?', $storeId),
                array(
                'store_option_type_price_id'=>'option_type_price_id',
                'store_price'=>'price',
                'store_price_type'=>'price_type',
                'option_type_price_id'=>new Zend_Db_Expr('IFNULL(`store_value_price`.option_type_price_id,`default_value_price`.option_type_price_id)'),
                'price'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price,`default_value_price`.price)'),
                'price_type'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price_type,`default_value_price`.price_type)')
                )
            );
        return $this;
    }
    
    public function addDescriptionToResult($storeId) {
        $this->getSelect()
            ->joinLeft(array('default_value_description'=>$this->getTable('mageworx_customoptions/option_type_description')),
                '`default_value_description`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`default_value_description`.store_id = ?', 0),
                array('default_description'=>'description'))
            ->joinLeft(array('store_value_description'=>$this->getTable('mageworx_customoptions/option_type_description')),
                '`store_value_description`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_description`.store_id=?', $storeId),
                array('store_description'=>'store_value_description.description','description'=>new Zend_Db_Expr('IFNULL(`store_value_description`.description,`default_value_description`.description)')));
        return $this;
    }
    
}
