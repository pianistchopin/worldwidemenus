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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_List_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('exitofferpopup_list_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('exitoffer/popup_collection');
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
            'header' => $helper->__('Popup Name'),
            'index'  => 'name',
        ));

        $this->addColumn('content_type', array(
            'header' => $helper->__('Type'),
            'index'  => 'content_type',
            'renderer'=> 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options',
            'filter' => 'adminhtml/widget_grid_column_filter_select',
            'options' => Mage::getModel('exitoffer/adminhtml_system_config_content ')->toValueArray(),
            'width' => '200px',
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

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}