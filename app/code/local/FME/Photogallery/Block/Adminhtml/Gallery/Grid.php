<?php
/**
 * Photo Photogallery & Products Photogallery extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Photogallery
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

/**
 * @category   Photogallery
 * @package    Photogallery
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 */
 
 
class FME_Photogallery_Block_Adminhtml_Gallery_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('galleryGrid');
      $this->setDefaultSort('photogallery_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $_conn = Mage::getSingleton('core/resource');
      $photogallery_images_table=$_conn->getTableName('photogallery_images');
      $collection = Mage::getModel('photogallery/gallery')->getCollection();
      
       $this->setCollection($collection);
       //exit;
       return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $this->addColumn(
        'gallery_id', array(
        'header'    => Mage::helper('photogallery')->__('ID'),
        'align'     =>'right',
        'width'     => '50px',
        'index'     => 'photogallery_id',
        )
    );
    
    
    $this->addColumn(
        'gallery_title', array(
        'header'    => Mage::helper('photogallery')->__('Album Name'),
        'align'     =>'left',
        'index'     => 'gallery_title',
        )
    );
    $this->addColumn(
        'gallery_identifier', array(
        'header'    => Mage::helper('photogallery')->__('Album  Identifier'),
        'align'     =>'left',
        'index'     => 'gallery_identifier',
        )
    );
    
    $this->addColumn(
        'gallery_description', array(
        'header'    => Mage::helper('photogallery')->__('Album Description'),
        'align'     =>'left',
        'index'     => 'gallery_description',
        )
    );
    
    $this->addColumn(
        'status', array(
        'header'    => Mage::helper('photogallery')->__('Status'),
        'align'     => 'left',
        'width'     => '80px',
        'index'     => 'status',
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
            'width'     => '80',
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
    //exit;
    return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
       
       $this->setMassactionIdField('gallery_id');
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
