<?php

/**
 * MageWorx
 * CustomOptions Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_CustomOptions_Block_Adminhtml_Options_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();

        $this->setId('customoptionsOptionsGrid');
        $this->setDefaultSort('title');
        $this->setDefaultDir(Varien_Data_Collection::SORT_ORDER_ASC);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('mageworx_customoptions/group')->getCollection();
        $collection->addProductsCount()->setShellRequest();      
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function getStoreId() {
        return Mage::registry('store_id');
    }

    protected function _prepareColumns() {
        $helper = $this->_getHelper();
        $this->addColumn('title', array(
            'header' => $helper->__('Title'),
            'index' => 'title',
            'align' => 'left',
        ));
        
        $this->addColumn('products', array(
            'header' => $helper->__('Products'),
            'type' => 'number',
            'index' => 'products',
            'width' => 80
        ));

        $this->addColumn('updated_at', array(
            'header' => $helper->__('Last Modified Date'),
            'type' => 'datetime',
            'index' => 'updated_at',
            'width' => 160
        ));
        

        $this->addColumn('is_active', array(
            'header' => $helper->__('Status'),
            'width' => 80,
            'index' => 'is_active',
            'type' => 'options',
            'options' => $helper->getOptionStatusArray(),
            'align' => 'center'
        ));

        $this->addColumn('action', array(
            'header' => $helper->__('Action'),
            'width' => 100,
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $helper->__('Edit'),
                    'url' => array('base' => '*/*/edit', array('store' => $this->getStoreId())),
                    'field' => 'group_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
            'align' => 'center'
        ));

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection() {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _prepareMassaction()
    {
    	$helper = $this->_getHelper();
        $this->setMassactionIdField('group_id');
        $this->getMassactionBlock()->setFormFieldName('groups');

        $this->getMassactionBlock()->addItem('export', array(
             'label'      => $helper->__('Export'),
             'url'        => $this->getUrl('*/*/export', array('_current' => true))
        ));        
        
        $this->getMassactionBlock()->addItem('import', array(
             'label'      => $helper->__('Import'),
             'url'        => $this->getUrl('*/*/import', array('_current' => true)),
             'additional' => array(
	             'import_file' => array(
	                  'name'   => 'import_file',
	                  'type'   => 'file',
	                  'class'  => 'required-entry',
	              )                    
             )
        ));
        
        
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'   => $helper->__('Delete'),
             'url'     => $this->getUrl('*/*/massDelete', array('store' => $this->getStoreId())),
             'confirm' => $helper->__('If you delete this item(s) all the options inside will be deleted as well?')
        ));

        $statuses = $helper->getOptionStatusArray();
        array_unshift($statuses, array('label' => '', 'value' => ''));

        $this->getMassactionBlock()->addItem('status', array(
             'label'      => $helper->__('Change status'),
             'url'        => $this->getUrl('*/*/massStatus', array('_current' => true, 'store' => $this->getStoreId())),
             'additional' => array(
	             'visibility' => array(
	                  'name'   => 'is_active',
	                  'type'   => 'select',
	                  'class'  => 'required-entry',
	                  'label'  => $helper->__('Status'),
	                  'values' => $statuses
	              )
             )
        ));
        
        
        
        
        return $this;
    }

    protected function _getHelper()
    {
    	return Mage::helper('mageworx_customoptions');
    }

    public function getRowUrl($row)
    {
      return $this->getUrl('*/*/edit', array('group_id' => $row->getGroupId(), 'store' => $this->getStoreId()));
    }
}