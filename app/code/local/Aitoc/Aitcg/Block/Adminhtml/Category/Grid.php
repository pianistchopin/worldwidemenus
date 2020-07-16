<?php
     
    class Aitoc_Aitcg_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
        public function __construct()
        {
            parent::__construct();
            $this->setId('categoryGrid');
            // This is the primary key of the database
            $this->setDefaultSort('category_id');
            $this->setDefaultDir('ASC');
            //$this->setSaveParametersInSession(true);
        }
     
        protected function _prepareCollection()
        {
            $collection = Mage::getModel('aitcg/category')->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
     
        protected function _prepareColumns()
        {
            $this->addColumn('category_id', array(
                'header'    => Mage::helper('aitcg')->__('ID'),
                'align'     =>'right',
                'width'     => '10px',
                'index'     => 'category_id',
            ));
            
            $this->addColumn('name', array(
                'header'    => Mage::helper('aitcg')->__('Category Name'),
                'align'     =>'left',
                'width'     => '300px',
                'index'     => 'name',
            ));            
            
            
            $this->addColumn('description', array(
                'header'    => Mage::helper('aitcg')->__('Category Description'),
                'align'     =>'left',
                'width'     => '70%',
                'index'     => 'description',
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
                        'caption' => Mage::helper('aitcg')->__('Images'),
                        'url'     => array(
                        'base'=>'*/aitcg_image/index',
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
            $this->setMassactionIdField('category_id');
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