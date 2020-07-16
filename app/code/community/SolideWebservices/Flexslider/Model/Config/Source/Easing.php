<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Model_Config_Source_Easing {

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
			'jswing' 			=> 'Swing',
			'easeInQuad' 		=> 'easeInQuad',
			'easeOutQuad' 		=> 'easeOutQuad',
			'easeInOutQuad' 	=> 'easeInOutQuad',
			'easeInCubic' 		=> 'easeInCubic',
			'easeOutCubic' 		=> 'easeOutCubic',
			'easeInOutCubic' 	=> 'easeInOutCubic',
			'easeInQuart' 		=> 'easeInQuart',
			'easeOutQuart' 		=> 'easeOutQuart',
			'easeInOutQuart' 	=> 'easeInOutQuart',
			'easeInQuint' 		=> 'easeInQuint',
			'easeOutQuint' 		=> 'easeOutQuint',
			'easeInOutQuint' 	=> 'easeInOutQuint',
			'easeInSine' 		=> 'easeInSine',
			'easeOutSine' 		=> 'easeOutSine',
			'easeInOutSine' 	=> 'easeInOutSine',
			'easeInExpo' 		=> 'easeInExpo',
			'easeOutExpo' 		=> 'easeOutExpo',
			'easeInOutExpo' 	=> 'easeInOutExpo',
			'easeInCirc' 		=> 'easeInCirc',
			'easeOutCirc' 		=> 'easeOutCirc',
			'easeInOutCirc' 	=> 'easeInOutCirc',
			'easeInElastic' 	=> 'easeInElastic',
			'easeOutElastic' 	=> 'easeOutElastic',
			'easeInOutElastic' 	=> 'easeInOutElastic',
			'easeInBack' 		=> 'easeInBack',
			'easeOutBack' 		=> 'easeOutBack',
			'easeInOutBack' 	=> 'easeInOutBack',
			'easeInBounce' 		=> 'easeInBounce',
			'easeOutBounce' 	=> 'easeOutBounce',
			'easeInOutBounce' 	=> 'easeInOutBounce',
		);
	}
}