<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Model_Slide extends Mage_Core_Model_Abstract {

	protected function _construct() {
		$this->_init('flexslider/slide');
	}

	/**
	 * Retrieve the group model associated with the slide
	 *
	 * @return SolideWebservices_Flexslider_Model_Group|false
	 */
	public function getGroup() {
		if (!$this->hasGroup()) {
			$this->setGroup($this->getResource()->getGroup($this));
		}

		return $this->getData('group');
	}

	/**
	 * Check if slide is a video
	 *
	 * @return bool
	 */
	public function isVideo() {
		if($this->getData('slidetype')!='image') { return true; }
	}

	/**
	 * Retrieve the alt text
	 * If the alt_text field is empty, use the title field
	 *
	 * @return string
	 */
	public function getAltText() {
		return $this->getData('alt_text') ? $this->getData('alt_text') : $this->getTitle();
	}

	/**
	 * Determine whether the slide has a valid URL
	 *
	 * @return bool
	 */
	public function hasUrl() {
		return strlen($this->getUrl()) > 1;
	}

	/**
	 * Retrieve the URL
	 * This converts relative URL's to absolute
	 *
	 * @return string
	 */
	public function getUrl() {
		if ($this->_getData('url')) {
			if (strpos($this->_getData('url'), 'http://') === false && strpos($this->_getData('url'), 'https://') === false) {
				$this->setUrl(Mage::getBaseUrl() . ltrim($this->_getData('url'), '/ '));
			}
		}

		return $this->_getData('url');
	}

	/**
	 * Retrieve the url target
	 * If the url_target field is empty, use the _self
	 *
	 * @return string
	 */
	public function getUrlTarget() {
		return $this->getData('url_target') ? $this->getData('url_target') : '_self';
	}

}