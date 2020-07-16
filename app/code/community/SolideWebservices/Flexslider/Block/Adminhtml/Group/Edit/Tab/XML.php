<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */
 
class SolideWebservices_Flexslider_Block_Adminhtml_Group_Edit_Tab_XML extends Mage_Adminhtml_Block_Catalog_Category_Tree {

	public function __construct() {
		parent::__construct();
		$this->setTemplate('flexslider/edit/tab/xml.phtml');
	}

	/**
	 * Retrieve currently edited group
	 */
	public function getCurrentGroup() {
		return Mage::registry('current_group');
	}

}