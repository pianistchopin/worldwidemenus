<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Block_Adminhtml_Slide_Helper_Image extends Varien_Data_Form_Element_Image {
	
	/**
	 * Prepend the base image URL to the image filename
	 *
	 * @return null|string
	 */
	protected function _getUrl() {
		if ($this->getValue() && !is_array($this->getValue())) {
			return Mage::helper('flexslider/image')->getImageUrl($this->getValue(), 'flexslider/thumbnails');
		}

		return null;
	}
}