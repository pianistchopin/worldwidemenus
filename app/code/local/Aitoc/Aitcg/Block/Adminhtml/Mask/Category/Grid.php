<?php
     
    class Aitoc_Aitcg_Block_Adminhtml_Mask_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
        public function __construct()
        {
            parent::__construct();
            $this->setId('mask_categoryGrid');
            // This is the primary key of the database
            $this->setDefaultSort('id');
            $this->setDefaultDir('ASC');
            //$this->setSaveParametersInSession(true);
        }
     
        protected function _prepareCollection()
        {
            $collection = Mage::getModel('aitcg/mask_category')->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
     
        protected function _prepareColumns()
        {
            $this->addColumn('id', array(
                'header'    => Mage::helper('aitcg')->__('ID'),
                'align'     =>'right',
                'width'     => '15px',
                'index'     => 'id',
            ));
            
            $this->addColumn('name', array(
                'header'    => Mage::helper('aitcg')->__('Category Name'),
                'align'     =>'left',
                'width'     => '70%',
                'index'     => 'name',
            ));
            $this->addColumn('size', array(
                'header'    => Mage::helper('aitcg')->__('Size'),
                'align'     =>'left',
                'width'     => '20%',
                'index'     => 'name_suffix',
            ));
            
            $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('aitcg')->__('Edit'),
                        'url'     => array(
                        'base'=>'*/*/edit',
                        ),
                        'field'   => 'id'
                    ),
                  
                ),
                'filter'    => false,
                'sortable'  => false,
        ));
        
        
            $this->addColumn('action2',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('aitcg')->__('Masks'),
                        'url'     => array(
                        'base'=>'*/aitcg_mask_image/index',
                        ),
                        'field'   => 'id'
                    ) 
                  
                ),
                'filter'    => false,
                'sortable'  => false,
        ));        
            

             
            return parent::_prepareColumns();
        }
        
        
        protected function _prepareMassaction()
        {
            $this->setMassactionIdField('id');
            $this->getMassactionBlock()->setFormFieldName('category');

            $this->getMassactionBlock()->addItem('delete', array(
                 'label'=> Mage::helper('aitcg')->__('Delete'),
                 'url'  => $this->getUrl('*/*/massDelete'),
                 'confirm' => Mage::helper('aitcg')->__('Are you sure?')
            ));


            return $this;
        }        
     
        public function getRowUrl($row)
        {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        }
     
    }