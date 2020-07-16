<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Model_Config_Source_Jquery {

	/**
	 * Retrieve an array of possible options
	 *
	 * @return array
	 */
	public function toOptionArray($includeEmpty = false, $emptyText = '-- Please Select --') {
		$options = array();

		if ($includeEmpty) {
			$options[] = array(
				'value' => '',
				'label' => Mage::helper('adminhtml')->__($emptyText),
			);
		}

		foreach($this->getOptions() as $value => $label) {
			$options[] = array(
				'value' => $value,
				'label' => Mage::helper('adminhtml')->__($label),
			);
		}

		return $options;
	}

	/**
	 * Retrieve an array of possible options
	 *
	 * @return array
	 */
	public function getOptions() {
		return array(
			'2.2.2'		=> 'Version 2.2.2',
			'2.1.4'		=> 'Version 2.1.4',
			'1.12.2' 	=> 'Version 1.12.2',
			'1.11.0' 	=> 'Version 1.11.0',
			'1.10.2' 	=> 'Version 1.10.2',
			'1.9.1' 	=> 'Version 1.9.1',
			'1.8.3' 	=> 'Version 1.8.3',
		);
	}

}
