<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Group extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_group';
		$this->_blockGroup = 'flexslider';
		$this->_headerText = $this->__('Groups - Flexslider');
		parent::__construct();
	}

	protected function _prepareLayout() {

		/**
		 * Display store switcher if system has more one store
		 */
		if (!Mage::app()->isSingleStoreMode()) {
			$this->setChild('store_switcher', $this->getLayout()->createBlock('adminhtml/store_switcher')
					->setUseConfirm(false)
					->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
			);
		}

		return parent::_prepareLayout();
	}

	public function getStoreSwitcherHtml() {
		return $this->getChildHtml('store_switcher');
	}

}
