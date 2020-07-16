<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Encode the mixed $valueToEncode into the JSON format
	 *
	 * @param mixed $valueToEncode
	 * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
	 * @param  array $options Additional options used during encoding
	 * @return string
	*/
	public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array()) {
		$json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
		/* @var $inline Mage_Core_Model_Translate_Inline */
		$inline = Mage::getSingleton('core/translate_inline');
		if ($inline->isAllowed()) {
			$inline->setIsJson(true);
			$inline->processResponseBody($json);
			$inline->setIsJson(false);
		}

		return $json;
	}

	/**
	 * Determine whether the extension is enabled
	 *
	 * @return bool
	*/
	public function isEnabled() {
		return Mage::getStoreConfig('flexslider/general/enabled');
	}

	/**
	 * Determine scope
	 *
	 * @return bool
	*/
	public function getEnabledScope($scope) {
		switch($scope) {
			case 'selected':
				return Mage::getStoreConfig('flexslider/advanced_settings/enable_selected_positions');
				break;
			case 'global':
				return Mage::getStoreConfig('flexslider/advanced_settings/enable_global_positions');
				break;
			case 'customer':
				return Mage::getStoreConfig('flexslider/advanced_settings/enable_customer_positions');
				break;
			case 'checkout':
				return Mage::getStoreConfig('flexslider/advanced_settings/enable_checkout_positions');
				break;
			default:
				return true;
		}
	}

	/**
	 * return base url
	 *
	 * @print string
	 */
	function base_url(){
		return sprintf(
			"%s://%s",
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http',
			$_SERVER['HTTP_HOST']
		);
	}

	/**
	 * see if there is a slider group with overlay navigation to determine if hoverIntent has to be loaded
	 */
	function overlay_enabled() {
		$group_collection = Mage::getModel('flexslider/group')->getCollection()
			->addFieldToFilter('type', array('eq' => 'overlay'))
			->addFieldToFilter('is_active', array('eq' => 1));


		if($group_collection->getSize() > 0) {
			return true;
		}
	}

	/**
	 * see if there is are video slides to determine if froogaloop and video scripts have to be loaded
	 */
	function videos_enabled() {
		$slide_collection = Mage::getModel('flexslider/slide')->getCollection()
			->addFieldToFilter('is_enabled', array('eq' => 1))
			->addFieldToFilter('slidetype', array('neq' => 'image'));

		if($slide_collection->getSize() > 0) {
			return true;
		}
	}

}
