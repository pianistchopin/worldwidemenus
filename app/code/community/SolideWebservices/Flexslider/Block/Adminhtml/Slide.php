<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Slide extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_slide';
		$this->_blockGroup = 'flexslider';
		$this->_headerText = $this->__('Slides - Flexslider');
		parent::__construct();
	}

}
