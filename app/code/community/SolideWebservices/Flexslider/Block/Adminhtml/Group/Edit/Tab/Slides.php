<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Group_Edit_Tab_Slides extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('slide_grid');
		$this->setDefaultSort('title');
		$this->setDefaultDir('asc');
		$this->setSaveParametersInSession(true);
	}

	/**
	 * Initialise and set the collection for the grid
	 */
	protected function _prepareCollection() {
		$slide_collection = Mage::getModel('flexslider/slide')->getCollection()
			->addGroupIdFilter($this->getGroupId());

		$this->setCollection($slide_collection);
	
		return parent::_prepareCollection();
	}
	
	/**
	 * Add the columns to the grid
	 */
	protected function _prepareColumns() {
		$this->addColumn('slide_id', array(
			'header'	=> $this->__('ID'),
			'align'		=> 'left',
			'width'		=> '60px',
			'index'		=> 'slide_id',
		));

		$this->addColumn('slide_title', array(
			'header'	=> $this->__('Title'),
			'align'		=> 'left',
			'index'		=> 'title',
		));

		$this->addColumn('is_enabled', array(
			'header'	=> $this->__('Enabled'),
			'width'		=> '90px',
			'index'		=> 'is_enabled',
			'type'		=> 'options',
			'options'	=> array(
				1 => $this->__('Enabled'),
				0 => $this->__('Disabled'),
			),
		));

		return parent::_prepareColumns();
	}

	/**
	 * Disable the edit URL for the row as this grid is for viewing only
	 */
	public function getRowUrl($row) {
		//return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

	/**
	 * Retrieve the group ID
	 * @return int
	 */
	public function getGroupId() {
		return Mage::registry('current_group') ? Mage::registry('current_group')->getId() : 0;
	}
}
