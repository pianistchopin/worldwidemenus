<?php
/**
 * Media Photogallery & Product Videos extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Mediaappearance
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 **/

class FME_Photogallery_Block_Adminhtml_Photogalleryblocks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('photogalleryblockGrid');
      $this->setDefaultSort('photogallery_block_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('photogallery/photogalleryblocks')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn(
          'photogallery_block_id', array(
          'header'    => Mage::helper('photogallery')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'photogallery_block_id',
          )
      );

      $this->addColumn(
          'block_title', array(
          'header'    => Mage::helper('photogallery')->__('Title'),
          'align'     =>'left',
          'index'     => 'block_title',
          )
      );
      
      $this->addColumn(
          'block_identifier', array(
          'header'    => Mage::helper('photogallery')->__('Identifier'),
          'align'     =>'left',
          'index'     => 'block_identifier',
          )
      );
      
      $this->addColumn(
          'block_status', array(
          'header'    => Mage::helper('photogallery')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'block_status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
          )
      );
      
        $this->addColumn(
            'action',
            array(
                'header'    =>  Mage::helper('photogallery')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('photogallery')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            )
        );
        
        $this->addExportType('*/*/exportCsv', Mage::helper('photogallery')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('photogallery')->__('XML'));
      
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('photogallery_block_id');
        $this->getMassactionBlock()->setFormFieldName('photogallery');

        $this->getMassactionBlock()->addItem(
            'delete', array(
             'label'    => Mage::helper('photogallery')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('photogallery')->__('Are you sure?')
            )
        );

        $statuses = Mage::getSingleton('photogallery/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem(
            'status', array(
             'label'=> Mage::helper('photogallery')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('photogallery')->__('Status'),
                         'values' => $statuses
                     )
             )
            )
        );
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
