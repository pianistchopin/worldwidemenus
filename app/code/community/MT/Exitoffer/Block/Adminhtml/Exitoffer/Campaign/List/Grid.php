<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Campaign_List_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('exitoffer_campaign_list_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('exitoffer/campaign_collection');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('exitoffer');

        $this->addColumn('entity_id', array(
            'header' => $helper->__(' #'),
            'index'  => 'entity_id',
            'width' => '60px',
        ));

        $this->addColumn('name', array(
            'header' => $helper->__('Campaign Name'),
            'index'  => 'name',
        ));

        $this->addColumn('start_date', array(
            'header' => $helper->__('Start Date'),
            'index'  => 'start_date',
            'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime',
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'width' => '150px',
        ));

        $this->addColumn('end_date', array(
            'header' => $helper->__('End Date'),
            'index'  => 'end_date',
            'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime',
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'width' => '150px',
        ));

        $this->addColumn('status', array(
            'header' => $helper->__('Active'),
            'index'  => 'status',
            'renderer'=> 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options',
            'filter' => 'adminhtml/widget_grid_column_filter_select',
            'options' => array(
                '0' => $helper->__('No'),
                '1' => $helper->__('Yes'),
            ),
            'width' => '60px',
        ));

       /* $this->addColumn('store_id', array(
            'header' => $helper->__('Store'),
            'width' => '160px',
            'index' => 'store_id',
            'type' => 'store',
            'renderer'=> 'MT_Exitoffer_Block_Adminhtml_Widget_Grid_Column_Renderer_Store'
        ));*/

        return parent::_prepareColumns();
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        return parent::_prepareMassaction();

        $helper = Mage::helper('exitoffer');
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('exitofferseries');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('catalog')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete', array('_current'=>true)),
            'confirm' => $helper->__('Are you sure?'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'delete_action',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('catalog')->__('Option'),
                    'values' => array(
                        array(
                            'label' => $helper->__('Delete Only Series'),
                            'value' => 0
                        ),
                        array(
                            'label' => $helper->__('Delete Series and Gift Cards'),
                            'value' => 1
                        ),
                    )
                )
            )
        ));
        return $this;
    }
}