<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */
 
class SolideWebservices_Flexslider_Block_Adminhtml_Group_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('groupGrid');
		$this->setDefaultSort('group_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _getStore() {
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}

	/**
	 * Initialise and set the collection for the grid
	 */
	protected function _prepareCollection() {
		$collection = Mage::getModel('flexslider/group')->getCollection();
		$store = $this->_getStore();
		if ($store->getId()) {
			$collection->addStoreFilter($store);
		}

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	/**
	 * Add the columns to the grid
	 */
	protected function _prepareColumns() {

		$this->addColumn('group_id', array(
				'header'	=>	Mage::helper('flexslider')->__('ID'),
				'align'		=>	'center',
				'width'		=>	'50px',
				'index'		=>	'group_id',
		));

		$this->addColumn('title', array(
				'header'	=>	Mage::helper('flexslider')->__('Title'),
				'align'		=>	'left',
				'index'		=>	'title',
		));

		$this->addColumn('position', array(
				'header'	=> Mage::helper('flexslider')->__('Position'),
				'align'		=> 'left',
				'width'		=> '180px',
				'index'		=> 'position',
				'type'		=> 'options',
				'options'	=> Mage::getSingleton('flexslider/config_source_position')->toGridOptionArray(),
		));

		$this->addColumn('is_active', array(
				'header'	=> Mage::helper('flexslider')->__('Status'),
				'align'		=> 'left',
				'width'		=> '100px',
				'index'		=> 'is_active',
				'type'		=> 'options',
				'options'	=> array(
						1 => Mage::helper('flexslider')->__('Enabled'),
						0 => Mage::helper('flexslider')->__('Disabled'),
				),
		));

		$this->addColumn('action',
				array(
				'header'	=>	Mage::helper('flexslider')->__('Action'),
				'width'		=> '100px',
				'type'		=> 'action',
				'getter'	=> 'getId',
				'actions'	=> array(
						array(
								'caption'	=> Mage::helper('flexslider')->__('Edit'),
								'url'		=> array('base'=> '*/*/edit'),
								'field'		=> 'id'
						),
						array(
								'caption'	=> Mage::helper('flexslider')->__('Delete'),
								'url'		=> array('base'=> '*/*/delete'),
								'field'		=> 'id'
						)
				),
				'filter'	=> false,
				'sortable'	=> false,
				'index'		=> 'stores',
				'is_system' => true,
		));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction() {
		$this->setMassactionIdField('group_id');
		$this->getMassactionBlock()->setFormFieldName('group');

		$this->getMassactionBlock()->addItem('delete', array(
				'label'	   => Mage::helper('flexslider')->__('Delete'),
				'url'	   => $this->getUrl('*/*/massDelete'),
				'confirm'  => Mage::helper('flexslider')->__('Are you sure?')
		));

		$statuses = array(
			0 => Mage::helper('flexslider')->__('Disabled'),
			1 => Mage::helper('flexslider')->__('Enabled')
		);

		$this->getMassactionBlock()->addItem('status', array(
				'label'=> Mage::helper('flexslider')->__('Change status'),
				'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
				'confirm'  => Mage::helper('flexslider')->__('Are you sure?'),
				'additional' => array(
						'visibility' => array(
								'name' => 'status',
								'type' => 'select',
								'class' => 'required-entry',
								'label' => Mage::helper('flexslider')->__('Status'),
								'values' => $statuses
						)
				)
		));
		return $this;
	}
	
	/**
	 * Retrieve the URL for the row
	 */
	public function getRowUrl($row) {
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}