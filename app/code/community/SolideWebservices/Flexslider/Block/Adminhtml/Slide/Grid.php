<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Slide_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('slide_grid');
		$this->setDefaultSort('slide_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	/**
	 * Initialise and set the collection for the grid
	 */
	protected function _prepareCollection() {
		$collection = Mage::getModel('flexslider/slide')->getCollection();

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	/**
	 * Add the columns to the grid
	 */
	protected function _prepareColumns() {

		$this->addColumn('slide_id', array(
			'header'	=> Mage::helper('flexslider')->__('ID'),
			'align'		=> 'left',
			'width'		=> '60px',
			'index'		=> 'slide_id',
		));

		$this->addColumn('title', array(
			'header'	=> Mage::helper('flexslider')->__('Title'),
			'width'		=> '300px',
			'align'		=> 'left',
			'index'		=> 'title',
		));

		$this->addColumn('image', array(
			'header'	=> Mage::helper('flexslider')->__('Slide'),
			'width'		=> '60px',
			'align'		=> 'center',
			'index'		=> 'image',
			'type'		=> 'image',
			'escape'    => true,
			'sortable'  => false,
			'filter'    => false,
			'renderer'  => new SolideWebservices_Flexslider_Block_Adminhtml_Grid_Renderer_Image,
		));

		$this->addColumn('group_id', array(
			'header'	=> $this->__('Group'),
			'align'		=> 'left',
			'index'		=> 'group_id',
			'type'		=> 'options',
			'options'	=> $this->_getGroups(),
		));

		$this->addColumn('is_enabled', array(
			'header'	=> Mage::helper('flexslider')->__('Enabled'),
			'width'		=> '90px',
			'index'		=> 'is_enabled',
			'type'		=> 'options',
			'options'	=> array(
				1 => $this->__('Enabled'),
				0 => $this->__('Disabled'),
			),
		));

		$this->addColumn('action',
				array(
				'header'	=>	Mage::helper('flexslider')->__('Action'),
				'width'		=> '100',
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

	/**
	 * Retrieve an array of all of the slider groups
	 *
	 * @return array
	 */
	protected function _getGroups() {
		$groups = Mage::getResourceModel('flexslider/group_collection');
		$options = array();

		foreach($groups as $group) {
			$options[$group->getId()] = $group->getTitle();
		}

		return $options;
	}

	protected function _prepareMassaction() {
		$this->setMassactionIdField('slide_id');
		$this->getMassactionBlock()->setFormFieldName('slide');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'		=> $this->__('Delete'),
			'url'  		=> $this->getUrl('*/*/massDelete'),
			'confirm' 	=> Mage::helper('catalog')->__('Are you sure?')
		));

		$statuses = array(
			0 => Mage::helper('flexslider')->__('Disabled'),
			1 => Mage::helper('flexslider')->__('Enabled')
		);

		$this->getMassactionBlock()->addItem('status', array(
				'label'		=> Mage::helper('flexslider')->__('Change status'),
				'url'  		=> $this->getUrl('*/*/massStatus', array('_current'=>true)),
				'confirm' 	=> Mage::helper('catalog')->__('Are you sure?'),
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
