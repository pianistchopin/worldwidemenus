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
 
 
class FME_Photogallery_Block_Adminhtml_Photogallery_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('photogalleryGrid');
      $this->setDefaultSort('photogallery_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
      // $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $_conn = Mage::getSingleton('core/resource');
      $photogallery_images_table=$_conn->getTableName('photogallery_images');
      $collection = Mage::getModel('photogallery/photogallery')->getCollection();
      $collection->getSelect()
        ->joinLeft(
            array('pi' => $photogallery_images_table),
            'main_table.photogallery_id=pi.photogallery_id AND (pi.img_id != 0)',
            array(
                            'images_count' => new Zend_Db_Expr('count(pi.img_id)'),
                        )
        )
            ->group('main_table.photogallery_id');
       $this->setCollection($collection);
       return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $albums = Mage::getModel('photogallery/gallery')->getCollection();
    
    $albums->addFieldToSelect('gallery_id');       
    $albums->addFieldToSelect('gallery_title');
    
    $albumCollection[] = array(
      "value"    =>  0,
      "label"    =>  Mage::helper('photogallery')->__(' '),
      "selected" => 'selected');
      
    foreach ($albums as $item)
    {
      $albumCollection[] = array(
      "value"    =>  $item->getGalleryId(),
      "label"    =>  $item->getGalleryTitle(),);
    }

    $this->addColumn(
        'photogallery_id', array(
        'header'    => Mage::helper('photogallery')->__('ID'),
        'align'     =>'right',
        'width'     => '50px',
        'index'     => 'photogallery_id',
        )
    );
    
    
    $this->addColumn(
        'gal_name', array(
        'header'    => Mage::helper('photogallery')->__('Photogallery Name'),
        'align'     =>'left',
        'index'     => 'gal_name',
        )
    );
    
    $this->addColumn(
        'gorder', array(
        'header'    => Mage::helper('photogallery')->__('Order'),
        'align'     =>'left',
        'index'     => 'gorder',
        )
    );
    

    if (!Mage::app()->isSingleStoreMode() && !$this->_isExport) {
            $this->addColumn(
                'store_id', array(
                'header'=> Mage::helper('photogallery')->__('Store Views'),
                'index' => 'store_id',
                'type'  => 'store',
                'store_all' => true,
                'store_view'=> true,
                'sortable'  => false,
                 'filter_condition_callback'=> array($this, '_filterStoreCondition'),
                'renderer'  => new FME_Photogallery_Block_Adminhtml_Photogallery_Grid_Renderer_Stores,

                )
            );
    }

    $this->addColumn(
        'parent_gallery_id', array(
          'header'    => Mage::helper('photogallery')->__('Parent Album'),
          'align'     =>'right',
          'width'     => '300px',
          'filter'    => false,
          'width'     => '50px',
          'index'     => 'parent_gallery_id',
          'type'      => 'options',
          'renderer'  => new FME_Photogallery_Block_Adminhtml_Photogallery_Grid_Renderer_Parentalbum,
        )
    );
    
    $this->addColumn(
        'images_count', array(
        'header'    => Mage::helper('photogallery')->__('Images Attached'),
        'align'     =>'left',
        'index'     => 'images_count',
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
       
       $this->setMassactionIdField('photogallery_id');
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

  protected function _filterStoreCondition($collection, $column)
  {
      
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        
        $collection->addStoreFilter($value);
        return $this;
  }
 

}
