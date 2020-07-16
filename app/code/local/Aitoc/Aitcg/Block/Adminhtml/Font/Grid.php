<?php
     
    class Aitoc_Aitcg_Block_Adminhtml_Font_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
        public function __construct()
        {
            parent::__construct();
            $this->setId('fontGrid');
            // This is the primary key of the database
            $this->setDefaultSort('font_id');
            $this->setDefaultDir('ASC');
            //$this->setSaveParametersInSession(true);
        }
     
        protected function _prepareCollection()
        {
            $collection = Mage::getModel('aitcg/font')->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
     
        protected function _prepareColumns()
        {
            $this->addColumn('font_id', array(
                'header'    => Mage::helper('aitcg')->__('ID'),
                'align'     =>'right',
                'width'     => '10px',
                'index'     => 'font_id',
            ));
            
            $this->addColumn('name', array(
                'header'    => Mage::helper('aitcg')->__('Font Name'),
                'align'     =>'left',
                'width'     => '70%',
                'index'     => 'name',
            ));            
            
            
            $this->addColumn('filename', array(
                'header'    => Mage::helper('aitcg')->__('File Name'),
                'align'     =>'left',
                'width'     => '300px',
                'index'     => 'filename',
            ));   

            
            $this->addColumn('status', array(

                'header'    => Mage::helper('aitcg')->__('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => array(
                    1 => 'Active',
                    0 => 'Inactive',
                ),
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
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
        ));
            
            
            return parent::_prepareColumns();
        }
        
        
        protected function _prepareMassaction()
        {
            $this->setMassactionIdField('font_id');
            $this->getMassactionBlock()->setFormFieldName('font');

            $this->getMassactionBlock()->addItem('delete', array(
                 'label'=> Mage::helper('aitcg')->__('Delete'),
                 'url'  => $this->getUrl('*/*/massDelete'),
                 'confirm' => Mage::helper('aitcg')->__('Are you sure?')
            ));

            $statuses = array(
                    1 => 'Active',
                    0 => 'Inactive',
                );

            array_unshift($statuses, array('label'=>'', 'value'=>''));
            $this->getMassactionBlock()->addItem('status', array(
                 'label'=> Mage::helper('aitcg')->__('Change status'),
                 'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                 'additional' => array(
                        'visibility' => array(
                             'name' => 'status',
                             'type' => 'select',
                             'class' => 'required-entry',
                             'label' => Mage::helper('catalog')->__('Status'),
                             'values' => $statuses
                         )
                 )
            ));

            return $this;
        }        
     
        public function getRowUrl($row)
        {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        }
        
        

     
     
    }