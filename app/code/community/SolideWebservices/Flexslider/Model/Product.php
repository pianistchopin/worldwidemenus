<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */
class SolideWebservices_Flexslider_Model_Product extends Mage_Catalog_Model_Product {

	function getDescription() {
		$content = $this->getData('description');
		$templateFilter = Mage::getModel('cms/template_filter');
		$html = $templateFilter->filter($content);
		return $html;
	}

}
