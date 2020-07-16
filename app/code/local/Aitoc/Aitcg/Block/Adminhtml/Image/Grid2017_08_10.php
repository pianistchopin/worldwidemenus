<?php
     
    class Aitoc_Aitcg_Block_Adminhtml_Image_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
        public function __construct()
        {
            parent::__construct();
            $this->setId('imageGrid');
            // This is the primary key of the database
            $this->setDefaultSort('category_image_id');
            $this->setDefaultDir('ASC');
            //$this->setSaveParametersInSession(true);
        }
     
        protected function _prepareCollection()
        {
            $collection =Mage::getModel('aitcg/category_image')->getCollection()->addFieldToFilter('category_id',Mage::app()->getRequest()->getParam('id'));
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
     
        protected function _prepareColumns()
        {
            $this->addColumn('category_image_id', array(
                'header'    => Mage::helper('aitcg')->__('ID'),
                'align'     =>'right',
                'width'     => '10px',
                'index'     => 'category_image_id',
            ));
            
            $this->addColumn('name', array(
                'header'    => Mage::helper('aitcg')->__('Image Name'),
                'align'     =>'left',
                'width'     => '300px',
                'index'     => 'name',
            ));            
            
            
            $this->addColumn('filename', array(
                'header'    => Mage::helper('aitcg')->__('File Name'),
                'align'     =>'left',
                'width'     => '70%',
                'index'     => 'filename',
            ));   

            $this->addColumn('image', array(
                    'header'    => Mage::helper('aitcg')->__('Image'),
                    'align'     =>'center',
                    'index'     => 'filename',
                    'width'     => '100px',
                    'renderer'  => 'aitcg/adminhtml_image_renderer_image'
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
                            'params' => array('id' => Mage::app()->getRequest()->getParam('id'))
                        ) ,
                        'field'   => 'imgid'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
        ));
            
            
            return parent::_prepareColumns();
        }
        
        
        protected function _prepareMassaction()
        {
            $this->setMassactionIdField('category_image_id');
            $this->getMassactionBlock()->setFormFieldName('image');

            $this->getMassactionBlock()->addItem('delete', array(
                 'label'=> Mage::helper('aitcg')->__('Delete'),
                 'url'  => $this->getUrl('*/*/massDelete',array('id' => Mage::app()->getRequest()->getParam('id'))),
                 'confirm' => Mage::helper('aitcg')->__('Are you sure?')
            ));

            return $this;
        }        
     
        public function getRowUrl($row)
        {
            return $this->getUrl('*/*/edit', array('id' => Mage::app()->getRequest()->getParam('id'),'imgid' => $row->getId()));
        }
        
    }