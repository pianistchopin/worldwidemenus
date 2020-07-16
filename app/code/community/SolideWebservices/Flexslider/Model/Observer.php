<?php
/**
 * @category	Solide Webservices
 * @package		Flexslider
 */

class SolideWebservices_Flexslider_Model_Observer {

	/**
	 * Add scripts depending on configuration values
	 */
	public function loadScripts($observer) {

		//initialize
		$jQueryPath = 'flexslider/jquery-'. Mage::getStoreConfig('flexslider/general/version_jquery') .'.min.js';
		if(Mage::getStoreConfig('flexslider/general/enable_jquery') && Mage::getStoreConfig('flexslider/general/jquery_noconflictmode')) {
			$afterJquery = 'flexslider/jquery.noconflict.js';
			$easingConflict = 'flexslider/jquery.easing.js';
		} elseif(Mage::getStoreConfig('flexslider/general/enable_jquery') && !Mage::getStoreConfig('flexslider/general/jquery_noconflictmode')) {
			$afterJquery = $jQueryPath;
			$easingConflict = 'flexslider/jquery.easing-nojquery.js';
		} elseif(!Mage::getStoreConfig('flexslider/general/enable_jquery')) {
			$afterJquery = 'flexslider/jquery.flexslider-min.js';
			$easingConflict = 'flexslider/jquery.easing-nojquery.js';
		}
		if(Mage::helper('flexslider')->videos_enabled()) {
			$beforeHoverIntent = 'flexslider/froogaloop.js';
		} elseif (Mage::getStoreConfig('flexslider/general/enable_easing')) {
			$beforeHoverIntent = 'flexslider/jquery.easing.js';
		} else {
			$beforeHoverIntent = 'flexslider/picturefill.min.js';
		}

		if (Mage::getStoreConfig('flexslider/general/enabled')) {
			$_head = $this->__getHeadBlock();

			if ($_head) {

				// load css
				$_head->addFirst('skin_css', 'flexslider/css/flexslider.min.css');

				// determine if the scripts should be loaded first or last
				if(Mage::getStoreConfig('flexslider/general/jquery_position') == 'before') {
					$_head->addFirst('js', 'flexslider/jquery.flexslider-min.js');
				} else {
					$_head->addEnd('js', 'flexslider/jquery.flexslider-min.js');
				}

				// if jQuery is enabled
				if(Mage::getStoreConfig('flexslider/general/enable_jquery')) {

					$_head->addBefore('js', $jQueryPath, 'flexslider/jquery.flexslider-min.js');

					// should noConflict mode be loaded
					if(Mage::getStoreConfig('flexslider/general/jquery_noconflictmode')) {
						$_head->addAfter('js', 'flexslider/jquery.noconflict.js', $jQueryPath);
					}

				}

				// load picturefill library
				if( (!Mage::getStoreConfig('flexslider/general/enable_jquery') || !Mage::getStoreConfig('flexslider/general/jquery_noconflictmode')) && !Mage::getStoreConfig('flexslider/general/enable_easing') ) {
					$_head->addBefore('js', 'flexslider/picturefill.min.js', 'flexslider/jquery.flexslider-min.js');
				} else {
					$_head->addAfter('js', 'flexslider/picturefill.min.js', $afterJquery);
				}

				// should the easing library be loaded
				if (Mage::getStoreConfig('flexslider/general/enable_easing')) {
					$_head->addAfter('js', $easingConflict, 'flexslider/picturefill.min.js');
				}

				// if there are video slides
				if(Mage::helper('flexslider')->videos_enabled()) {
					$_head->addAfter('js', 'flexslider/jquery.fitvid.js', Mage::getStoreConfig('flexslider/general/enable_easing') ? $easingConflict : 'flexslider/picturefill.min.js');
					$_head->addAfter('js', 'flexslider/froogaloop.js', 'flexslider/jquery.fitvid.js');
				}

				// if there are slider groups with overlay navigation
				if(Mage::helper('flexslider')->overlay_enabled()) {
					$_head->addAfter('js', 'flexslider/jquery.hoverIntent.js', $beforeHoverIntent);
				}

			}
		}
	}

	/*
	 * Get head block
	 */
	private function __getHeadBlock() {
		return Mage::getSingleton('core/layout')->getBlock('flexslider_head');
	}

}
