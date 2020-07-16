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

class MT_Exitoffer_Block_Adminhtml_Exitoffer_Popup_Edit_Tabs_Fields_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('exitofferpopup_list_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $popup = Mage::registry('exitoffer_popup_data');
        $collection = Mage::getResourceModel('exitoffer/field_collection')
            ->addFieldToFilter('popup_id', $popup->getId());
        $collection->getSelect()->order('position ASC');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('exitoffer');
/*
        $this->addColumn('entity_id', array(
            'header' => $helper->__(' #'),
            'index'  => 'entity_id',
            'width' => '60px',
            'sortable'  => false
        ));
*/

        $this->addColumn('name', array(
            'header' => $helper->__('Name'),
            'index'  => 'name',
            'sortable'  => false
        ));

        $this->addColumn('admin_title', array(
            'header' => $helper->__('Admin Title'),
            'index'  => 'admin_title',
            'sortable'  => false
        ));

        $this->addColumn('title', array(
            'header' => $helper->__('Frontend Title'),
            'index'  => 'title',
            'sortable'  => false
        ));

        $this->addColumn('default_value', array(
            'header' => $helper->__('Default value'),
            'index'  => 'default_value',
            'sortable'  => false
        ));

        $this->addColumn('type', array(
            'header' => $helper->__('Input Type'),
            'index'  => 'type',
            'sortable'  => false
        ));

        $this->addColumn('options', array(
            'header' => $helper->__('Options'),
            'index'  => 'options',
            'sortable'  => false
        ));

        $this->addColumn('is_required', array(
            'header' => $helper->__('Is Required'),
            'index'  => 'is_required',
            'sortable'  => false,
            'renderer'=> 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options',
            'options' => array(
                0 => 'No',
                1 => 'Yes'
            )
        ));

        $this->addColumn('error_message_is_required', array(
            'header' => $helper->__('Custom error Message: is required'),
            'index'  => 'error_message_is_required',
            'sortable'  => false
        ));

        $this->addColumn('position', array(
            'header' => $helper->__('Position'),
            'index'  => 'position',
            'sortable'  => false
        ));


        $this->addColumn('actions', array(
            'header' => $helper->__('Actions'),
            'index'  => 'is_required',
            'sortable'  => false,
            'renderer'=> 'MT_Exitoffer_Block_Adminhtml_Widget_Grid_Column_Renderer_Fieldaction',
            'options' => array(
                0 => 'No',
                1 => 'Yes'
            )
        ));

        return parent::_prepareColumns();
    }


    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addSeriesNameFilter($column);
    }

    public function getGridUrl()
    {
        $popup = Mage::registry('exitoffer_popup_data');
        return $this->getUrl('*/*/fieldGrid', array(
            '_current'=>true,
            'popup_id' => $popup->getId()
        ));
    }

    public function getRowUrl($row) {
        return false;
    }
}