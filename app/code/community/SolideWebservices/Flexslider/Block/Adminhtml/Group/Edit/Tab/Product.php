<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Group_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Form {

	public function __construct() {
		parent::__construct();
		$this->setTemplate('flexslider/edit/tab/product.phtml');
	}

	/**
	 * Retrieve currently edited group
	 */
	public function getCurrentGroup() {
		return Mage::registry('current_group');
	}

	protected function getProductIds() {
		$_productList = $this->getCurrentGroup()->getProductSku();
		return is_array($_productList) ? $_productList : array();
	}

	public function getIdsString() {
		return implode(', ', $this->getProductIds());
	}

}
