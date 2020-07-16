<?php

class Aitoc_Aitcg_Block_Adminhtml_Font_Family_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('fontFamilyGrid');
        // This is the primary key of the database
        $this->setDefaultSort('font_family_id');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aitcg/font_family')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'font_family_id', array(
                'header' => Mage::helper('aitcg')->__('ID'),
                'align'  => 'right',
                'width'  => '10px',
                'index'  => 'font_family_id',
            )
        );

        $this->addColumn(
            'title', array(
                'header' => Mage::helper('aitcg')->__('Font Family Name'),
                'align'  => 'left',
                'width'  => '90%',
                'index'  => 'title',
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'   => Mage::helper('catalog')->__('Action'),
                'width'    => '50px',
                'type'     => 'action',
                'getter'   => 'getId',
                'actions'  => array(
                    array(
                        'caption' => Mage::helper('aitcg')->__('Edit'),
                        'url'     => array(
                            'base' => '*/*/edit',
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'   => false,
                'sortable' => false,
            )
        );
        
        return parent::_prepareColumns();
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('font_family_id');
        $this->getMassactionBlock()->setFormFieldName('font');

        $this->getMassactionBlock()->addItem(
            'delete', array(
                'label'   => Mage::helper('aitcg')->__('Delete'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('aitcg')->__('Are you sure?')
            )
        );

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
