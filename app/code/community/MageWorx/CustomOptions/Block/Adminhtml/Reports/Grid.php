<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Block_Adminhtml_Reports_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();

        $this->setId('customoptionsReportsGrid');
        $this->setDefaultSort('product_id');
        $this->setDefaultDir(Varien_Data_Collection::SORT_ORDER_ASC);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    public function getRowUrl($row) {
        return '';
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
    
    protected function _prepareCollection() {
        $collection = Mage::getModel('mageworx_customoptions/stock')->getCollection();
        $collection->addOptionName();
        $collection->addValueName();
        $collection->addProductSku();
        $collection->addProductName();
        $collection->addOptionsQty();
        //echo $collection->getSelect(); exit;      
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function getStoreId() {
        return Mage::registry('store_id');
    }

    protected function _prepareColumns() {
        $helper = $this->_getHelper();
        $this->addColumn('product_id', array(
            'header' => $helper->__('Product Id'),
            'index' => 'product_id',
            'width' => '50px',
            'type'  => 'number',
        ));
        
        $this->addColumn('product_name', array(
            'header' => $helper->__('Product Name'),
            'index' => 'product_name',
            'renderer'  => 'MageWorx_CustomOptions_Block_Adminhtml_Reports_Renderer_ProductName',
        ));
        
        $this->addColumn('product_sku', array(
            'header' => $helper->__('Product Sku'),
            'index' => 'product_sku',
            'width' => '80px',
        ));
        
        $this->addColumn('option_name', array(
            'header' => $helper->__('Option Name'),
            'index' => 'option_name',
        ));
        
        $this->addColumn('value_name', array(
            'header' => $helper->__('Value Name'),
            'index' => 'value_name',
        ));
        
        $this->addColumn('qty', array(
            'header'=> Mage::helper('catalog')->__('Value Qty'),
            'width' => '100px',
            'type'  => 'number',
            'index' => 'qty',
            'renderer'  => 'MageWorx_CustomOptions_Block_Adminhtml_Reports_Renderer_Qty',
            'filter_condition_callback' => array($this, 'filterQtyConditionCallback')
        ));
        
        $this->addColumn('sku', array(
            'header' => $helper->__('Linked Sku'),
            'index' => 'sku',
            'width' => '80px',
            'renderer'  => 'MageWorx_CustomOptions_Block_Adminhtml_Reports_Renderer_LinkedSku',
        ));

        return parent::_prepareColumns();
    }

    protected function filterQtyConditionCallback($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        if (!empty($value['from'])) {
            $this->getCollection()->getSelect()->where(
                "IF(main_table.sku IS NULL OR main_table.sku='', main_table.customoptions_qty, csi.qty) >= '".intval($value['from'])."' OR IF(main_table.sku IS NULL OR main_table.sku='', main_table.customoptions_qty, csi.qty) IS NULL");
        }
        if (!empty($value['to'])) {
            $this->getCollection()->getSelect()->where(
                "IF(main_table.sku IS NULL OR main_table.sku='', main_table.customoptions_qty, csi.qty) <= '".intval($value['to'])."'");
        }

        return $this;
    }

    protected function _afterLoadCollection() {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _getHelper()
    {
    	return Mage::helper('mageworx_customoptions');
    }
    
    protected function _prepareMassaction()
    {
    	$helper = $this->_getHelper();
        $this->setMassactionIdField('report_id');
        $this->getMassactionBlock()->setFormFieldName('reports');

        $statuses = $helper->getOptionStatusArray();
        array_unshift($statuses, array('label' => '', 'value' => ''));

        $this->getMassactionBlock()->addItem('change_qty', array(
             'label'      => $helper->__('Change Quantity'),
             'url'        => $this->getUrl('*/*/massQty', array('_current' => true, 'store' => $this->getStoreId())),
             'additional' => array(
	             'visibility' => array(
	                  'name'   => 'customoption_qty',
	                  'type'   => 'text',
	                  'class'  => 'required-entry',
	                  'label'  => $helper->__('Qty'),
	              )
             )
        ));
        
        $this->getMassactionBlock()->addItem('disable_inventory', array(
             'label'=> $helper->__('Disable Inventory'),
             'url'  => $this->getUrl('*/*/massDisableInventory', array('_current' => true, 'store' => $this->getStoreId())),
        ));

        return $this;
    }
    
}